<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_id', 'order_id', 'rating', 'comment', 'is_approved'];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('is_approved', true);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('is_approved', false);
    }

    public function scopeForRating(Builder $query, ?int $rating): void
    {
        if ($rating !== null) {
            $query->where('rating', $rating);
        }
    }

    public function scopeSortBySelection(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'oldest' => $query->oldest(),
            'highest_rating' => $query->orderByDesc('rating')->latest(),
            'lowest_rating' => $query->orderBy('rating')->latest(),
            default => $query->latest(),
        };
    }
}
