<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    public const CATEGORY_OPTIONS = ['Pria', 'Wanita', 'Unisex'];

    protected $fillable = [
        'name',
        'price',
        'category',
        'image',
        'description',
        'images',
        'sizes',
        'stock',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'price' => 'integer',
        'images' => 'array',
        'sizes' => 'array',
        'stock' => 'array',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public static function normalizeStock(?array $stock): array
    {
        return collect($stock ?? [])
            ->mapWithKeys(fn ($quantity, $size) => [$size => max((int) $quantity, 0)])
            ->all();
    }

    public static function availableSizesFromStock(?array $stock): array
    {
        $normalizedStock = self::normalizeStock($stock);

        return array_keys(array_filter($normalizedStock, fn (int $quantity) => $quantity > 0));
    }

    public function deleteStoredMedia(): void
    {
        $paths = collect([$this->image, ...($this->images ?? [])])
            ->filter(fn (?string $path) => filled($path) && !str_starts_with($path, 'http'))
            ->values()
            ->all();

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }

    public function getStockForSize(?string $size): int
    {
        if (!is_array($this->stock) || $size === null) {
            return 0;
        }

        return max((int) ($this->stock[$size] ?? 0), 0);
    }

    public function isInStock(): bool
    {
        if (!is_array($this->stock) || $this->stock === []) {
            return true;
        }

        return collect($this->stock)->sum(fn ($quantity) => max((int) $quantity, 0)) > 0;
    }

    public function hasLowStock(int $threshold = 5): bool
    {
        if (!is_array($this->stock) || $this->stock === []) {
            return false;
        }

        return collect($this->stock)->contains(fn ($quantity) => (int) $quantity <= $threshold);
    }
}
