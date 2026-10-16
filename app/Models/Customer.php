<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'phone','name','email','joined_on','location','account_status',
        'total_orders','source_order_id','product_name','assigned_to',
        'activation_status','barrier','coupon_offered','follow_up_on',
        'notes','verified_order_id','verified_order_at','delivered','coupon_redeemed',
    ];

    protected function casts(): array
    {
        return [
            'joined_on'         => 'date',
            'follow_up_on'      => 'date',
            'verified_order_at' => 'datetime',
            'coupon_offered'    => 'boolean',
            'delivered'         => 'boolean',
            'coupon_redeemed'   => 'boolean',
            'total_orders'      => 'integer',
        ];
    }

    public static array $STATUSES = [
        'not_called'         => 'Not Called',
        'no_answer'          => 'No Answer',
        'interested'         => 'Interested',
        'not_ready'          => 'Not Ready',
        'callback'           => 'Callback',
        'ordered_unverified' => 'Order Unverified',
        'verified_order'     => 'Verified Order',
        'do_not_contact'     => 'Do Not Contact',
    ];

    public static array $BARRIERS = [
        ''        => 'Not identified',
        'browsing'=> 'Just browsing',
        'trust'   => 'Trust',
        'price'   => 'Price',
        'app'     => 'App difficulty',
        'product' => 'Product unavailable',
        'later'   => 'Buying later',
        'other'   => 'Other',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$STATUSES[$this->activation_status] ?? $this->activation_status;
    }

    public function isCallable(): bool
    {
        return !in_array($this->activation_status, ['do_not_contact', 'verified_order'])
            && $this->total_orders === 0;
    }

    /**
     * Normalize Ethiopian phone numbers to 251XXXXXXXXX format.
     */
    public static function normalizePhone(string $value): ?string
    {
        $s = preg_replace('/[^0-9]/', '', $value);
        if (str_starts_with($s, '00')) $s = substr($s, 2);
        if (preg_match('/^0[79]\d{8}$/', $s)) $s = '251' . substr($s, 1);
        if (preg_match('/^[79]\d{8}$/', $s)) $s = '251' . $s;
        return preg_match('/^251[79]\d{8}$/', $s) ? $s : null;
    }
}
