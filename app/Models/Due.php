<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Due extends Model
{
    protected $fillable = [
        'title',
        'description',
        'amount',
        'due_date',
        'type',
        'party_name',
        'party_type',
        'contact_person',
        'contact_phone',
        'contact_email',
        'payment_method',
        'paid_amount',
        'remaining_amount',
        'paid_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'paid_date' => 'date',
        'type' => 'string',
        'party_type' => 'string',
        'party_name' => 'string',
        'contact_person' => 'string',
        'contact_phone' => 'string',
        'contact_email' => 'string',
        'payment_method' => 'string',
        'notes' => 'string',
        'user_id' => 'integer',
    ];

    public function scopePending($query)
    {
        return $query->where('type', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('type', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('type', 'overdue');
    }

    public function scopeCancelled($query)
    {
        return $query->where('type', 'cancelled');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDueBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', $startDate, $endDate);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return number_format($this->paid_amount, 2);
    }

    public function getFormattedRemainingAmountAttribute(): string
    {
        return number_format($this->remaining_amount, 2);
    }

    public function getFormattedDueDateAttribute(): string
    {
        return $this->due_date->format('M d, Y');
    }

    public function getFormattedPaidDateAttribute(): string
    {
        return $this->paid_date ? $this->paid_date->format('M d, Y') : 'Not paid yet';
    }

    public function getStatusBadgeAttribute(): string
    {
        switch ($this->type) {
            case 'pending':
                return '<span style="background:#ffc107; color:#856404; padding:2px 4px; border-radius:4px; font-size:12px;">Pending</span>';
            case 'paid':
                return '<span style="background:#d4edda; color:#155724; padding:2px 4px; border-radius:4px; font-size:12px;">Paid</span>';
            case 'overdue':
                return '<span style="background:#dc3545; color:#ffffff; padding:2px 4px; border-radius:4px; font-size:12px;">Overdue</span>';
            case 'cancelled':
                return '<span style="background:#6c757d; color:#ffffff; padding:2px 4px; border-radius:4px; font-size:12px;">Cancelled</span>';
            default:
                return '<span style="background:#6c757d; color:#ffffff; padding:2px 4px; border-radius:4px; font-size:12px;">Unknown</span>';
        }
    }

    public function getPartyTypeBadgeAttribute(): string
    {
        switch ($this->party_type) {
            case 'supplier':
                return '<span style="background:#007bff; color:#ffffff; padding:2px 4px; border-radius:4px; font-size:12px;">Supplier</span>';
            case 'customer':
                return '<span style="background:#28a745; color:#ffffff; padding:2px 4px; border-radius:4px; font-size:12px;">Customer</span>';
            case 'employee':
                return '<span style="background:#17a2b8; color:#ffffff; padding:2px 4px; border-radius:4px; font-size:12px;">Employee</span>';
            default:
                return '<span style="background:#6c757d; color:#ffffff; padding:2px 4px; border-radius:4px; font-size:12px;">Unknown</span>';
        }
    }

    public function getDaysOverdueAttribute(): int
    {
        if ($this->type === 'overdue' && $this->due_date) {
            return $this->due_date->diffInDays(now())->days;
        }

        return 0;
    }
}
