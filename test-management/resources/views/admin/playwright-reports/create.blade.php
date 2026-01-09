<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Wgraj raport Playwright') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-xl p-6 space-y-6">
                <form method="POST" action="{{ route('admin.playwright-reports.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="run_name" value="Nazwa runu (opcjonalnie)" />
                        <x-text-input id="run_name" name="run_name" type="text" class="mt-1 block w-full" :value="old('run_name')" />
                        <x-input-error :messages="$errors->get('run_name')" class="mt-2" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="branch" value="Branch (opcjonalnie)" />
                            <x-text-input id="branch" name="branch" type="text" class="mt-1 block w-full" :value="old('branch')" />
                            <x-input-error :messages="$errors->get('branch')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="commit_sha" value="Commit SHA (opcjonalnie)" />
                            <x-text-input id="commit_sha" name="commit_sha" type="text" class="mt-1 block w-full" :value="old('commit_sha')" />
                            <x-input-error :messages="$errors->get('commit_sha')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="ci_url" value="Link do CI (opcjonalnie)" />
                        <x-text-input id="ci_url" name="ci_url" type="url" class="mt-1 block w-full" :value="old('ci_url')" />
                        <x-input-error :messages="$errors->get('ci_url')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="report" value="Plik raportu (Playwright JSON)" />
                        <input id="report" name="report" type="file" accept="application/json,.json"
                               class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        <p class="mt-2 text-xs text-gray-500">
                            Wygeneruj plik JSON np. przez Playwright: <span class="font-mono">npx playwright test --reporter=json &gt; playwright-report.json</span>
                        </p>
                        <x-input-error :messages="$errors->get('report')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>
                            Wgraj
                        </x-primary-button>
                        <a href="{{ route('admin.playwright-reports.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            Anuluj
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

