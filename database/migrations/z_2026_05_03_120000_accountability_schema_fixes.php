<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'status')) {
                $table->string('status', 20)->default('vigente')->after('payment_method');
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'payment_method')) {
                $table->string('payment_method', 32)->default('Efectivo')->after('status');
            }
        });

        if (Schema::hasTable('tax_documents') && Schema::hasColumn('tax_documents', 'document_number')) {
            $this->dedupeDocumentNumbers('tax_documents');
            Schema::table('tax_documents', function (Blueprint $table) {
                if (! $this->indexExists('tax_documents', 'tax_documents_document_number_unique')) {
                    $table->unique('document_number', 'tax_documents_document_number_unique');
                }
            });
        }

        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'document_number')) {
            $this->dedupeDocumentNumbers('purchases');
            Schema::table('purchases', function (Blueprint $table) {
                if (! $this->indexExists('purchases', 'purchases_document_number_unique')) {
                    $table->unique('document_number', 'purchases_document_number_unique');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'document_number')) {
            Schema::table('purchases', function (Blueprint $table) {
                if ($this->indexExists('purchases', 'purchases_document_number_unique')) {
                    $table->dropUnique('purchases_document_number_unique');
                }
            });
        }

        if (Schema::hasTable('tax_documents') && Schema::hasColumn('tax_documents', 'document_number')) {
            Schema::table('tax_documents', function (Blueprint $table) {
                if ($this->indexExists('tax_documents', 'tax_documents_document_number_unique')) {
                    $table->dropUnique('tax_documents_document_number_unique');
                }
            });
        }

        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    private function dedupeDocumentNumbers(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $dupes = DB::table($table)
            ->select('document_number')
            ->whereNotNull('document_number')
            ->where('document_number', '!=', '')
            ->groupBy('document_number')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('document_number');

        foreach ($dupes as $value) {
            $ids = DB::table($table)->where('document_number', $value)->orderBy('id')->pluck('id');
            $ids = $ids->values();
            $ids->shift();
            foreach ($ids as $id) {
                DB::table($table)->where('id', $id)->update([
                    'document_number' => $value.'-ID'.$id,
                ]);
            }
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $row = $connection->selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $index]
        );

        return isset($row->c) && (int) $row->c > 0;
    }
};
