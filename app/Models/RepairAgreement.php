<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairAgreement extends Model
{
    protected $fillable = [
        'repair_session_id',
        'couple_id',
        'agreement_text',
        'created_by',
        'partner_acknowledged',
        'acknowledged_at',
    ];

    protected $casts = [
        'partner_acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    /**
     * Get the repair session this agreement belongs to
     */
    public function repairSession()
    {
        return $this->belongsTo(RepairSession::class);
    }

    /**
     * Get the couple this agreement belongs to
     */
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    /**
     * Get the user who created this agreement
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Partner acknowledges the agreement
     */
    public function acknowledge(): void
    {
        $this->update([
            'partner_acknowledged' => true,
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Check if agreement is acknowledged
     */
    public function isAcknowledged(): bool
    {
        return $this->partner_acknowledged;
    }

    /**
     * Scope to get acknowledged agreements
     */
    public function scopeAcknowledged($query)
    {
        return $query->where('partner_acknowledged', true);
    }

    /**
     * Scope to get unacknowledged agreements
     */
    public function scopeUnacknowledged($query)
    {
        return $query->where('partner_acknowledged', false);
    }
}
