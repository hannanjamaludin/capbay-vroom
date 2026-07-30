<?php

namespace App\Models;

use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vehicle_id',
    'promotion_id',
    'customer_name',
    'email',
    'phone',
    'status',
    'registered_at',
    'test_drive_scheduled_at',
    'test_drive_completed_at',
    'loan_approved_at',
    'down_payment_sen',
    'vehicle_price_sen',
    'applied_discount_sen',
    'final_price_sen',
    'loan_amount_sen',
    'purchased_at',
    'cancelled_at',
])]
class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory;

    /** @return BelongsTo<Vehicle, $this> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** @return BelongsTo<Promotion, $this> */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'test_drive_scheduled_at' => 'datetime',
            'test_drive_completed_at' => 'datetime',
            'loan_approved_at' => 'datetime',
            'purchased_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
