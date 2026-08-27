<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegulatoryFolder extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'description',
        'created_by',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RegulatoryFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(RegulatoryFolder::class, 'parent_id')->orderBy('name');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RegulatoryDocument::class, 'folder_id')->orderBy('original_name');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Breadcrumb from root to current
    public function breadcrumbs(): array
    {
        $crumbs = [];
        $current = $this;
        while ($current) {
            $crumbs[] = $current;
            $current = $current->parent;
        }
        return array_reverse($crumbs);
    }

    // Recursive tree with children
    public static function tree(?int $parentId = null): \Illuminate\Support\Collection
    {
        return static::where('parent_id', $parentId)
            ->with(['children', 'creator'])
            ->orderBy('name')
            ->get()
            ->map(function ($folder) {
                $folder->children_tree = static::tree($folder->id);
                return $folder;
            });
    }

    public function allDescendantIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->allDescendantIds());
        }
        return $ids;
    }

    public function documentCountRecursive(): int
    {
        $count = $this->documents()->count();
        foreach ($this->children as $child) {
            $count += $child->documentCountRecursive();
        }
        return $count;
    }
}
