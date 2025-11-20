<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Import i eksport danych') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 grid gap-8 md:grid-cols-2">
            <div class="bg-white shadow rounded-xl p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Eksport przypadków</h3>
                <p class="text-sm text-gray-600">Pobierz plik CSV lub XLSX z wybranymi przypadkami testowymi. Możesz zawęzić wyniki do konkretnego folderu.</p>
                <form method="GET" action="{{ route('import-export.export') }}" class="space-y-4">
                    <div>
                        <x-input-label for="export_folder_id" value="Folder" />
                        <select id="export_folder_id" name="folder_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Wszystkie</option>
                            @foreach($folders as $folder)
                                <option value="{{ $folder->id }}">{{ $folder->breadcrumb ?? $folder->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="format" value="Format pliku" />
                        <select id="format" name="format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="xlsx">Excel (.xlsx)</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <x-primary-button>Pobierz plik</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow rounded-xl p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Import przypadków</h3>
                <p class="text-sm text-gray-600">Załaduj plik CSV/XLSX zgodny z eksportem, aby szybko zaktualizować lub dodać nowe przypadki. Pola niestandardowe są dopasowywane po nazwie kolumny.</p>
                <form method="POST" action="{{ route('import-export.import') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="import_file" value="Plik z przypadkami" />
                        <input id="import_file" name="file" type="file" accept=".xlsx,.xls,.csv"
                               class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700">
                        <x-input-error :messages="$errors->get('file')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="import_folder_id" value="Przypisz do folderu (opcjonalnie)" />
                        <select id="import_folder_id" name="folder_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Zgodnie z plikiem</option>
                            @foreach($folders as $folder)
                                <option value="{{ $folder->id }}">{{ $folder->breadcrumb ?? $folder->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Jeśli pozostawisz puste, folder zostanie odczytany z kolumny „Folder”.</p>
                    </div>
                    <x-primary-button>Importuj</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
