<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryType;
use App\Models\Sale;
use App\Models\TaxDocument;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class SaleObserver
{
    public function created(Sale $sale): void
    {
        try {
            $period = FiscalPeriod::find(session('active_fiscal_period_id'));
            if (!$period) return;

            $isCCF      = $sale->document_type === 'CCF';
            $ivaAmount  = round($sale->total / 1.13 * 0.13, 2);
            $baseAmount = round($sale->total - $ivaAmount, 2);

            // Crear TaxDocument automáticamente
            $lastDoc = TaxDocument::where('type', $sale->document_type)
                ->orderByDesc('correlative_number')
                ->first();

            $nextCorrelative = ($lastDoc?->correlative_number ?? 0) + 1;
            $series          = 'A';
            $docNumber       = $series . '-' . str_pad($nextCorrelative, 6, '0', STR_PAD_LEFT);

            $taxDoc = TaxDocument::create([
                'type'               => $sale->document_type,
                'series'             => $series,
                'correlative_number' => $nextCorrelative,
                'document_number'    => $docNumber,
                'issue_date'         => now(),
                'customer_id'        => $sale->customer_id,
                'reference_id'       => $sale->id,
                'reference_type'     => 'sale',
                'exempt_amount'      => 0,
                'non_taxable_amount' => 0,
                'taxable_amount'     => $baseAmount,
                'iva_amount'         => $ivaAmount,
                'total_amount'       => $sale->total,
                'is_voided'          => false,
            ]);

            $sale->updateQuietly(['tax_document_id' => $taxDoc->id]);

            // Asiento contable
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

            $entryType = JournalEntryType::where('name', 'Diario')->first();
            if (!$entryType) return;

            $entry = JournalEntry::create([
                'entry_date'            => now(),
                'description'           => "Venta {$sale->document_type} - {$docNumber}",
                'reference_type'        => 'sale',
                'reference_id'          => $sale->id,
                'fiscal_period_id'      => $period->id,
                'user_id'               => Auth::id(),
                'journal_entry_type_id' => $entryType->id,
            ]);

            $entry->lines()->create([
                'account_id'  => $debitAccount->id,
                'debit'       => $sale->total,
                'credit'      => 0,
                'description' => "Cobro {$sale->document_type} - {$docNumber}",
            ]);

            $entry->lines()->create([
                'account_id'  => $ventasAccount->id,
                'debit'       => 0,
                'credit'      => $baseAmount,
                'description' => $isCCF ? "Ingreso CCF - base gravada" : "Ingreso FCF",
            ]);

            $entry->lines()->create([
                'account_id'  => $ivaAccount->id,
                'debit'       => 0,
                'credit'      => $ivaAmount,
                'description' => $isCCF ? "IVA débito fiscal (CCF)" : "IVA débito fiscal (FCF)",
            ]);

            $recipient = Auth::user();
            if ($recipient) {
                Notification::make()
                    ->title('Venta registrada')
                    ->body("Documento **{$docNumber}** ({$sale->document_type}) por \${$sale->total}.")
                    ->success()
                    ->sendToDatabase($recipient);
            }
        } catch (Throwable $e) {
            Log::error('SaleObserver error: ' . $e->getMessage());
        }
    }
}
