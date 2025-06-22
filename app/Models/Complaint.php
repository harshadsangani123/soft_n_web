<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'customer_id',
        'technician_id',
        'assigned_at',
        'resolved_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_NOT_AVAILABLE = 'not_available';
    const STATUS_RESOLVED = 'resolved';

    // Relationships
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    public function scopeWithRelations($query)
    {
        return $query->with(['customer:id,name,email', 'technician:id,name,email']);
    }

    // Helper methods
    public function isResolved()
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function canBeAssigned()
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_NOT_AVAILABLE]);
    }
}