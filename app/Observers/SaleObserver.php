<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\Sale;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class SaleObserver
{
    public function created(Sale $sale): void
    {
        $period = FiscalPeriod::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$period) return;

        $isCCF = $sale->document_type === 'CCF';

        // FCF: IVA embebido pero igual se declara
        // CCF: IVA explícito y desglosado
        $ivaAmount  = round($sale->total / 1.13 * 0.13, 2);
        $baseAmount = round($sale->total - $ivaAmount, 2);

        // DÉBITO → caja o banco
        $debitAccount = match ($sale->payment_method) {
            'Efectivo'      => Account::where('code', '1102')->first(),
            'Transferencia' => Account::where('code', '1101')->first(),
            'Tarjeta'       => Account::where('code', '1101')->first(),
            default         => null,
        };

        if (!$debitAccount) return;

        $ventasAccount = Account::where('code', '4100')->first();
        if (!$ventasAccount) return;

        $ivaAccount = Account::where('code', '2104-01')->first();
        if (!$ivaAccount) return;

        $docNumber = $sale->taxDocument?->document_number ?? 'S/N';

        $entry = JournalEntry::create([
            'entry_date'       => now(),
            'description'      => "Venta {$sale->document_type} - {$docNumber}",
            'reference_type'   => 'sale',
            'reference_id'     => $sale->id,
            'fiscal_period_id' => $period->id,
            'user_id'          => Auth::check() ? Auth::id() : null,
        ]);

        // DÉBITO → cobro total (igual en FCF y CCF)
        $entry->lines()->create([
            'account_id'  => $debitAccount->id,
            'debit'       => $sale->total,
            'credit'      => 0,
            'description' => "Cobro {$sale->document_type} - {$docNumber}",
        ]);

        // CRÉDITO → ingresos por ventas (base sin IVA)
        $entry->lines()->create([
            'account_id'  => $ventasAccount->id,
            'debit'       => 0,
            'credit'      => $baseAmount,
            'description' => $isCCF
                ? "Ingreso por servicio (CCF - base gravada)"
                : "Ingreso por servicio (FCF)",
        ]);

        // CRÉDITO → IVA por pagar
        // FCF: IVA embebido que igual se debe declarar al fisco
        // CCF: IVA débito fiscal explícito
        $entry->lines()->create([
            'account_id'  => $ivaAccount->id,
            'debit'       => 0,
            'credit'      => $ivaAmount,
            'description' => $isCCF
                ? "IVA débito fiscal (CCF)"
                : "IVA débito fiscal (FCF)",
        ]);

        $recipient = Auth::user();
        if ($recipient) {
            Notification::make()
                ->title('Venta registrada')
                ->body("Documento **{$docNumber}** ({$sale->document_type}) por \${$sale->total} — Pago: *{$sale->payment_method}*.")
                ->success()
                ->sendToDatabase($recipient);
        }
    }
}
