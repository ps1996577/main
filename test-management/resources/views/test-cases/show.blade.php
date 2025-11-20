<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500">{{ $testCase->case_key }}</p>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    {{ $testCase->title }}
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <x-status-badge :status="$testCase->status" />
                <a href="{{ route('test-cases.edit', $testCase) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-indigo-600 hover:bg-indigo-500">
                    Edytuj
                </a>
                <form method="POST" action="{{ route('test-cases.destroy', $testCase) }}" onsubmit="return confirm('Usunąć ten przypadek?')">
                    @csrf
                    @method('DELETE')
                    <x-secondary-button type="submit" class="border-rose-200 text-rose-600 hover:bg-rose-50">Usuń</x-secondary-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white shadow rounded-xl p-6 grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm text-gray-500">Folder</p>
                    <p class="text-base font-semibold text-gray-900">{{ optional($testCase->folder)->breadcrumb ?? 'Brak' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Ostatnia aktualizacja</p>
                    <p class="text-base font-semibold text-gray-900">{{ $testCase->updated_at?->diffForHumans() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Autor</p>
                    <p class="text-base font-semibold text-gray-900">{{ $testCase->creator->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Edytował</p>
                    <p class="text-base font-semibold text-gray-900">{{ $testCase->updater->name ?? '—' }}</p>
                </div>
            </div>

            <div class="bg-white shadow rounded-xl p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Wymagania wstępne</h3>
                    <p class="text-gray-700 whitespace-pre-line">{{ $testCase->preconditions ?: '—' }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Kroki testowe</h3>
                    <p class="text-gray-700 whitespace-pre-line">{{ $testCase->steps }}</p>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Oczekiwany rezultat</h3>
                        <p class="text-gray-700 whitespace-pre-line">{{ $testCase->expected_result }}</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Kryteria zaliczenia</h3>
                        <p class="text-gray-700 whitespace-pre-line">{{ $testCase->acceptance_criteria ?: '—' }}</p>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Uwagi dodatkowe</h3>
                    <p class="text-gray-700 whitespace-pre-line">{{ $testCase->additional_notes ?: '—' }}</p>
                </div>
            </div>

            @if($customFields->count())
                <div class="bg-white shadow rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Pola niestandardowe</h3>
                    <dl class="grid gap-4 md:grid-cols-2">
                        @foreach($customFields as $field)
                            <div>
                                <dt class="text-sm text-gray-500">{{ $field->name }}</dt>
                                <dd class="text-base text-gray-900 whitespace-pre-line">
                                    {{ $testCase->getCustomFieldValue($field->id) ?? '—' }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
