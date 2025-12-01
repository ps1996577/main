<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'position',
        'created_by',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Folder, Folder>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Folder>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->with('children')
            ->withCount('testCases')
            ->orderBy('position')
            ->orderBy('name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TestCase>
     */
    public function testCases(): HasMany
    {
        return $this->hasMany(TestCase::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Folder>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getBreadcrumbAttribute(): string
    {
        $segments = collect();
        $current = $this;

        while ($current) {
            $segments->prepend($current->name);
            $current = $current->parent;
        }

        return $segments->implode(' / ');
    }
}
