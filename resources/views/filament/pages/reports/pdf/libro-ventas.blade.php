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

        h3 {
            font-size: 11px;
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

        .summary {
            margin-top: 16px;
            width: 280px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-row.total {
            font-weight: bold;
            border-top: 2px solid #333;
            border-bottom: none;
        }

        .header-info {
            text-align: center;
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="header-info">
        <h2>LIBRO DE VENTAS A CONSUMIDOR FINAL</h2>
        <h3>{{ $company->name }}</h3>
        <p>N.R.C.: {{ $company->nrc ?? '—' }} &nbsp;|&nbsp; N.I.T.: {{ $company->nit ?? '—' }}</p>
        <p>Periodo Tributario: {{ $period->name }}</p>
        <p>Del {{ $period->start_date->format('d/m/Y') }} al {{ $period->end_date->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Doc. Del</th>
                <th>Doc. Al</th>
                <th class="right">V. Exentas</th>
                <th class="right">V. No Grav.</th>
                <th class="right">V. Gravadas</th>
                <th class="right">IVA (13%)</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $doc)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($doc->issue_date)->format('d/m/Y') }}</td>
                    <td>{{ $doc->document_number }}</td>
                    <td>{{ $doc->document_number }}</td>
                    <td class="right">${{ number_format($doc->exempt_amount, 2) }}</td>
                    <td class="right">${{ number_format($doc->non_taxable_amount, 2) }}</td>
                    <td class="right">${{ number_format($doc->taxable_amount, 2) }}</td>
                    <td class="right">${{ number_format($doc->iva_amount, 2) }}</td>
                    <td class="right">${{ number_format($doc->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:#999;">Sin documentos en este periodo</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">TOTAL</td>
                <td class="right">${{ number_format($totals['ventas_exentas'], 2) }}</td>
                <td class="right">${{ number_format($totals['ventas_no_grav'], 2) }}</td>
                <td class="right">${{ number_format($totals['ventas_gravadas'], 2) }}</td>
                <td class="right">${{ number_format($totals['debito_fiscal'], 2) }}</td>
                <td class="right">${{ number_format($totals['total_ventas'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="summary" style="margin-top:20px;">
        <p style="font-weight:bold; border-bottom:1px solid #333; padding-bottom:4px;">RESUMEN DEL PERIODO</p>
        <div class="summary-row">
            <span>Ventas Netas Gravadas</span>
            <span>${{ number_format($totals['ventas_gravadas'], 2) }}</span>
        </div>
        <div class="summary-row">
            <span>13% IVA Débito Fiscal</span>
            <span>${{ number_format($totals['debito_fiscal'], 2) }}</span>
        </div>
        <div class="summary-row total">
            <span>Total Ventas del Mes</span>
            <span>${{ number_format($totals['total_ventas'], 2) }}</span>
        </div>
    </div>
</body>

</html>
