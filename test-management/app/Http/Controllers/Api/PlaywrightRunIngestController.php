<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaywrightRun;
use App\Support\Playwright\PlaywrightJsonParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlaywrightRunIngestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        /** @var mixed $payload */
        $payload = $request->json()->all();
        if (! is_array($payload)) {
            $payload = $request->all();
        }

        $report = $payload['report'] ?? $payload;
        $meta = is_array(($payload['meta'] ?? null)) ? $payload['meta'] : [];

        if (! is_array($report) || ! isset($report['suites'])) {
            return response()->json([
                'message' => 'Invalid payload. Expected Playwright JSON report.',
            ], 422);
        }

        $summary = PlaywrightJsonParser::summarize($report);

        $run = PlaywrightRun::create([
            'id' => (string) Str::uuid(),
            'created_by' => null,
            'source' => 'api',
            'run_name' => isset($meta['runName']) && is_string($meta['runName']) ? $meta['runName'] : null,
            'branch' => isset($meta['branch']) && is_string($meta['branch']) ? $meta['branch'] : null,
            'commit_sha' => isset($meta['commitSha']) && is_string($meta['commitSha']) ? $meta['commitSha'] : null,
            'ci_url' => isset($meta['ciUrl']) && is_string($meta['ciUrl']) ? $meta['ciUrl'] : null,
            'started_at' => $summary['startedAt'],
            'finished_at' => $summary['finishedAt'],
            'status' => $summary['status'],
            'total' => $summary['total'],
            'passed' => $summary['passed'],
            'failed' => $summary['failed'],
            'skipped' => $summary['skipped'],
            'flaky' => $summary['flaky'],
            'report' => $report,
        ]);

        return response()->json([
            'id' => $run->id,
            'status' => $run->status,
            'total' => $run->total,
            'passed' => $run->passed,
            'failed' => $run->failed,
            'skipped' => $run->skipped,
            'flaky' => $run->flaky,
        ]);
    }
}

