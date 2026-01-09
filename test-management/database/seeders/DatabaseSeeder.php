<?php

namespace Database\Seeders;

use App\Models\CustomField;
use App\Models\Folder;
use App\Models\TestCase;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // SECURITY: Never seed predictable admin credentials.
        // If you want to create a bootstrap admin user, provide explicit env vars:
        // - SEED_ADMIN_EMAIL
        // - SEED_ADMIN_PASSWORD
        // - (optional) SEED_ADMIN_NAME
        $seedAdminEmail = env('SEED_ADMIN_EMAIL');
        $seedAdminPassword = env('SEED_ADMIN_PASSWORD');
        $seedAdminName = env('SEED_ADMIN_NAME', 'Administrator');

        if (filled($seedAdminEmail) && filled($seedAdminPassword)) {
            User::updateOrCreate(
                ['email' => $seedAdminEmail],
                [
                    'name' => $seedAdminName,
                    'password' => $seedAdminPassword,
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ]
            );
        }

        // Sample/demo data is only seeded in local/testing environments.
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@local.test'],
            [
                'name' => 'Administrator (Local)',
                'password' => Str::password(24),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::factory()->create([
            'name' => 'QA Tester',
            'email' => 'tester@local.test',
            'role' => 'tester',
        ]);

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
