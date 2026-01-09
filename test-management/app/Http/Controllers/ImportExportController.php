<?php

namespace App\Http\Controllers;

use App\Exports\TestCasesExport;
use App\Http\Requests\TestCaseImportRequest;
use App\Imports\TestCasesImport;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ImportExportController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $folders = Folder::with('parent')
            ->when(! $user->isAdmin(), fn ($query) => $query->where('created_by', $user->id))
            ->orderBy('name')
            ->get();

        return view('import-export.index', compact('folders'));
    }

    public function export(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $format = strtolower($request->input('format', 'xlsx'));
        $folderId = $request->integer('folder_id') ?: null;

        // SECURITY: prevent exporting other users' data by restricting folder selection.
        if ($folderId && ! $user->isAdmin()) {
            $ownsFolder = Folder::where('id', $folderId)
                ->where('created_by', $user->id)
                ->exists();
            if (! $ownsFolder) {
                abort(403);
            }
        }

        $filename = sprintf(
            'test-cases-%s.%s',
            now()->format('Ymd_His'),
            $format
        );

        $export = new TestCasesExport($user, $folderId);

        $writerType = match ($format) {
            'csv' => ExcelFormat::CSV,
            'xlsx' => ExcelFormat::XLSX,
            'xls' => ExcelFormat::XLS,
            default => ExcelFormat::XLSX,
        };

        return Excel::download($export, $filename, $writerType);
    }

    public function import(TestCaseImportRequest $request): RedirectResponse
    {
        $folderId = $request->input('folder_id');

        Excel::import(
            new TestCasesImport($request->user(), $folderId),
            $request->file('file')
        );

        return redirect()
            ->route('import-export.index')
            ->with('status', 'Plik został pomyślnie zaimportowany.');
    }
}
