<x-filament-panels::page>
    @php $company = \App\Models\CompanySetting::current(); @endphp

    <div class="bg-white rounded-xl shadow p-6 dark:bg-gray-900">
        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        @if ($this->fiscal_period_id)
            @php
                $period = \App\Models\FiscalPeriod::find($this->fiscal_period_id);
                $r = $this->getResultados();
            @endphp

            <div class="text-center space-y-1 border-b pb-4 mt-4">
                <p class="font-bold uppercase">Estado de Resultados</p>
                <p class="font-semibold">{{ $company->name }}</p>
                <p>Del {{ $period->start_date->format('d/m/Y') }} al {{ $period->end_date->format('d/m/Y') }}</p>
                <p class="text-sm text-gray-500">Expresado en Dólares Americanos</p>
            </div>

            <div class="mt-4 space-y-1 text-sm max-w-lg mx-auto">
                <div class="flex justify-between"><span>Ingresos
                        (Ventas)</span><span>${{ number_format($r['ingresos'], 2) }}</span></div>
                <div class="flex justify-between text-red-500"><span>(-) Costo de
                        venta</span><span>${{ number_format($r['costos'], 2) }}</span></div>
                <div class="flex justify-between font-bold border-t pt-1"><span>(=) Utilidad
                        Bruta</span><span>${{ number_format($r['utilidad_bruta'], 2) }}</span></div>

                <div class="flex justify-between text-red-500 mt-2"><span>(-) Gastos de
                        Administración</span><span>${{ number_format($r['gastos_admin'], 2) }}</span></div>
                <div class="flex justify-between text-red-500"><span>(-) Gastos de
                        Venta</span><span>${{ number_format($r['gastos_venta'], 2) }}</span></div>
                <div class="flex justify-between font-bold border-t pt-1"><span>(=) Utilidad
                        Operativa</span><span>${{ number_format($r['utilidad_operativa'], 2) }}</span></div>

                <div class="flex justify-between text-red-500 mt-2"><span>(-) Gastos
                        Financieros</span><span>${{ number_format($r['gastos_financieros'], 2) }}</span></div>
                <div class="flex justify-between text-red-500"><span>(-) Gastos no
                        operativos</span><span>${{ number_format($r['gastos_no_operativos'], 2) }}</span></div>
                <div class="flex justify-between font-bold border-t pt-1"><span>(=) Utilidad antes de
                        ISR</span><span>${{ number_format($r['utilidad_antes_isr'], 2) }}</span></div>

                <div class="flex justify-between text-red-500 mt-2"><span>(-) ISR
                        (25%)</span><span>${{ number_format($r['isr'], 2) }}</span></div>
                <div
                    class="flex justify-between font-bold text-lg border-t-2 pt-2 mt-2 {{ $r['utilidad_neta'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    <span>(=) Utilidad / Pérdida del Ejercicio</span>
                    <span>${{ number_format($r['utilidad_neta'], 2) }}</span>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
