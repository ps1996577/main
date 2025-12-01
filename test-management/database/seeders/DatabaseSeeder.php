<?php

namespace Database\Seeders;

use App\Models\CustomField;
use App\Models\Folder;
use App\Models\TestCase;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => 'Password123!',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $tester = User::updateOrCreate(
            ['email' => 'tester@example.com'],
            [
                'name' => 'QA Tester',
                'password' => 'Password123!',
                'role' => 'tester',
                'email_verified_at' => now(),
            ]
        );

        $smokeFolder = Folder::create([
            'name' => 'Smoke Tests',
            'description' => 'Szybkie scenariusze potwierdzające działanie krytycznych funkcji.',
            'created_by' => $admin->id,
        ]);

        $loginFolder = Folder::create([
            'name' => 'Logowanie',
            'description' => 'Przypadki dotyczące procesu logowania użytkownika.',
            'parent_id' => $smokeFolder->id,
            'created_by' => $admin->id,
        ]);

        $priorityField = CustomField::create([
            'name' => 'Priorytet',
            'slug' => 'priorytet',
            'field_type' => 'text',
            'position' => 1,
            'created_by' => $admin->id,
        ]);

        $componentField = CustomField::create([
            'name' => 'Komponent',
            'slug' => 'komponent',
            'field_type' => 'text',
            'position' => 2,
            'created_by' => $admin->id,
        ]);

        $testCase = TestCase::create([
            'case_key' => 'TC-0001',
            'title' => 'Użytkownik może zalogować się poprawnymi danymi',
            'folder_id' => $loginFolder->id,
            'preconditions' => 'Użytkownik istnieje w systemie i ma aktywne konto.',
            'steps' => "- Wejdź na stronę logowania\n- Wprowadź poprawny e-mail i hasło\n- Kliknij „Zaloguj”",
            'expected_result' => 'System przekierowuje do pulpitu użytkownika.',
            'acceptance_criteria' => 'Widoczna nazwa użytkownika w prawym górnym rogu, brak błędów.',
            'additional_notes' => 'Scenariusz wykonywany przy każdym wydaniu.',
            'status' => 'ready',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $testCase->customFieldValues()->createMany([
            [
                'custom_field_id' => $priorityField->id,
                'value' => 'Wysoki',
            ],
            [
                'custom_field_id' => $componentField->id,
                'value' => 'Moduł logowania',
            ],
        ]);
    }
}
