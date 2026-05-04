<x-filament-panels::page>
    @php $company = \App\Models\CompanySetting::current(); @endphp

    <div class="bg-white rounded-xl shadow p-6 space-y-4 dark:bg-gray-900">

        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        @if ($this->fiscal_period_id)
            @php
                $period = \App\Models\FiscalPeriod::find($this->fiscal_period_id);
                $purchases = $this->getPurchases();
                $totals = $this->getTotals();
            @endphp

            {{-- Encabezado --}}
            <div class="text-center space-y-1 border-b pb-4">
                <p class="font-bold uppercase">Libro de Compras</p>
                <p class="font-semibold">{{ $company->name }}</p>
                <p>N.R.C.: {{ $company->nrc }} &nbsp;|&nbsp; N.I.T.: {{ $company->nit }}</p>
                <p>Periodo Tributario: {{ $period->name }}</p>
            </div>

            {{-- Tabla --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="border p-2 text-left">Fecha</th>
                            <th class="border p-2 text-left">Documento</th>
                            <th class="border p-2 text-left">Proveedor</th>
                            <th class="border p-2 text-right">Compras Exentas</th>
                            <th class="border p-2 text-right">Compras No Grav.</th>
                            <th class="border p-2 text-right">Compras Gravadas</th>
                            <th class="border p-2 text-right">Crédito Fiscal</th>
                            <th class="border p-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="border p-2">
                                    {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d-M-y') }}
                                </td>
                                <td class="border p-2">
                                    {{ $purchase->document_number ?: ($purchase->taxDocument?->document_number ?? 'S/D') }}
                                </td>
                                <td class="border p-2">{{ $purchase->supplier?->name }}</td>
                                <td class="border p-2 text-right">
                                    ${{ number_format($purchase->exempt_amount, 2) }}
                                </td>
                                <td class="border p-2 text-right">
                                    ${{ number_format($purchase->non_taxable_amount, 2) }}
                                </td>
                                <td class="border p-2 text-right">
                                    ${{ number_format($purchase->taxable_amount, 2) }}
                                </td>
                                <td class="border p-2 text-right">
                                    ${{ number_format($purchase->credit_fiscal, 2) }}
                                </td>
                                <td class="border p-2 text-right">
                                    ${{ number_format($purchase->total_amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="border p-4 text-center text-gray-400">
                                    No hay compras en este periodo
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="font-bold bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <td class="border p-2" colspan="3">TOTAL</td>
                            <td class="border p-2 text-right">
                                ${{ number_format($totals['exentas'], 2) }}
                            </td>
                            <td class="border p-2 text-right">
                                ${{ number_format($totals['no_gravadas'], 2) }}
                            </td>
                            <td class="border p-2 text-right">
                                ${{ number_format($totals['gravadas'], 2) }}
                            </td>
                            <td class="border p-2 text-right">
                                ${{ number_format($totals['credito_fiscal'], 2) }}
                            </td>
                            <td class="border p-2 text-right">
                                ${{ number_format($totals['total'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Resumen --}}
            <div class="grid grid-cols-2 gap-6 mt-4 text-sm">
                <div class="space-y-1">
                    <p class="font-bold uppercase border-b pb-1">Resumen del Periodo</p>
                    <div class="flex justify-between">
                        <span>Compras Netas Gravadas</span>
                        <span>${{ number_format($totals['gravadas'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Crédito Fiscal (13%)</span>
                        <span>${{ number_format($totals['credito_fiscal'], 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold border-t pt-1">
                        <span>Total Compras del Mes</span>
                        <span>${{ number_format($totals['total'], 2) }}</span>
                    </div>
                </div>

                {{-- IVA a pagar --}}
                @php
                    $debitoFiscal = $this->getDebitoFiscalPeriodo();
                    $creditoFiscal = $totals['credito_fiscal'];
                    $ivaPagar = $debitoFiscal - $creditoFiscal;
                @endphp
                <div class="space-y-1">
                    <p class="font-bold uppercase border-b pb-1">Liquidación IVA</p>
                    <div class="flex justify-between">
                        <span>Débito Fiscal (ventas)</span>
                        <span>${{ number_format($debitoFiscal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Crédito Fiscal (compras)</span>
                        <span>- ${{ number_format($creditoFiscal, 2) }}</span>
                    </div>
                    <div
                        class="flex justify-between font-bold border-t pt-1
                    {{ $ivaPagar >= 0 ? 'text-red-600' : 'text-green-600' }}">
                        <span>{{ $ivaPagar >= 0 ? 'IVA por Pagar' : 'Remanente IVA' }}</span>
                        <span>${{ number_format(abs($ivaPagar), 2) }}</span>
                    </div>
                </div>
            </div>

        @endif
    </div>
</x-filament-panels::page>
