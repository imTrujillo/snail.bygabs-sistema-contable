<x-filament-panels::page>
    @php
        $periods = $this->getPeriods();
        $activePeriod = $this->getActivePeriod();
        $byYear = $periods->groupBy(fn ($p) => $p->start_date->year)->sortKeysDesc();
    @endphp

    <div class="space-y-8">
        {{-- Single calm status header (no warning triangle) --}}
        <div
            class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900"
        >
            <div class="border-b border-gray-100 bg-gradient-to-r from-primary-50/60 to-transparent px-6 py-5 dark:border-gray-800 dark:from-primary-950/40">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                            Período de trabajo
                        </h2>
                        <p class="mt-1 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                            @if ($activePeriod)
                                Estás registrando operaciones en
                                <span class="font-medium text-primary-800 dark:text-primary-200">
                                    {{ $activePeriod->start_date->locale('es')->isoFormat('MMMM YYYY') }}
                                </span>
                                @if ($activePeriod->name)
                                    <span class="text-gray-500">({{ $activePeriod->name }})</span>
                                @endif
                                .
                                Puedes cambiar el período en cualquier momento desde la cuadrícula.
                            @else
                                Todavía no hay un período seleccionado. Elige uno de los meses disponibles más abajo
                                para poder usar el sistema.
                            @endif
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        @if ($activePeriod)
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-primary-200/80 bg-primary-50 px-4 py-2 text-sm font-medium text-primary-800 dark:border-primary-800 dark:bg-primary-950 dark:text-primary-200"
                            >
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary-400 opacity-40"
                                    ></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-primary-500"></span>
                                </span>
                                En uso
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-dashed border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            >
                                <x-heroicon-m-calendar class="h-4 w-4 text-gray-400" />
                                Sin período seleccionado
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Periods grid grouped by year --}}
        @forelse ($byYear as $year => $yearPeriods)
            <div>
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                    {{ $year }}
                </h3>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($yearPeriods as $period)
                        @php
                            $isActive = $activePeriod?->id === $period->id;
                            $isClosed = $period->is_closed;
                            $monthName = $period->start_date->locale('es')->isoFormat('MMMM');
                        @endphp

                        <button
                            wire:click="{{ $isClosed ? '' : 'selectPeriod(' . $period->id . ')' }}"
                            @disabled($isClosed)
                            type="button"
                            @class([
                                'group relative rounded-xl border p-4 text-left transition duration-150 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
                                'border-primary-400 bg-primary-50 ring-2 ring-primary-300 dark:border-primary-600 dark:bg-primary-950 dark:ring-primary-700' => $isActive,
                                'cursor-not-allowed border-gray-200 bg-gray-50 opacity-60 dark:border-gray-700 dark:bg-gray-900' => $isClosed && ! $isActive,
                                'cursor-pointer border-gray-200 bg-white hover:border-primary-300 hover:bg-primary-50/40 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-primary-700 dark:hover:bg-primary-950/50' => ! $isClosed && ! $isActive,
                            ])
                        >
                            @if ($isActive)
                                <span
                                    class="absolute top-2 right-2 h-2 w-2 rounded-full bg-primary-500 ring-2 ring-white dark:ring-primary-950"
                                ></span>
                            @endif

                            <span
                                @class([
                                    'block text-sm font-semibold capitalize',
                                    'text-primary-800 dark:text-primary-200' => $isActive,
                                    'text-gray-400 dark:text-gray-600' => $isClosed && ! $isActive,
                                    'text-gray-800 group-hover:text-primary-800 dark:text-gray-200 dark:group-hover:text-primary-300' => ! $isClosed && ! $isActive,
                                ])
                            >
                                {{ $monthName }}
                            </span>

                            <span
                                @class([
                                    'mt-0.5 block text-xs',
                                    'text-primary-600 dark:text-primary-400' => $isActive,
                                    'text-gray-400 dark:text-gray-600' => ! $isActive,
                                ])
                            >
                                {{ $period->start_date->format('d') }}–{{ $period->end_date->format('d M') }}
                            </span>

                            <span
                                @class([
                                    'mt-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium',
                                    'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200' => $isActive,
                                    'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500' => $isClosed && ! $isActive,
                                    'bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' => ! $isClosed && ! $isActive,
                                ])
                            >
                                @if ($isActive)
                                    <x-heroicon-s-check-circle class="h-3 w-3" />
                                    Activo
                                @elseif ($isClosed)
                                    <x-heroicon-s-lock-closed class="h-3 w-3" />
                                    Cerrado
                                @else
                                    <x-heroicon-s-cursor-arrow-rays class="h-3 w-3" />
                                    Elegir
                                @endif
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-gray-50 py-14 text-center dark:border-gray-700 dark:bg-gray-900">
                <x-heroicon-o-calendar class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-gray-600" />
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay períodos fiscales creados.</p>
                @can('create', \App\Models\FiscalPeriod::class)
                    <a
                        href="{{ route('filament.admin.resources.fiscal-periods.create') }}"
                        class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400"
                    >
                        <x-heroicon-o-plus class="h-4 w-4" />
                        Crear período
                    </a>
                @endcan
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
