<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_key',
        'title',
        'folder_id',
        'created_by',
        'updated_by',
        'preconditions',
        'steps',
        'expected_result',
        'acceptance_criteria',
        'additional_notes',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $testCase) {
            if (blank($testCase->case_key)) {
                $nextNumber = (int) (self::max('id') ?? 0) + 1;
                $testCase->case_key = sprintf('TC-%04d', $nextNumber);
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Folder, TestCase>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, TestCase>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, TestCase>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CustomFieldValue>
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    public function getCustomFieldValue(int $customFieldId): ?string
    {
        return optional(
            $this->customFieldValues->firstWhere('custom_field_id', $customFieldId)
        )->value;
    }
}
