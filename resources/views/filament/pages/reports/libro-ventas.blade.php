{{-- resources/views/filament/pages/reports/libro-ventas.blade.php --}}
<x-filament-panels::page>

    {{-- Encabezado de la empresa --}}
    @php $company = \App\Models\CompanySetting::current(); @endphp

    <div class="bg-white rounded-xl shadow p-6 space-y-4 dark:bg-gray-900">

        {{-- Filtro de periodo --}}
        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        @if ($this->fiscal_period_id)
            @php
                $period = \App\Models\FiscalPeriod::find($this->fiscal_period_id);
                $documents = $this->getDocuments();
                $totals = $this->getTotals();
            @endphp

            {{-- Encabezado del reporte --}}
            <div class="text-center space-y-1 border-b pb-4">
                <p class="font-bold uppercase">Libro de Ventas a Consumidor Final</p>
                <p class="font-semibold">{{ $company->name }}</p>
                <p>N.R.C.: {{ $company->nrc }} &nbsp;|&nbsp; N.I.T.: {{ $company->nit }}</p>
                <p>Periodo Tributario: {{ $period->name }}</p>
            </div>

            {{-- Tabla de documentos --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="border p-2 text-left">Fecha</th>
                            <th class="border p-2 text-left">Doc. Del</th>
                            <th class="border p-2 text-left">Doc. Al</th>
                            <th class="border p-2 text-right">V. Exentas</th>
                            <th class="border p-2 text-right">V. No Grav.</th>
                            <th class="border p-2 text-right">V. Gravadas</th>
                            <th class="border p-2 text-right">IVA (13%)</th>
                            <th class="border p-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $doc)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="border p-2">{{ \Carbon\Carbon::parse($doc->issue_date)->format('d-M-y') }}
                                </td>
                                <td class="border p-2">{{ $doc->document_number }}</td>
                                <td class="border p-2">{{ $doc->document_number }}</td>
                                <td class="border p-2 text-right">${{ number_format($doc->exempt_amount, 2) }}</td>
                                <td class="border p-2 text-right">${{ number_format($doc->non_taxable_amount, 2) }}</td>
                                <td class="border p-2 text-right">${{ number_format($doc->taxable_amount, 2) }}</td>
                                <td class="border p-2 text-right">${{ number_format($doc->iva_amount, 2) }}</td>
                                <td class="border p-2 text-right">${{ number_format($doc->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="font-bold bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <td class="border p-2" colspan="3">TOTAL</td>
                            <td class="border p-2 text-right">${{ number_format($totals['ventas_exentas'], 2) }}</td>
                            <td class="border p-2 text-right">${{ number_format($totals['ventas_no_grav'], 2) }}</td>
                            <td class="border p-2 text-right">${{ number_format($totals['ventas_gravadas'], 2) }}</td>
                            <td class="border p-2 text-right">${{ number_format($totals['debito_fiscal'], 2) }}</td>
                            <td class="border p-2 text-right">${{ number_format($totals['total_ventas'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Resumen del periodo --}}
            <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                <div class="space-y-1">
                    <p class="font-bold uppercase border-b pb-1">Resumen del Periodo</p>
                    <div class="flex justify-between"><span>Ventas Netas
                            Gravadas</span><span>${{ number_format($totals['ventas_gravadas'], 2) }}</span></div>
                    <div class="flex justify-between"><span>13% IVA Débito
                            Fiscal</span><span>${{ number_format($totals['debito_fiscal'], 2) }}</span></div>
                    <div class="flex justify-between font-bold border-t pt-1"><span>Total Ventas del
                            Mes</span><span>${{ number_format($totals['total_ventas'], 2) }}</span></div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
