<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 30px;
        }

        h2,
        h3,
        p {
            text-align: center;
            margin: 3px 0;
        }

        .header-info {
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .report-body {
            max-width: 400px;
            margin: 0 auto;
        }

        .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .row.section-title {
            font-weight: bold;
            background: #f3f4f6;
            padding: 4px 6px;
            margin-top: 8px;
            border-bottom: 1px solid #ccc;
        }

        .row.subtotal {
            font-weight: bold;
            border-top: 1px solid #333;
            border-bottom: 2px solid #333;
        }

        .row.total {
            font-weight: bold;
            font-size: 13px;
            border-top: 2px solid #333;
            border-bottom: none;
            margin-top: 6px;
        }

        .text-red {
            color: #dc2626;
        }

        .text-green {
            color: #16a34a;
        }
    </style>
</head>

<body>
    <div class="header-info">
        <h2>ESTADO DE RESULTADOS</h2>
        <h3>{{ $company->name }}</h3>
        <p>Del {{ $period->start_date->format('d/m/Y') }} al {{ $period->end_date->format('d/m/Y') }}</p>
        <p style="color:#666; font-size:10px;">Expresado en Dólares Americanos (USD)</p>
    </div>

    <div class="report-body">
        <div class="row section-title"><span>INGRESOS</span></div>
        <div class="row">
            <span>Ingresos por Ventas</span>
            <span>${{ number_format($r['ingresos'], 2) }}</span>
        </div>

        <div class="row section-title"><span>COSTOS</span></div>
        <div class="row text-red">
            <span>(-) Costo de Venta</span>
            <span>${{ number_format($r['costos'], 2) }}</span>
        </div>

        <div class="row subtotal">
            <span>(=) Utilidad Bruta</span>
            <span>${{ number_format($r['utilidad_bruta'], 2) }}</span>
        </div>

        <div class="row section-title"><span>GASTOS OPERATIVOS</span></div>
        <div class="row text-red">
            <span>(-) Gastos de Administración</span>
            <span>${{ number_format($r['gastos_admin'], 2) }}</span>
        </div>
        <div class="row text-red">
            <span>(-) Gastos de Venta</span>
            <span>${{ number_format($r['gastos_venta'], 2) }}</span>
        </div>

        <div class="row subtotal">
            <span>(=) Utilidad Operativa</span>
            <span>${{ number_format($r['utilidad_operativa'], 2) }}</span>
        </div>

        <div class="row section-title"><span>OTROS GASTOS</span></div>
        <div class="row text-red">
            <span>(-) Gastos Financieros</span>
            <span>${{ number_format($r['gastos_financieros'], 2) }}</span>
        </div>
        <div class="row text-red">
            <span>(-) Gastos no operativos</span>
            <span>${{ number_format($r['gastos_no_operativos'], 2) }}</span>
        </div>

        <div class="row subtotal">
            <span>(=) Utilidad antes de ISR</span>
            <span>${{ number_format($r['utilidad_antes_isr'], 2) }}</span>
        </div>

        <div class="row text-red">
            <span>(-) ISR</span>
            <span>${{ number_format($r['isr'], 2) }}</span>
        </div>

        <div class="row total {{ $r['utilidad_neta'] >= 0 ? 'text-green' : 'text-red' }}">
            <span>(=) {{ $r['utilidad_neta'] >= 0 ? 'Utilidad' : 'Pérdida' }} del Ejercicio</span>
            <span>${{ number_format(abs($r['utilidad_neta']), 2) }}</span>
        </div>
    </div>
</body>

</html>
