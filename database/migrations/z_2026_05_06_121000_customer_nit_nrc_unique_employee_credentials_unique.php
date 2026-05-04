<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $conn = Schema::getConnection();

        if ($conn->getDriverName() === 'sqlite') {
            $rows = $conn->select("PRAGMA index_list('".$table."')");

            foreach ($rows as $row) {
                if (($row->name ?? '') === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $database = $conn->getDatabaseName();
        $row = $conn->selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName],
        );

        return isset($row->c) && (int) $row->c > 0;
    }

    public function up(): void
    {
        if (Schema::hasTable('employees')) {
            DB::table('employees')->where('isss', '')->update(['isss' => null]);
            DB::table('employees')->where('afp', '')->update(['afp' => null]);

            Schema::table('employees', function (Blueprint $table) {
                if (! $this->indexExists('employees', 'employees_isss_unique')) {
                    $table->unique('isss');
                }
                if (! $this->indexExists('employees', 'employees_afp_unique')) {
                    $table->unique('afp');
                }
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (! $this->indexExists('customers', 'customers_nit_unique')) {
                    $table->unique('nit', 'customers_nit_unique');
                }
                if (! $this->indexExists('customers', 'customers_nrc_unique')) {
                    $table->unique('nrc', 'customers_nrc_unique');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if ($this->indexExists('customers', 'customers_nit_unique')) {
                    $table->dropUnique('customers_nit_unique');
                }
                if ($this->indexExists('customers', 'customers_nrc_unique')) {
                    $table->dropUnique('customers_nrc_unique');
                }
            });
        }

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if ($this->indexExists('employees', 'employees_isss_unique')) {
                    $table->dropUnique('employees_isss_unique');
                }
                if ($this->indexExists('employees', 'employees_afp_unique')) {
                    $table->dropUnique('employees_afp_unique');
                }
            });
        }
    }
};
