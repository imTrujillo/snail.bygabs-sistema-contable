<x-filament-panels::page>
    @php $company = \App\Models\CompanySetting::current(); @endphp

    <div class="bg-white rounded-xl shadow p-6 space-y-4 dark:bg-gray-900">

        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        @if ($this->fiscal_period_id)
            @php
                $period = \App\Models\FiscalPeriod::find($this->fiscal_period_id);
                $data = $this->getBalanceData();
            @endphp

            {{-- Encabezado --}}
            <div class="text-center space-y-1 border-b pb-4 mt-4">
                <p class="font-bold uppercase">Balance General</p>
                <p class="font-semibold">{{ $company->name }}</p>
                <p>Al {{ $period->end_date->format('d/m/Y') }}</p>
                <p class="text-sm text-gray-500">Expresado en Dólares Americanos</p>
            </div>

            {{-- Cuerpo: Activos | Pasivos+Patrimonio --}}
            <div class="grid grid-cols-2 gap-6 mt-4 text-sm">

                {{-- COLUMNA IZQUIERDA: ACTIVOS --}}
                <div class="space-y-3">

                    {{-- Activos Corrientes --}}
                    <p class="font-bold uppercase bg-gray-100 dark:bg-gray-700 p-2 rounded">
                        Activos Corrientes
                    </p>
                    @foreach ($data['activos_corrientes'] as $account)
                        <div class="flex justify-between px-2">
                            <span>{{ $account['name'] }}</span>
                            <span>${{ number_format($account['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between px-2 font-semibold border-t pt-1">
                        <span>Total Activos Corrientes</span>
                        <span>${{ number_format($data['activos_corrientes']->sum('balance'), 2) }}</span>
                    </div>

                    {{-- Activos No Corrientes --}}
                    <p class="font-bold uppercase bg-gray-100 dark:bg-gray-700 p-2 rounded mt-3">
                        Activos No Corrientes
                    </p>
                    @foreach ($data['activos_no_corrientes'] as $account)
                        <div class="flex justify-between px-2">
                            <span>{{ $account['name'] }}</span>
                            <span>${{ number_format($account['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between px-2 font-semibold border-t pt-1">
                        <span>Total Activos No Corrientes</span>
                        <span>${{ number_format($data['activos_no_corrientes']->sum('balance'), 2) }}</span>
                    </div>

                    {{-- Total Activos --}}
                    <div class="flex justify-between px-2 font-bold text-base border-t-2 pt-2 mt-2">
                        <span>TOTAL ACTIVOS</span>
                        <span>${{ number_format($data['total_activos'], 2) }}</span>
                    </div>
                </div>

                {{-- COLUMNA DERECHA: PASIVOS + PATRIMONIO --}}
                <div class="space-y-3">

                    {{-- Pasivos Corrientes --}}
                    <p class="font-bold uppercase bg-gray-100 dark:bg-gray-700 p-2 rounded">
                        Pasivos Corrientes
                    </p>
                    @foreach ($data['pasivos_corrientes'] as $account)
                        <div class="flex justify-between px-2">
                            <span>{{ $account['name'] }}</span>
                            <span>${{ number_format($account['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between px-2 font-semibold border-t pt-1">
                        <span>Total Pasivos Corrientes</span>
                        <span>${{ number_format($data['pasivos_corrientes']->sum('balance'), 2) }}</span>
                    </div>

                    {{-- Pasivos No Corrientes --}}
                    <p class="font-bold uppercase bg-gray-100 dark:bg-gray-700 p-2 rounded mt-3">
                        Pasivos No Corrientes
                    </p>
                    @foreach ($data['pasivos_no_corrientes'] as $account)
                        <div class="flex justify-between px-2">
                            <span>{{ $account['name'] }}</span>
                            <span>${{ number_format($account['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between px-2 font-semibold border-t pt-1">
                        <span>Total Pasivos No Corrientes</span>
                        <span>${{ number_format($data['pasivos_no_corrientes']->sum('balance'), 2) }}</span>
                    </div>

                    {{-- Total Pasivos --}}
                    <div class="flex justify-between px-2 font-semibold border-t pt-1">
                        <span>TOTAL PASIVOS</span>
                        <span>${{ number_format($data['total_pasivos'], 2) }}</span>
                    </div>

                    {{-- Patrimonio --}}
                    <p class="font-bold uppercase bg-gray-100 dark:bg-gray-700 p-2 rounded mt-3">
                        Patrimonio
                    </p>
                    @foreach ($data['patrimonio'] as $account)
                        <div class="flex justify-between px-2">
                            <span>{{ $account['name'] }}</span>
                            <span>${{ number_format($account['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between px-2 font-semibold border-t pt-1">
                        <span>Total Patrimonio</span>
                        <span>${{ number_format($data['total_patrimonio'], 2) }}</span>
                    </div>

                    {{-- Pasivos + Patrimonio --}}
                    <div class="flex justify-between px-2 font-bold text-base border-t-2 pt-2 mt-2">
                        <span>PASIVOS + PATRIMONIO</span>
                        <span>${{ number_format($data['total_pasivos'] + $data['total_patrimonio'], 2) }}</span>
                    </div>

                    {{-- Verificación cuadre --}}
                    @php $diferencia = $data['total_activos'] - ($data['total_pasivos'] + $data['total_patrimonio']); @endphp
                    @if (abs($diferencia) > 0.01)
                        <div class="bg-red-100 text-red-700 rounded p-2 text-xs mt-2">
                            ⚠️ El balance no cuadra. Diferencia: ${{ number_format($diferencia, 2) }}
                        </div>
                    @else
                        <div class="bg-green-100 text-green-700 rounded p-2 text-xs mt-2">
                            ✅ Balance cuadrado correctamente
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
