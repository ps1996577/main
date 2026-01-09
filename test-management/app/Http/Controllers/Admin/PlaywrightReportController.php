<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlaywrightRun;
use App\Support\Playwright\PlaywrightJsonParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlaywrightReportController extends Controller
{
    public function index(): View
    {
        $runs = PlaywrightRun::query()
            ->latest()
            ->paginate(20);

        return view('admin.playwright-reports.index', compact('runs'));
    }

    public function create(): View
    {
        return view('admin.playwright-reports.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
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

        $run = PlaywrightRun::create([
            'id' => (string) Str::uuid(),
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
        ]);

        return redirect()
            ->route('admin.playwright-reports.show', $run)
            ->with('status', 'Raport Playwright został wgrany.');
    }

    public function show(PlaywrightRun $playwrightRun): View
    {
        $report = is_array($playwrightRun->report) ? $playwrightRun->report : [];
        $tests = PlaywrightJsonParser::flattenTests($report);

        return view('admin.playwright-reports.show', [
            'run' => $playwrightRun,
            'tests' => $tests,
        ]);
    }
}

