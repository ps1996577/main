<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm uppercase tracking-wide text-gray-500 font-semibold">Przestrzenie jakości</p>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    Inteligentne checklisty zespołów
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Twórz niezależne tabele podobne do Confluence, dodawaj/układaj kolumny, wiersze
                    oraz kolejne sekcje dla każdego zespołu.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:border-gray-400 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        x-data
                        x-on:click="$dispatch('reset-active-checklist')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4.5 12a7.5 7.5 0 0112.712-5.303l1.288 1.303m-1.288-1.303v-3.75m0 3.75h-3.75m9.75 9a7.5 7.5 0 01-12.712 5.303L9.75 18.75m-1.5 1.5H12" />
                    </svg>
                    Wyczyść bieżącą zakładkę
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        [x-cloak] {
            display: none !important;
        }
        textarea[data-auto-resize] {
            resize: vertical;
            min-height: 3.25rem;
        }
    </style>

    <div class="py-10" x-data="checklistWorkbench()" x-init="boot()" x-cloak>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <section class="bg-white shadow rounded-2xl p-6 sm:p-8 border border-gray-100">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Wybierz obszar</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Każda zakładka przechowuje swoje własne tabele i układ – zmiany są zapisywane lokalnie,
                            więc możesz śmiało wrócić do pracy później.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:border-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                x-on:click="addTableToEnd()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                 stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 6v12m6-6H6" />
                            </svg>
                            Dodaj tabelę na końcu
                        </button>
                        <button type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:border-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                x-on:click="exportActiveTab()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                 stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 16.5v-9m0 0L8.25 11.25M12 7.5l3.75 3.75M4.5 15.75v1.125A2.625 2.625 0 007.125 19.5h9.75A2.625 2.625 0 0019.5 16.875V15.75" />
                            </svg>
                            Eksportuj do JSON
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mt-6">
                    <template x-for="tab in tabs" :key="tab.slug">
                        <button type="button"
                                class="flex-1 min-w-[180px] rounded-2xl border px-5 py-4 text-left transition focus:outline-none focus:ring-2 focus:ring-offset-2"
                                :class="tab.slug === activeTab
                                    ? 'border-transparent bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white shadow-lg'
                                    : 'border-gray-200 text-gray-700 hover:border-gray-300 hover:bg-gray-50'"
                                x-on:click="setActiveTab(tab.slug)">
                            <p class="text-xs uppercase tracking-widest font-semibold opacity-80">Zakładka</p>
                            <p class="text-lg font-semibold mt-1" x-text="tab.name"></p>
                            <p class="text-sm mt-1 opacity-80" x-text="tab.description"></p>
                        </button>
                    </template>
                </div>
            </section>

            <template x-if="activeTabData()">
                <div class="space-y-8">
                    <template x-for="(table, tableIndex) in activeTabData().tables" :key="table.id">
                        <section class="bg-white shadow rounded-2xl border border-gray-100 p-6 sm:p-8 space-y-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="w-full">
                                    <p class="text-xs font-semibold uppercase text-gray-500 tracking-wider">
                                        Tablica {{ tableIndex + 1 }}
                                    </p>
                                    <input type="text"
                                           class="mt-2 w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-lg font-semibold text-gray-900"
                                           placeholder="Nadaj nazwę tabeli"
                                           x-model="table.title"
                                           x-on:input.debounce.300ms="persist()" />
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button"
                                            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:border-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            x-on:click="addTable('before', tableIndex)">
                                        Dodaj tabelę powyżej
                                    </button>
                                    <button type="button"
                                            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:border-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            x-on:click="addTable('after', tableIndex)">
                                        Dodaj tabelę poniżej
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto border border-gray-100 rounded-2xl">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <template x-for="(column, columnIndex) in table.columns" :key="columnIndex">
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                                    <div class="flex items-center gap-2">
                                                        <input type="text"
                                                               class="w-full rounded-lg border border-transparent bg-white/70 px-2 py-1.5 text-sm font-semibold text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                                               placeholder="Nazwa kolumny"
                                                               x-model="table.columns[columnIndex]"
                                                               x-on:input.debounce.300ms="persist()" />
                                                        <button type="button"
                                                                class="text-gray-400 hover:text-rose-500"
                                                                x-show="table.columns.length > 1"
                                                                x-on:click="removeColumn(table, columnIndex)">
                                                            <span class="sr-only">Usuń kolumnę</span>
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </th>
                                            </template>
                                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide w-24">
                                                Akcje
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <template x-for="(row, rowIndex) in table.rows" :key="row.id">
                                            <tr class="align-top">
                                                <template x-for="(cell, columnIndex) in row.cells" :key="`${row.id}-${columnIndex}`">
                                                    <td class="px-4 py-3 align-top">
                                                        <textarea
                                                            data-auto-resize
                                                            class="w-full rounded-xl border border-transparent bg-gray-50 px-3 py-2 text-sm text-gray-800 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition"
                                                            placeholder="Wpisz szczegóły"
                                                            x-model="table.rows[rowIndex].cells[columnIndex]"
                                                            x-on:input.debounce.400ms="persist()"
                                                        ></textarea>
                                                    </td>
                                                </template>
                                                <td class="px-3 py-3 align-top">
                                                    <div class="flex flex-col gap-2">
                                                        <button type="button"
                                                                class="text-xs font-semibold text-gray-500 hover:text-gray-900"
                                                                x-on:click="addRowAfter(table, rowIndex)">
                                                            Dodaj pod spodem
                                                        </button>
                                                        <button type="button"
                                                                class="text-xs font-semibold text-gray-500 hover:text-gray-900"
                                                                x-on:click="duplicateRow(table, rowIndex)">
                                                            Duplikuj
                                                        </button>
                                                        <button type="button"
                                                                class="text-xs font-semibold text-rose-500 hover:text-rose-600"
                                                                x-show="table.rows.length > 1"
                                                                x-on:click="removeRow(table, rowIndex)">
                                                            Usuń wiersz
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-gray-100 pt-4">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button"
                                            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:border-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            x-on:click="addColumn(table)">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                        </svg>
                                        Dodaj kolumnę
                                    </button>
                                    <button type="button"
                                            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:border-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            x-on:click="addRow(table)">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                        </svg>
                                        Dodaj wiersz
                                    </button>
                                </div>
                                <button type="button"
                                        class="text-sm font-semibold text-rose-500 hover:text-rose-600"
                                        x-on:click="resetTable(table)">
                                    Wyczyść tabelę
                                </button>
                            </div>
                        </section>
                    </template>
                </div>
            </template>

            <template x-if="!activeTabData()">
                <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-500">
                    Brak danych do wyświetlenia. Dodaj nową tabelę lub wybierz istniejącą zakładkę.
                </div>
            </template>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checklistWorkbench', () => ({
                storageKey: 'checklist-workbench-v1',
                tabs: [],
                activeTab: null,

                boot() {
                    const stored = this.readStorage();
                    if (stored?.length) {
                        this.tabs = stored;
                    } else {
                        this.tabs = this.defaultTabs();
                        this.persist();
                    }

                    this.activeTab = this.tabs[0]?.slug ?? null;

                    window.addEventListener('reset-active-checklist', () => this.resetActiveTab());
                },

                readStorage() {
                    try {
                        return JSON.parse(window.localStorage.getItem(this.storageKey) ?? 'null');
                    } catch (error) {
                        console.warn('Nie udało się odczytać checklisty', error);
                        return null;
                    }
                },

                persist() {
                    try {
                        window.localStorage.setItem(this.storageKey, JSON.stringify(this.tabs));
                    } catch (error) {
                        console.warn('Nie udało się zapisać checklisty', error);
                    }
                },

                defaultTabs() {
                    return [
                        {
                            slug: 'tytani',
                            name: 'Checklista Tytani',
                            description: 'Szybkie przygotowanie i zamknięcie sprintu w zespole Tytani.',
                            tables: [
                                this.createTable({
                                    title: 'Start sprintu',
                                    columns: ['Obszar', 'Właściciel', 'Status', 'Uwagi'],
                                    rows: [
                                        { cells: ['Akceptacja zakresu', 'Scrum Master', 'Do zrobienia', 'Potwierdź listę user stories'] },
                                        { cells: ['Plan testowy', 'QA Lead', 'W toku', 'Zdefiniuj krytyczne scenariusze'] },
                                    ],
                                }),
                                this.createTable({
                                    title: 'Stabilizacja jakości',
                                    columns: ['Kryterium', 'Data', 'Wynik', 'Następne kroki'],
                                    rows: [
                                        { cells: ['Testy regresyjne', '', '', ''] },
                                        { cells: ['Audyt dostępności', '', '', ''] },
                                    ],
                                }),
                            ],
                        },
                        {
                            slug: 'dziki',
                            name: 'Checklista Dziki',
                            description: 'Ekspresowe eksperymenty i rollout funkcji w zespole Dziki.',
                            tables: [
                                this.createTable({
                                    title: 'Eksperyment funkcjonalny',
                                    columns: ['Hipoteza', 'Osoba', 'Miara sukcesu', 'Ryzyka'],
                                    rows: [
                                        { cells: ['Dodanie mikro-interakcji zwiększy konwersję', 'UX', '', ''] },
                                        { cells: ['Refaktoryzacja serwisu skróci TT', 'Backend', '', 'Zachować kompatybilność API'] },
                                    ],
                                }),
                                this.createTable({
                                    title: 'Rollout produkcyjny',
                                    columns: ['Krok', 'Status', 'Właściciel'],
                                    rows: [
                                        { cells: ['Feature flagi', 'Planowany', 'DevOps'] },
                                        { cells: ['Monitoring po wdrożeniu', '', ''] },
                                    ],
                                }),
                            ],
                        },
                    ];
                },

                createTable(config = {}) {
                    const columns = Array.isArray(config.columns) && config.columns.length
                        ? [...config.columns]
                        : ['Zadanie', 'Status', 'Uwagi'];

                    const baseRows = Array.isArray(config.rows) && config.rows.length
                        ? config.rows.map(row => this.createRow(columns.length, row.cells))
                        : [this.createRow(columns.length), this.createRow(columns.length)];

                    return {
                        id: this.uid(),
                        title: config.title ?? 'Nowa tabela',
                        columns,
                        rows: baseRows,
                    };
                },

                createRow(columnCount, preset = []) {
                    const cells = Array.from({ length: columnCount }, (_, index) => preset?.[index] ?? '');
                    return { id: this.uid(), cells };
                },

                uid() {
                    return typeof crypto !== 'undefined' && crypto.randomUUID
                        ? crypto.randomUUID()
                        : `tbl-${Date.now()}-${Math.random().toString(36).slice(2, 9)}`;
                },

                activeTabData() {
                    return this.tabs.find(tab => tab.slug === this.activeTab) ?? null;
                },

                setActiveTab(slug) {
                    this.activeTab = slug;
                },

                addTable(position, referenceIndex) {
                    const tab = this.activeTabData();
                    if (!tab) return;

                    const newTable = this.createTable({ title: `Nowa tabela ${tab.tables.length + 1}` });
                    const offset = position === 'before' ? 0 : 1;
                    tab.tables.splice(referenceIndex + offset, 0, newTable);
                    this.persist();
                },

                addTableToEnd() {
                    const tab = this.activeTabData();
                    if (!tab) return;

                    tab.tables.push(this.createTable({ title: `Nowa tabela ${tab.tables.length + 1}` }));
                    this.persist();
                },

                resetActiveTab() {
                    const tab = this.activeTabData();
                    if (!tab) return;

                    const defaults = this.defaultTabs().find(defaultTab => defaultTab.slug === tab.slug);
                    if (!defaults) return;

                    tab.tables = defaults.tables;
                    this.persist();
                },

                addColumn(table) {
                    table.columns.push(`Kolumna ${table.columns.length + 1}`);
                    table.rows.forEach(row => row.cells.push(''));
                    this.persist();
                },

                removeColumn(table, columnIndex) {
                    if (table.columns.length === 1) return;
                    table.columns.splice(columnIndex, 1);
                    table.rows.forEach(row => row.cells.splice(columnIndex, 1));
                    this.persist();
                },

                addRow(table) {
                    table.rows.push(this.createRow(table.columns.length));
                    this.persist();
                },

                addRowAfter(table, rowIndex) {
                    table.rows.splice(rowIndex + 1, 0, this.createRow(table.columns.length));
                    this.persist();
                },

                duplicateRow(table, rowIndex) {
                    const currentRow = table.rows[rowIndex];
                    if (!currentRow) return;

                    const clone = this.createRow(table.columns.length, [...currentRow.cells]);
                    table.rows.splice(rowIndex + 1, 0, clone);
                    this.persist();
                },

                removeRow(table, rowIndex) {
                    if (table.rows.length === 1) return;
                    table.rows.splice(rowIndex, 1);
                    this.persist();
                },

                resetTable(table) {
                    table.columns = table.columns.map((_, index) => `Kolumna ${index + 1}`);
                    table.rows = [this.createRow(table.columns.length)];
                    this.persist();
                },

                exportActiveTab() {
                    const tab = this.activeTabData();
                    if (!tab) return;

                    const sanitized = {
                        name: tab.name,
                        description: tab.description,
                        tables: tab.tables.map(table => ({
                            title: table.title,
                            columns: table.columns,
                            rows: table.rows.map(row => row.cells),
                        })),
                    };

                    const blob = new Blob([JSON.stringify(sanitized, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `${tab.slug}-checklista.json`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                },
            }));
        });
    </script>
</x-app-layout>
