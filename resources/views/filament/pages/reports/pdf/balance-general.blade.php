<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 20px;
        }

        h2,
        h3,
        p {
            text-align: center;
            margin: 3px 0;
        }

        .header-info {
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .grid {
            width: 100%;
            display: table;
        }

        .col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 8px;
        }

        .section-title {
            background: #e5e7eb;
            font-weight: bold;
            padding: 4px 6px;
            margin: 8px 0 4px;
            font-size: 9px;
            text-transform: uppercase;
        }

        .account-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 6px;
            border-bottom: 1px solid #f0f0f0;
        }

        .subtotal-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 6px;
            font-weight: bold;
            border-top: 1px solid #333;
            margin-top: 2px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 6px;
            font-weight: bold;
            font-size: 11px;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            margin-top: 8px;
        }

        .alert {
            padding: 6px;
            border-radius: 4px;
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
        }

        .alert-red {
            background: #fee2e2;
            color: #dc2626;
        }

        .alert-green {
            background: #dcfce7;
            color: #16a34a;
        }
    </style>
</head>

<body>
    <div class="header-info">
        <h2>BALANCE GENERAL</h2>
        <h3>{{ $company->name }}</h3>
        <p>Al {{ $period->end_date->format('d/m/Y') }}</p>
        <p style="color:#666; font-size:9px;">Expresado en Dólares Americanos (USD)</p>
    </div>

    <div class="grid">
        {{-- ACTIVOS --}}
        <div class="col">
            <div class="section-title">Activos Corrientes</div>
            @foreach ($data['activos_corrientes'] as $account)
                <div class="account-row">
                    <span>{{ $account['name'] }}</span>
                    <span>${{ number_format($account['balance'], 2) }}</span>
                </div>
            @endforeach
            <div class="subtotal-row">
                <span>Total Activos Corrientes</span>
                <span>${{ number_format($data['activos_corrientes']->sum('balance'), 2) }}</span>
            </div>

            <div class="section-title">Activos No Corrientes</div>
            @foreach ($data['activos_no_corrientes'] as $account)
                <div class="account-row">
                    <span>{{ $account['name'] }}</span>
                    <span>${{ number_format($account['balance'], 2) }}</span>
                </div>
            @endforeach
            <div class="subtotal-row">
                <span>Total Activos No Corrientes</span>
                <span>${{ number_format($data['activos_no_corrientes']->sum('balance'), 2) }}</span>
            </div>

            <div class="total-row">
                <span>TOTAL ACTIVOS</span>
                <span>${{ number_format($data['total_activos'], 2) }}</span>
            </div>
        </div>

        {{-- PASIVOS + PATRIMONIO --}}
        <div class="col">
            <div class="section-title">Pasivos Corrientes</div>
            @foreach ($data['pasivos_corrientes'] as $account)
                <div class="account-row">
                    <span>{{ $account['name'] }}</span>
                    <span>${{ number_format($account['balance'], 2) }}</span>
                </div>
            @endforeach
            <div class="subtotal-row">
                <span>Total Pasivos Corrientes</span>
                <span>${{ number_format($data['pasivos_corrientes']->sum('balance'), 2) }}</span>
            </div>

            <div class="section-title">Pasivos No Corrientes</div>
            @foreach ($data['pasivos_no_corrientes'] as $account)
                <div class="account-row">
                    <span>{{ $account['name'] }}</span>
                    <span>${{ number_format($account['balance'], 2) }}</span>
                </div>
            @endforeach
            <div class="subtotal-row">
                <span>Total Pasivos No Corrientes</span>
                <span>${{ number_format($data['pasivos_no_corrientes']->sum('balance'), 2) }}</span>
            </div>

            <div class="subtotal-row" style="margin-top:4px;">
                <span>TOTAL PASIVOS</span>
                <span>${{ number_format($data['total_pasivos'], 2) }}</span>
            </div>

            <div class="section-title">Patrimonio</div>
            @foreach ($data['patrimonio'] as $account)
                <div class="account-row">
                    <span>{{ $account['name'] }}</span>
                    <span>${{ number_format($account['balance'], 2) }}</span>
                </div>
            @endforeach
            <div class="subtotal-row">
                <span>Total Patrimonio</span>
                <span>${{ number_format($data['total_patrimonio'], 2) }}</span>
            </div>

            <div class="total-row">
                <span>PASIVOS + PATRIMONIO</span>
                <span>${{ number_format($data['total_pasivos'] + $data['total_patrimonio'], 2) }}</span>
            </div>

            @php $diff = $data['total_activos'] - ($data['total_pasivos'] + $data['total_patrimonio']); @endphp
            @if (abs($diff) > 0.01)
                <div class="alert alert-red">⚠ Balance no cuadra — Diferencia: ${{ number_format($diff, 2) }}</div>
            @else
                <div class="alert alert-green">✓ Balance cuadrado correctamente</div>
            @endif
        </div>
    </div>
</body>

</html>
