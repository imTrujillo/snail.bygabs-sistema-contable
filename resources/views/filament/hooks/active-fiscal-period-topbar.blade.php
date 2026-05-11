@php
    use App\Filament\Pages\SelectFiscalPeriod;
    use App\Models\FiscalPeriod;

    $href = SelectFiscalPeriod::getUrl(panel: 'admin');
    $periodId = session('active_fiscal_period_id');
    $period = $periodId ? FiscalPeriod::find($periodId) : null;
@endphp

<a
    href="{{ $href }}"
    title="Cambiar período fiscal"
    class=" group flex max-w-[14rem] shrink-0 items-center gap-1.5 rounded-md border border-primary-200/80 bg-white px-2 py-0.5 text-left shadow-sm transition hover:border-primary-300 hover:bg-primary-50/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400"
>
    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-primary-100 text-primary-700">
        <x-heroicon-m-calendar-days class="h-3.5 w-3.5" />
    </span>
    <span class="min-w-0 flex-1 leading-tight">
        @if ($period && ! $period->is_closed)
            <span class="block truncate text-xs font-medium text-primary-700">
                {{ $period->start_date->locale('es')->isoFormat('MMMM YYYY') }}
            </span>
            <span class="block truncate text-[10px] uppercase tracking-wide text-gray-500">
                Período activo · Cambiar
            </span>
        @else
            <span class="block truncate text-xs font-medium text-gray-700">
                Período fiscal
            </span>
            <span class="block truncate text-[10px] uppercase tracking-wide text-gray-500">
                Elegir período
            </span>
        @endif
    </span>
    </a>
