<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlaywrightRun;
use App\Support\Playwright\PlaywrightJsonParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlaywrightReportController extends Controller
{
    public function index(): View
    {
        $runs = PlaywrightRun::query()
            ->when(request('q'), function ($q): void {
                $term = (string) request('q');
                $q->where(function ($qq) use ($term): void {
                    $qq->where('run_name', 'like', "%{$term}%")
                        ->orWhere('branch', 'like', "%{$term}%")
                        ->orWhere('commit_sha', 'like', "%{$term}%")
                        ->orWhere('external_id', 'like', "%{$term}%");
                });
            })
            ->when(request('status'), fn ($q) => $q->where('status', request('status')))
            ->when(request('source'), fn ($q) => $q->where('source', request('source')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.playwright-reports.index', compact('runs'));
    }

    public function create(): View
    {
        return view('admin.playwright-reports.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'run_name' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'commit_sha' => ['nullable', 'string', 'max:64'],
            'ci_url' => ['nullable', 'string', 'max:2000'],
            'report' => ['required', 'file', 'max:51200'],
        ]);

        $file = $request->file('report');
        $raw = $file?->get();
        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        if (! is_array($decoded) || ! isset($decoded['suites'])) {
            return back()
                ->withInput()
                ->withErrors(['report' => 'Nieprawidłowy plik. Oczekiwano raportu Playwright w formacie JSON.']);
        }

        $summary = PlaywrightJsonParser::summarize($decoded);

        $externalId = $validated['external_id'] ?? null;
        if (is_string($externalId)) {
            $externalId = trim($externalId);
            if ($externalId === '') {
                $externalId = null;
            }
        } else {
            $externalId = null;
        }

        $data = [
            'created_by' => $request->user()->id,
            'source' => 'upload',
            'run_name' => $validated['run_name'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'commit_sha' => $validated['commit_sha'] ?? null,
            'ci_url' => $validated['ci_url'] ?? null,
            'started_at' => $summary['startedAt'],
            'finished_at' => $summary['finishedAt'],
            'status' => $summary['status'],
            'total' => $summary['total'],
            'passed' => $summary['passed'],
            'failed' => $summary['failed'],
            'skipped' => $summary['skipped'],
            'flaky' => $summary['flaky'],
            'report' => $decoded,
        ];

        if ($externalId) {
            $run = PlaywrightRun::query()->firstOrNew(['external_id' => $externalId]);
            if (! $run->exists) {
                $run->id = (string) Str::uuid();
                $run->external_id = $externalId;
            }
            $run->fill($data);
            $run->save();
        } else {
            $run = PlaywrightRun::create([
                'id' => (string) Str::uuid(),
            ] + $data);
        }

        return redirect()
            ->route('admin.playwright-reports.show', $run)
            ->with('status', 'Raport Playwright został wgrany.');
    }

    public function show(PlaywrightRun $playwrightRun): View
    {
        $report = is_array($playwrightRun->report) ? $playwrightRun->report : [];
        $tests = PlaywrightJsonParser::flattenTests($report);
        $filter = (string) request('filter', 'all');
        if ($filter === 'failed') {
            $tests = array_values(array_filter($tests, fn ($t) => ($t['status'] ?? '') === 'failed'));
        } elseif ($filter === 'flaky') {
            $tests = array_values(array_filter($tests, fn ($t) => ($t['status'] ?? '') === 'flaky'));
        }

        $byFile = [];
        foreach ($tests as $t) {
            $file = $t['file'] ?? null;
            $key = is_string($file) && $file !== '' ? $file : '(unknown)';
            $byFile[$key] = ($byFile[$key] ?? 0) + 1;
        }
        arsort($byFile);

        return view('admin.playwright-reports.show', [
            'run' => $playwrightRun,
            'tests' => $tests,
            'filter' => $filter,
            'byFile' => $byFile,
        ]);
    }

    public function download(PlaywrightRun $playwrightRun): Response
    {
        $json = json_encode($playwrightRun->report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $name = $playwrightRun->run_name ?: ($playwrightRun->external_id ?: $playwrightRun->id);
        $safe = preg_replace('/[^a-zA-Z0-9._-]+/', '-', (string) $name);
        $file = trim((string) $safe, '-') ?: 'playwright-report';

        return response($json ?: '{}', 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$file.'.json"',
        ]);
    }
}

