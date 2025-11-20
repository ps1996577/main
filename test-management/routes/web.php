<?php

use App\Http\Controllers\Admin\CustomFieldController as AdminCustomFieldController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestCaseController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('test-cases', TestCaseController::class);
    Route::resource('folders', FolderController::class);

    Route::get('import-export', [ImportExportController::class, 'index'])->name('import-export.index');
    Route::post('import-export/import', [ImportExportController::class, 'import'])->name('import-export.import');
    Route::get('import-export/export', [ImportExportController::class, 'export'])->name('import-export.export');

    Route::prefix('admin')->as('admin.')->middleware('admin')->group(function () {
        Route::resource('users', AdminUserController::class)->except('show');
        Route::resource('custom-fields', AdminCustomFieldController::class)->except('show');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
