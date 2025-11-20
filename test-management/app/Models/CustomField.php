<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'field_type',
        'is_required',
        'is_active',
        'options',
        'position',
        'created_by',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $field) {
            if (blank($field->slug)) {
                $field->slug = Str::slug($field->name);
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CustomFieldValue>
     */
    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, CustomField>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('position')->orderBy('name');
    }
}
