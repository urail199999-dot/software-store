<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public static function publishedTree(): Collection
    {
        $all = static::orderBy('sort_order')->orderBy('name')->get();
        $visible = collect();
        $parents = [null];
        $seen = [];
        while ($parents !== []) {
            $next = [];
            foreach ($all as $category) {
                if ($category->is_active && ! isset($seen[$category->id]) && in_array($category->parent_id, $parents, true)) {
                    $visible->push($category);
                    $seen[$category->id] = true;
                    $next[] = $category->id;
                }
            }
            $parents = $next;
        }

        return $visible;
    }

    public function descendantIds(?Collection $tree = null): array
    {
        $tree ??= static::all();
        $ids = [$this->id];
        $front = [$this->id];
        while ($front !== []) {
            $next = [];
            foreach ($tree as $category) {
                if (in_array($category->parent_id, $front, true) && ! in_array($category->id, $ids, true)) {
                    $ids[] = $category->id;
                    $next[] = $category->id;
                }
            }
            $front = $next;
        }

        return $ids;
    }

    protected static function booted(): void
    {
        static::saving(function (self $category) {
            if ($category->parent_id && in_array((int) $category->parent_id, $category->exists ? $category->descendantIds() : [], true)) {
                throw ValidationException::withMessages(['parent_id' => 'Нельзя поместить раздел внутрь самого себя или своего подраздела.']);
            }
        });
    }
}
