<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STALE_PENDING_DAYS = 3;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const SUCCESS_STATUSES = [
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'user_id',
        'order_number',
        'total_price',
        'shipping_cost',
        'courier',
        'status',
        'shipping_address',
        'phone',
        'notes',
        'voucher_code',
        'discount_amount',
    ];

    protected $casts = [
        'total_price' => 'integer',
        'shipping_cost' => 'integer',
        'discount_amount' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeSuccessful(Builder $query): void
    {
        $query->whereIn('status', self::SUCCESS_STATUSES);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', self::STATUS_PENDING);
    }

    public function scopeStalePending(Builder $query, int $days = self::STALE_PENDING_DAYS): void
    {
        $query
            ->pending()
            ->where('created_at', '<=', now()->subDays($days));
    }

    public function getSubtotalAttribute(): int
    {
        return max($this->total_price - $this->shipping_cost + $this->discount_amount, 0);
    }

    public function canBeCancelled(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
