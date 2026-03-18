<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function deleteStoredImage(): void
    {
        if ($this->image && !str_starts_with($this->image, 'http')) {
            Storage::disk('public')->delete($this->image);
        }
    }
}
