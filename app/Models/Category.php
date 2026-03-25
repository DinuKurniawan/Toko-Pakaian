<?php

namespace App\Models;

use App\Support\MediaStorage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => MediaStorage::url($value)
        );
    }

    public function deleteStoredImage(): void
    {
        MediaStorage::delete($this->getRawOriginal('image'));
    }
}
