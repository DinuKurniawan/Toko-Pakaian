<?php

namespace App\Models;

use App\Support\MediaStorage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => MediaStorage::url($value)
        );
    }

    protected function images(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => collect(is_array($value) ? $value : (json_decode((string) ($value ?? '[]'), true) ?: []))
                ->map(fn (?string $path) => MediaStorage::url($path))
                ->filter()
                ->values()
                ->all()
        );
    }

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
        $rawImages = $this->getRawOriginal('images');

        $paths = array_merge(
            [$this->getRawOriginal('image')],
            is_array($rawImages) ? $rawImages : (json_decode((string) ($rawImages ?? '[]'), true) ?: [])
        );

        MediaStorage::delete($paths);
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
