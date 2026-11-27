<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class HelpdeskCategory extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'depth',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (HelpdeskCategory $category): void {
            if (blank($category->parent_id)) {
                $category->depth = 1;

                return;
            }

            $parent = HelpdeskCategory::query()->find($category->parent_id);

            if (! $parent) {
                return;
            }

            if ($parent->depth >= 3) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Una categoría de tercer nivel no puede tener subcategorías.',
                ]);
            }

            $category->depth = $parent->depth + 1;
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(HelpdeskTicket::class);
    }

    public function fullPath(): string
    {
        $segments = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($segments, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' › ', $segments);
    }

    /** @return array<int, string> */
    public static function optionsForSelect(): array
    {
        return self::query()
            ->with(['parent.parent'])
            ->where('is_active', true)
            ->orderBy('depth')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (self $category): array => [
                $category->getKey() => $category->fullPath(),
            ])
            ->all();
    }
}
