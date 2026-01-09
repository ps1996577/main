<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $run->run_name ?? 'Playwright run' }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $run->created_at->format('Y-m-d H:i') }}
                    @if($run->branch)
                        · {{ $run->branch }}
                    @endif
                    @if($run->commit_sha)
                        · {{ $run->commit_sha }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $badge = match ($run->status) {
                        'passed' => ['bg' => 'bg-green-100', 'fg' => 'text-green-800', 'label' => 'PASS'],
                        'failed' => ['bg' => 'bg-rose-100', 'fg' => 'text-rose-800', 'label' => 'FAIL'],
                        default => ['bg' => 'bg-gray-200', 'fg' => 'text-gray-700', 'label' => 'UNKNOWN'],
                    };
                @endphp
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $badge['bg'] }} {{ $badge['fg'] }}">
                    {{ $badge['label'] }}
                </span>
                <a href="{{ route('admin.playwright-reports.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                    Wgraj kolejny
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow rounded-xl p-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="text-xs text-gray-500 uppercase tracking-wider">Total</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ $run->total }}</div>
                    </div>
                    <div class="rounded-lg bg-green-50 p-4">
                        <div class="text-xs text-green-800 uppercase tracking-wider">Passed</div>
                        <div class="text-2xl font-semibold text-green-900">{{ $run->passed }}</div>
                    </div>
                    <div class="rounded-lg bg-rose-50 p-4">
                        <div class="text-xs text-rose-800 uppercase tracking-wider">Failed</div>
                        <div class="text-2xl font-semibold text-rose-900">{{ $run->failed }}</div>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="text-xs text-gray-500 uppercase tracking-wider">Skipped</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ $run->skipped }}</div>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-4">
                        <div class="text-xs text-amber-800 uppercase tracking-wider">Flaky</div>
                        <div class="text-2xl font-semibold text-amber-900">{{ $run->flaky }}</div>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="text-xs text-gray-500 uppercase tracking-wider">Źródło</div>
                        <div class="text-sm font-semibold text-gray-900 mt-1">{{ $run->source }}</div>
                        @if($run->ci_url)
                            <a href="{{ $run->ci_url }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium break-all">CI link</a>
                        @endif
                        @if($run->external_id)
                            <div class="mt-2 text-xs text-gray-500">runId: <span class="font-mono">{{ $run->external_id }}</span></div>
                        @endif
                        <div class="mt-2">
                            <a href="{{ route('admin.playwright-reports.download', $run) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                Pobierz JSON
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Testy</h3>
                        <p class="text-sm text-gray-500">Widok w stylu SorryCypress: lista runów → szczegóły, z filtrowaniem i podglądem błędów.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.playwright-reports.show', $run) }}"
                           class="px-3 py-1 rounded-full text-xs font-semibold border {{ ($filter ?? 'all') === 'all' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-800 border-gray-300' }}">
                            Wszystkie
                        </a>
                        <a href="{{ route('admin.playwright-reports.show', [$run, 'filter' => 'failed']) }}"
                           class="px-3 py-1 rounded-full text-xs font-semibold border {{ ($filter ?? 'all') === 'failed' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-800 border-gray-300' }}">
                            Failed
                        </a>
                        <a href="{{ route('admin.playwright-reports.show', [$run, 'filter' => 'flaky']) }}"
                           class="px-3 py-1 rounded-full text-xs font-semibold border {{ ($filter ?? 'all') === 'flaky' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-800 border-gray-300' }}">
                            Flaky
                        </a>
                    </div>
                </div>

                @if(!empty($byFile))
                    <div class="px-6 py-4 border-b bg-gray-50">
                        <div class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Top pliki</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_slice($byFile, 0, 8, true) as $file => $count)
                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white border border-gray-200 text-gray-800">
                                    <span class="font-mono">{{ $file }}</span>
                                    <span class="text-gray-500">{{ $count }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="divide-y">
                    @forelse($tests as $t)
                        @php
                            $c = match ($t['status']) {
                                'passed' => ['dot' => 'bg-green-500', 'fg' => 'text-green-800', 'bg' => 'bg-green-50'],
                                'failed' => ['dot' => 'bg-rose-500', 'fg' => 'text-rose-800', 'bg' => 'bg-rose-50'],
                                'flaky' => ['dot' => 'bg-amber-500', 'fg' => 'text-amber-900', 'bg' => 'bg-amber-50'],
                                default => ['dot' => 'bg-gray-400', 'fg' => 'text-gray-800', 'bg' => 'bg-gray-50'],
                            };
                        @endphp
                        <div class="px-6 py-4 {{ $c['bg'] }}">
                            <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $c['dot'] }}"></span>
                                    <p class="font-semibold text-gray-900 truncate">{{ $t['title'] }}</p>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    @if($t['project'])
                                        {{ $t['project'] }}
                                    @endif
                                    @if($t['location'])
                                        @if($t['project'])
                                            ·
                                        @endif
                                        {{ $t['location'] }}
                                    @endif
                                    @if(($t['attempts'] ?? 0) > 1)
                                        · attempts: {{ $t['attempts'] }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xs font-semibold uppercase {{ $c['fg'] }}">{{ $t['status'] }}</div>
                                <div class="text-xs text-gray-500">{{ (int) ceil(($t['durationMs'] ?? 0) / 1000) }}s</div>
                            </div>
                        </div>
                            @if(!empty($t['errors']))
                                <div class="mt-3 text-xs text-gray-800">
                                    <div class="font-semibold">Błąd</div>
                                    <pre class="mt-1 whitespace-pre-wrap break-words bg-white/70 border border-gray-200 rounded-lg p-3 overflow-auto">{{ $t['errors'][0] }}</pre>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500">Brak testów w raporcie.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

