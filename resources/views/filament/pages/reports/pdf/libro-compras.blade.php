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

        h2 {
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        th {
            background: #e5e7eb;
            padding: 5px 4px;
            border: 1px solid #bbb;
            text-align: left;
            font-size: 9px;
        }

        td {
            padding: 4px;
            border: 1px solid #ddd;
            font-size: 9px;
        }

        tfoot td {
            background: #e5e7eb;
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .header-info {
            text-align: center;
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .grid {
            display: table;
            width: 100%;
            margin-top: 16px;
        }

        .col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }

        .summary-row {
            padding: 3px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-title {
            font-weight: bold;
            border-bottom: 1px solid #333;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .total-row {
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 4px;
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
        <h2>LIBRO DE COMPRAS</h2>
        <h3>{{ $company->name }}</h3>
        <p>N.R.C.: {{ $company->nrc ?? '—' }} &nbsp;|&nbsp; N.I.T.: {{ $company->nit ?? '—' }}</p>
        <p>Periodo Tributario: {{ $period->name }}</p>
        <p>Del {{ $period->start_date->format('d/m/Y') }} al {{ $period->end_date->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Documento</th>
                <th>Proveedor</th>
                <th class="right">Exentas</th>
                <th class="right">No Grav.</th>
                <th class="right">Gravadas</th>
                <th class="right">Crédito Fiscal</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $purchase)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
                    <td>{{ $purchase->document_number ?: ($purchase->taxDocument?->document_number ?? 'S/D') }}</td>
                    <td>{{ $purchase->supplier?->name }}</td>
                    <td class="right">${{ number_format($purchase->exempt_amount, 2) }}</td>
                    <td class="right">${{ number_format($purchase->non_taxable_amount, 2) }}</td>
                    <td class="right">${{ number_format($purchase->taxable_amount, 2) }}</td>
                    <td class="right">${{ number_format($purchase->credit_fiscal, 2) }}</td>
                    <td class="right">${{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:#999;">Sin compras en este periodo</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">TOTAL</td>
                <td class="right">${{ number_format($totals['exentas'], 2) }}</td>
                <td class="right">${{ number_format($totals['no_gravadas'], 2) }}</td>
                <td class="right">${{ number_format($totals['gravadas'], 2) }}</td>
                <td class="right">${{ number_format($totals['credito_fiscal'], 2) }}</td>
                <td class="right">${{ number_format($totals['total'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="grid">
        <div class="col">
            <p class="summary-title">RESUMEN DEL PERIODO</p>
            <div class="summary-row" style="display:flex; justify-content:space-between;">
                <span>Compras Netas Gravadas</span>
                <span>${{ number_format($totals['gravadas'], 2) }}</span>
            </div>
            <div class="summary-row" style="display:flex; justify-content:space-between;">
                <span>Crédito Fiscal (13%)</span>
                <span>${{ number_format($totals['credito_fiscal'], 2) }}</span>
            </div>
            <div class="total-row" style="display:flex; justify-content:space-between;">
                <span>Total Compras del Mes</span>
                <span>${{ number_format($totals['total'], 2) }}</span>
            </div>
        </div>

        <div class="col">
            <p class="summary-title">LIQUIDACIÓN IVA</p>
            <div class="summary-row" style="display:flex; justify-content:space-between;">
                <span>Débito Fiscal (ventas)</span>
                <span>${{ number_format($debitoFiscal, 2) }}</span>
            </div>
            <div class="summary-row" style="display:flex; justify-content:space-between;">
                <span>Crédito Fiscal (compras)</span>
                <span>- ${{ number_format($creditoFiscal, 2) }}</span>
            </div>
            <div class="total-row {{ $ivaPagar >= 0 ? 'text-red' : 'text-green' }}"
                style="display:flex; justify-content:space-between;">
                <span>{{ $ivaPagar >= 0 ? 'IVA por Pagar' : 'Remanente IVA' }}</span>
                <span>${{ number_format(abs($ivaPagar), 2) }}</span>
            </div>
        </div>
    </div>
</body>

</html>
