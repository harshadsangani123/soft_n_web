<?php

namespace App\Services;

use App\Events\ComplaintStatusUpdated;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ComplaintService
{
    public function getComplaintsForUser(User $user, array $filters = [])
    {
        $query = $this->buildBaseQuery($user);
        
        return $this->applyFilters($query, $filters)->paginate(15);
    }

    public function createComplaint(array $data, User $customer)
    {
        return Complaint::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'customer_id' => $customer->id,
        ]);
    }

    public function assignComplaint(Complaint $complaint, User $technician)
    {
        if (!$complaint->canBeAssigned()) {
            throw new \Exception('Complaint cannot be assigned in its current status');
        }

        if (!$technician->is_available) {
            throw new \Exception('Technician is not available');
        }

        return $complaint->update([
            'technician_id' => $technician->id,
            'assigned_at' => now(),
            'status' => Complaint::STATUS_IN_PROGRESS,
        ]);
    }

    public function updateStatus(Complaint $complaint, string $status)
    {
        $oldStatus = $complaint->status;
        
        $updateData = ['status' => $status];
        
        if ($status === Complaint::STATUS_RESOLVED) {
            $updateData['resolved_at'] = now();
        }

        $complaint->update($updateData);

        // Fire event for status change
        event(new ComplaintStatusUpdated($complaint, $oldStatus, $status));

        return $complaint;
    }

    private function buildBaseQuery(User $user): Builder
    {
        $query = Complaint::withRelations();

        if ($user->isCustomer()) {
            $query->forCustomer($user->id);
        } elseif ($user->isTechnician()) {
            $query->forTechnician($user->id);
        }

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    public function getAvailableTechnicians()
    {
        return User::availableTechnicians()
            ->select('id', 'name', 'email')
            ->get();
    }

    public function getComplaintStats(User $user = null)
    {
        $query = Complaint::query();

        if ($user && $user->isCustomer()) {
            $query->forCustomer($user->id);
        } elseif ($user && $user->isTechnician()) {
            $query->forTechnician($user->id);
        }

        return [
            'total' => $query->count(),
            'open' => $query->clone()->byStatus('open')->count(),
            'in_progress' => $query->clone()->byStatus('in_progress')->count(),
            'resolved' => $query->clone()->byStatus('resolved')->count(),
            'not_available' => $query->clone()->byStatus('not_available')->count(),
        ];
    }
}