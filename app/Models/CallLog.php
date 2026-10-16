<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    protected $fillable = [
        'customer_id','contractor_id','outcome',
        'barrier','coupon_offered','follow_up_on','notes',
    ];

    protected function casts(): array
    {
        return [
            'coupon_offered' => 'boolean',
            'follow_up_on'   => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contractor_id');
    }
}
