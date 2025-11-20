<?php

namespace App\Http\Controllers;

use App\Exports\TestCasesExport;
use App\Http\Requests\TestCaseImportRequest;
use App\Imports\TestCasesImport;
use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ImportExportController extends Controller
{
    public function index(): View
    {
        $folders = Folder::with('parent')->orderBy('name')->get();

        return view('import-export.index', compact('folders'));
    }

    public function export(Request $request)
    {
        $format = strtolower($request->input('format', 'xlsx'));
        $folderId = $request->integer('folder_id') ?: null;

        $filename = sprintf(
            'test-cases-%s.%s',
            now()->format('Ymd_His'),
            $format
        );

        $export = new TestCasesExport($folderId);

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
