<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Podsumowanie jakości') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <section>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <x-dashboard-card label="Wszystkie przypadki" :value="$stats['total_cases']" icon="clipboard-document-check" />
                    <x-dashboard-card label="Gotowe" :value="$stats['ready_cases']" tone="success" icon="check-badge" />
                    <x-dashboard-card label="Szkice" :value="$stats['draft_cases']" tone="warning" icon="pencil-square" />
                    <x-dashboard-card label="Foldery" :value="$stats['folders']" icon="folder" />
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white shadow rounded-xl p-6 lg:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Najnowsze przypadki</h3>
                        <a href="{{ route('test-cases.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Zobacz wszystkie</a>
                    </div>
                    <div class="space-y-4">
                        @forelse($recentTestCases as $case)
                            <div class="flex items-start justify-between border-b pb-4 last:border-0 last:pb-0">
                                <div>
                                    <p class="text-sm text-gray-500">{{ $case->case_key }}</p>
                                    <p class="text-base font-semibold text-gray-900">
                                        <a href="{{ route('test-cases.show', $case) }}" class="hover:text-indigo-600">{{ $case->title }}</a>
                                    </p>
                                    <p class="text-sm text-gray-500">{{ optional($case->folder)->breadcrumb ?? 'Brak folderu' }}</p>
                                </div>
                                <x-status-badge :status="$case->status" />
                            </div>
                        @empty
                            <p class="text-gray-500">Brak przypadków do wyświetlenia.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow rounded-xl p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Statusy</h3>
                        <dl class="space-y-2">
                            @foreach(['ready' => 'Gotowe', 'draft' => 'Szkic', 'deprecated' => 'Wycofane'] as $key => $label)
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm text-gray-500">{{ $label }}</dt>
                                    <dd class="text-base font-semibold text-gray-900">{{ $statusBreakdown[$key] ?? 0 }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Najaktywniejsze foldery</h3>
                        <ul class="space-y-3">
                            @forelse($casesByFolder as $folder)
                                <li class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">{{ $folder->breadcrumb }}</span>
                                    <span class="text-gray-900 font-semibold">{{ $folder->test_cases_count }}</span>
                                </li>
                            @empty
                                <li class="text-gray-500 text-sm">Brak folderów.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
