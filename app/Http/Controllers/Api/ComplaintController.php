<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintStatusRequest;
use App\Http\Requests\AssignComplaintRequest;
use App\Jobs\SendComplaintResolvedNotification;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComplaintController extends Controller
{
    use AuthorizesRequests;

    // Customer endpoints
    /**
     * Store a new complaint.
     * @param StoreComplaintRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(StoreComplaintRequest $request)
    {
        DB::beginTransaction();

        try {
            $complaint = Complaint::create([
                'title' => $request->title,
                'description' => $request->description,
                'customer_id' => auth()->id(),
            ]);

            $complaint->load(['customer:id,name,email']);

            DB::commit();

            Log::info('Complaint submitted successfully', [
                'complaint_id' => $complaint->id,
                'customer_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Complaint submitted successfully',
                'complaint' => $complaint,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to submit complaint', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Failed to submit complaint. Please try again later.',
            ], 500);
        }
    }

    /**
     * Retrieve customer's complaints.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerComplaints(Request $request)
    {
        try {
            Log::info('Fetching customer complaints', [
                'customer_id' => auth()->id(),
                'status' => $request->get('status'),
            ]);

            // Since it's a read operation, no need for a transaction here
            $query = Complaint::forCustomer(auth()->id())
                ->withRelations();

            if ($request->filled('status')) {
                $query->byStatus($request->status);
            }

            $complaints = $query->latest()->paginate(15);

            return response()->json([
                'message' => 'Complaints retrieved successfully',
                'complaints' => $complaints,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching customer complaints', [
                'customer_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve complaints',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Admin endpoints
    /**
     * Retrieve all complaints.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Complaint::class);

            Log::info('Admin fetching all complaints', [
                'user_id' => auth()->id(),
                'status' => $request->get('status'),
            ]);

            $query = Complaint::withRelations();

            if ($request->filled('status')) {
                $query->byStatus($request->status);
            }

            $complaints = $query->latest()->paginate(15);

            return response()->json([
                'message' => 'All complaints retrieved successfully',
                'complaints' => $complaints,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized access attempt to complaints index', [
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Unauthorized access',
                'error' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            Log::error('Error retrieving all complaints', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve complaints',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign a complaint to a technician.
     * @param AssignComplaintRequest $request
     * @param Complaint $complaint
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function assign(AssignComplaintRequest $request, Complaint $complaint)
    {
        try {
            $this->authorize('assign', $complaint);

            if (!$complaint->canBeAssigned()) {
                Log::warning('Attempt to assign complaint in invalid state', [
                    'complaint_id' => $complaint->id,
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'message' => 'Complaint cannot be assigned in its current status',
                ], 422);
            }

            $technician = User::findOrFail($request->technician_id);

            if (!$technician->is_available) {
                Log::info('Unavailable technician selected', [
                    'technician_id' => $request->technician_id,
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'message' => 'Selected technician is not available',
                ], 422);
            }

            DB::beginTransaction();

            $complaint->update([
                'technician_id' => $request->technician_id,
                'assigned_at' => now(),
                'status' => Complaint::STATUS_IN_PROGRESS,
            ]);

            DB::commit();

            $complaint->load(['customer:id,name,email', 'technician:id,name,email']);

            Log::info('Complaint assigned successfully', [
                'complaint_id' => $complaint->id,
                'technician_id' => $request->technician_id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Complaint assigned successfully',
                'complaint' => $complaint,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized complaint assignment attempt', [
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Unauthorized',
                'error' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error assigning complaint', [
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to assign complaint',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retrieve available technicians
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function availableTechnicians()
    {
        try {
            $this->authorize('viewAny', Complaint::class);

            Log::info('Fetching available technicians', [
                'requested_by' => auth()->id(),
            ]);

            $technicians = User::availableTechnicians()
                ->select('id', 'name', 'email')
                ->get();

            return response()->json([
                'message' => 'Available technicians retrieved successfully',
                'technicians' => $technicians,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized attempt to view available technicians', [
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Unauthorized',
                'error' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            Log::error('Error retrieving available technicians', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve technicians',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Technician endpoints

    /**
     * Retrieve assigned complaints for a technician
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function technicianComplaints(Request $request)
    {
        try {
            $technicianId = auth()->id();

            Log::info('Fetching complaints assigned to technician', [
                'technician_id' => $technicianId,
                'status_filter' => $request->get('status'),
            ]);

            $query = Complaint::forTechnician($technicianId)
                ->withRelations();

            if ($request->filled('status')) {
                $query->byStatus($request->status);
            }

            $complaints = $query->latest('assigned_at')->paginate(15);

            return response()->json([
                'message' => 'Assigned complaints retrieved successfully',
                'complaints' => $complaints,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching technician complaints', [
                'technician_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve assigned complaints',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the status of a complaint
     * @param UpdateComplaintStatusRequest $request
     * @param Complaint $complaint
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(UpdateComplaintStatusRequest $request, Complaint $complaint)
    {
        try {
            $this->authorize('updateStatus', $complaint);

            $oldStatus = $complaint->status;
            $newStatus = $request->status;

            $updateData = ['status' => $newStatus];

            if ($newStatus === Complaint::STATUS_RESOLVED) {
                $updateData['resolved_at'] = now();
            }

            DB::beginTransaction();

            $complaint->update($updateData);

            // Send notification if status is resolved
            if ($newStatus === Complaint::STATUS_RESOLVED && $oldStatus !== Complaint::STATUS_RESOLVED) {
                // Send notification to customer in the background using a job
                SendComplaintResolvedNotification::dispatch($complaint);
            }

            DB::commit();

            $complaint->load(['customer:id,name,email', 'technician:id,name,email']);

            Log::info('Complaint status updated', [
                'complaint_id' => $complaint->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Complaint status updated successfully',
                'complaint' => $complaint,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized status update attempt', [
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Unauthorized',
                'error' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update complaint status', [
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to update complaint status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Common endpoints
    /**
     * Retrieve a specific complaint
     * @param Complaint $complaint
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Complaint $complaint)
    {
        try {
            $this->authorize('view', $complaint);

            Log::info('Viewing complaint details', [
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
            ]);

            $complaint->load(['customer:id,name,email', 'technician:id,name,email']);

            return response()->json([
                'message' => 'Complaint retrieved successfully',
                'complaint' => $complaint,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized complaint view attempt', [
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Unauthorized',
                'error' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            Log::error('Error retrieving complaint', [
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve complaint',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specific complaint
     * @param Complaint $complaint
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Complaint $complaint)
    {
        try {
            $this->authorize('delete', $complaint);

            DB::beginTransaction();

            $complaintId = $complaint->id;

            $complaint->delete();

            DB::commit();

            Log::info('Complaint deleted successfully', [
                'complaint_id' => $complaintId,
                'deleted_by' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Complaint deleted successfully',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized complaint deletion attempt', [
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Unauthorized',
                'error' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting complaint', [
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to delete complaint',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
