<?php

namespace App\Models;

use App\Support\MediaStorage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'cta_text',
        'cta_link',
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
