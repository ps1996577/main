<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaywrightRun extends Model
{
    protected $table = 'playwright_runs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'created_by',
        'source',
        'run_name',
        'branch',
        'commit_sha',
        'ci_url',
        'started_at',
        'finished_at',
        'status',
        'total',
        'passed',
        'failed',
        'skipped',
        'flaky',
        'report',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'report' => 'array',
            'total' => 'integer',
            'passed' => 'integer',
            'failed' => 'integer',
            'skipped' => 'integer',
            'flaky' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

