<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'coupon_code', 'fixed_pay_birr', 'commission_birr',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'fixed_pay_birr' => 'decimal:2',
            'commission_birr' => 'decimal:2',
        ];
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isContractor(): bool
    {
        return $this->role === 'contractor';
    }

    public function assignedCustomers()
    {
        return $this->hasMany(Customer::class, 'assigned_to');
    }

    public function callLogs()
    {
        return $this->hasMany(CallLog::class, 'contractor_id');
    }
}
