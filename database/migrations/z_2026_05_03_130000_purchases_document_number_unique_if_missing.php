<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchases') || ! Schema::hasColumn('purchases', 'document_number')) {
            return;
        }

        $this->dedupeDocumentNumbers('purchases');

        Schema::table('purchases', function (Blueprint $table) {
            if (! $this->indexExists('purchases', 'purchases_document_number_unique')) {
                $table->unique('document_number', 'purchases_document_number_unique');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('purchases') || ! Schema::hasColumn('purchases', 'document_number')) {
            return;
        }

        Schema::table('purchases', function (Blueprint $table) {
            if ($this->indexExists('purchases', 'purchases_document_number_unique')) {
                $table->dropUnique('purchases_document_number_unique');
            }
        });
    }

    private function dedupeDocumentNumbers(string $table): void
    {
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
