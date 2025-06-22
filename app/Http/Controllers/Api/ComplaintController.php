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


class ComplaintController extends Controller
{
    use AuthorizesRequests;

    // Customer endpoints
    public function store(StoreComplaintRequest $request)
    {
        $complaint = Complaint::create([
            'title' => $request->title,
            'description' => $request->description,
            'customer_id' => auth()->id(),
        ]);

        $complaint->load(['customer:id,name,email']);

        return response()->json([
            'message' => 'Complaint submitted successfully',
            'complaint' => $complaint,
        ], 201);
    }

    public function customerComplaints(Request $request)
    {
        $query = Complaint::forCustomer(auth()->id())
            ->withRelations();

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->byStatus($request->status);
        }

        $complaints = $query->latest()->paginate(15);

        return response()->json([
            'message' => 'Complaints retrieved successfully',
            'complaints' => $complaints,
        ]);
    }

    // Admin endpoints
    public function index(Request $request)
    {        
        $this->authorize('viewAny', Complaint::class);

        $query = Complaint::withRelations();

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->byStatus($request->status);
        }

        $complaints = $query->latest()->paginate(15);

        return response()->json([
            'message' => 'All complaints retrieved successfully',
            'complaints' => $complaints,
        ]);
    }

    public function assign(AssignComplaintRequest $request, Complaint $complaint)
    {
        $this->authorize('assign', $complaint);

        if (!$complaint->canBeAssigned()) {
            return response()->json([
                'message' => 'Complaint cannot be assigned in its current status',
            ], 422);
        }

        $technician = User::findOrFail($request->technician_id);

        if (!$technician->is_available) {
            return response()->json([
                'message' => 'Selected technician is not available',
            ], 422);
        }

        $complaint->update([
            'technician_id' => $request->technician_id,
            'assigned_at' => now(),
            'status' => Complaint::STATUS_IN_PROGRESS,
        ]);

        $complaint->load(['customer:id,name,email', 'technician:id,name,email']);

        return response()->json([
            'message' => 'Complaint assigned successfully',
            'complaint' => $complaint,
        ]);
    }

    public function availableTechnicians()
    {
        $this->authorize('viewAny', Complaint::class);

        $technicians = User::availableTechnicians()
            ->select('id', 'name', 'email')
            ->get();

        return response()->json([
            'message' => 'Available technicians retrieved successfully',
            'technicians' => $technicians,
        ]);
    }

    // Technician endpoints
    public function technicianComplaints(Request $request)
    {
        $query = Complaint::forTechnician(auth()->id())
            ->withRelations();

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->byStatus($request->status);
        }

        $complaints = $query->latest('assigned_at')->paginate(15);

        return response()->json([
            'message' => 'Assigned complaints retrieved successfully',
            'complaints' => $complaints,
        ]);
    }

    public function updateStatus(UpdateComplaintStatusRequest $request, Complaint $complaint)
    {
        $this->authorize('updateStatus', $complaint);

        $oldStatus = $complaint->status;
        $newStatus = $request->status;

        $updateData = ['status' => $newStatus];

        // Set resolved_at timestamp when marking as resolved
        if ($newStatus === Complaint::STATUS_RESOLVED) {
            $updateData['resolved_at'] = now();
        }

        $complaint->update($updateData);

        // Queue notification email if complaint is resolved
        if ($newStatus === Complaint::STATUS_RESOLVED && $oldStatus !== Complaint::STATUS_RESOLVED) {
            SendComplaintResolvedNotification::dispatch($complaint);
        }

        $complaint->load(['customer:id,name,email', 'technician:id,name,email']);

        return response()->json([
            'message' => 'Complaint status updated successfully',
            'complaint' => $complaint,
        ]);
    }

    // Common endpoints
    public function show(Complaint $complaint)
    {
        $this->authorize('view', $complaint);

        $complaint->load(['customer:id,name,email', 'technician:id,name,email']);

        return response()->json([
            'message' => 'Complaint retrieved successfully',
            'complaint' => $complaint,
        ]);
    }

    public function destroy(Complaint $complaint)
    {
        $this->authorize('delete', $complaint);

        $complaint->delete();

        return response()->json([
            'message' => 'Complaint deleted successfully',
        ]);
    }
}
