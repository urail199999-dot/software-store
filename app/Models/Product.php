<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['download_url'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'download_url' => 'encrypted', 'gallery' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublished(Builder $query): void
    {
        $ids = Category::publishedTree()->pluck('id');
        $query->where('is_active', true)->where(fn ($query) => $query->whereNull('category_id')->orWhereIn('category_id', $ids));
    }

    public function licenseKeys()
    {
        return $this->hasMany(LicenseKey::class);
    }

    public function availableKeys()
    {
        return $this->licenseKeys()->whereNull('order_id');
    }
}
