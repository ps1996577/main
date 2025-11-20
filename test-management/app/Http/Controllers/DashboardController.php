<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\Folder;
use App\Models\TestCase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $stats = [
            'total_cases' => TestCase::count(),
            'ready_cases' => TestCase::where('status', 'ready')->count(),
            'draft_cases' => TestCase::where('status', 'draft')->count(),
            'deprecated_cases' => TestCase::where('status', 'deprecated')->count(),
            'folders' => Folder::count(),
            'custom_fields' => CustomField::count(),
            'active_testers' => User::where('role', 'tester')->count(),
        ];

        $statusBreakdown = TestCase::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $casesByFolder = Folder::withCount('testCases')
            ->orderByDesc('test_cases_count')
            ->take(6)
            ->get();

        $recentTestCases = TestCase::with('folder')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'stats' => $stats,
            'statusBreakdown' => $statusBreakdown,
            'casesByFolder' => $casesByFolder,
            'recentTestCases' => $recentTestCases,
        ]);
    }
}
