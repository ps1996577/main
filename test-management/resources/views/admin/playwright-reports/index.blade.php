<x-app-layout>
    @php
        use Illuminate\Support\Str;
    @endphp
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Playwright raport') }}
            </h2>
            <a href="{{ route('admin.playwright-reports.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                Wgraj raport
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-xl p-4 mb-6">
                <form method="GET" action="{{ route('admin.playwright-reports.index') }}" class="grid gap-3 md:grid-cols-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">Szukaj</label>
                        <input name="q" value="{{ request('q') }}" placeholder="run name / branch / commit / runId"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Wszystkie</option>
                            @foreach(['passed' => 'PASS', 'failed' => 'FAIL', 'unknown' => 'UNKNOWN'] as $k => $lbl)
                                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">Źródło</label>
                        <select name="source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Wszystkie</option>
                            @foreach(['upload' => 'upload', 'api' => 'api'] as $k => $lbl)
                                <option value="{{ $k }}" @selected(request('source') === $k)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4 flex items-center gap-3">
                        <button class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800">
                            Filtruj
                        </button>
                        <a href="{{ route('admin.playwright-reports.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Wyczyść</a>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Run</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wyniki</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Źródło</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($runs as $run)
                            <tr>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-semibold text-gray-900">
                                        <a href="{{ route('admin.playwright-reports.show', $run) }}" class="hover:text-indigo-600">
                                            {{ $run->run_name ?? ($run->external_id ?? $run->id) }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $run->created_at->format('Y-m-d H:i') }}
                                        @if($run->branch)
                                            · {{ $run->branch }}
                                        @endif
                                        @if($run->commit_sha)
                                            · {{ Str::limit($run->commit_sha, 8, '') }}
                                        @endif
                                        @if($run->external_id)
                                            · runId: {{ Str::limit($run->external_id, 20) }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
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
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <span class="font-semibold text-gray-900">{{ $run->passed }}</span> passed
                                    · <span class="font-semibold text-gray-900">{{ $run->failed }}</span> failed
                                    · <span class="font-semibold text-gray-900">{{ $run->skipped }}</span> skipped
                                    · <span class="font-semibold text-gray-900">{{ $run->flaky }}</span> flaky
                                    <div class="text-xs text-gray-500">Total: {{ $run->total }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $run->source }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right">
                                    <a href="{{ route('admin.playwright-reports.show', $run) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Szczegóły</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    Brak raportów. Kliknij „Wgraj raport”, żeby dodać pierwszy run.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $runs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

