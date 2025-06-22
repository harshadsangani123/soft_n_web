<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ComplaintPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();

    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Complaint $complaint): bool
    {
        return $user->isAdmin() || 
               ($user->isCustomer() && $complaint->customer_id === $user->id) ||
               ($user->isTechnician() && $complaint->technician_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isCustomer();
    }

    /**
     * Determine whether the admin can assign the complaint.
     */
    public function assign(User $user, Complaint $complaint)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the admin can update the status.
     */
     public function updateStatus(User $user, Complaint $complaint)
    {
        return $user->isTechnician() && $complaint->technician_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Complaint $complaint)
    {
        return $user->isAdmin() || 
               ($user->isCustomer() && $complaint->customer_id === $user->id);
    }
}
