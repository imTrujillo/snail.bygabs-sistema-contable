<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // ─── ACTIVOS ───────────────────────────────────────
            ['code' => '1000', 'name' => 'ACTIVOS',                      'type' => 'Activo',    'subtype' => 'Corriente',     'parent' => null,   'is_group' => true],

            ['code' => '1100', 'name' => 'ACTIVOS CORRIENTES',           'type' => 'Activo',    'subtype' => 'Corriente',     'parent' => '1000', 'is_group' => true],
            ['code' => '1101', 'name' => 'Bancos',                       'type' => 'Activo',    'subtype' => 'Corriente',     'parent' => '1100', 'is_group' => false],
            ['code' => '1102', 'name' => 'Efectivo',                     'type' => 'Activo',    'subtype' => 'Corriente',     'parent' => '1100', 'is_group' => false],
            ['code' => '1103', 'name' => 'Mercancía',                    'type' => 'Activo',    'subtype' => 'Corriente',     'parent' => '1100', 'is_group' => false],
            ['code' => '1104', 'name' => 'Cuentas por cobrar',           'type' => 'Activo',    'subtype' => 'Corriente',     'parent' => '1100', 'is_group' => false],
            ['code' => '1105', 'name' => 'Inversiones a corto plazo',    'type' => 'Activo',    'subtype' => 'Corriente',     'parent' => '1100', 'is_group' => false],
            ['code' => '1106', 'name' => 'IVA crédito fiscal',           'type' => 'Activo',    'subtype' => 'Corriente',     'parent' => '1100', 'is_group' => false],

            ['code' => '1200', 'name' => 'ACTIVOS NO CORRIENTES',        'type' => 'Activo',    'subtype' => 'No Corriente',  'parent' => '1000', 'is_group' => true],
            ['code' => '1201', 'name' => 'Propiedad Planta y Equipo',    'type' => 'Activo',    'subtype' => 'No Corriente',  'parent' => '1200', 'is_group' => false],
            ['code' => '1202', 'name' => 'Activo intangible',            'type' => 'Activo',    'subtype' => 'No Corriente',  'parent' => '1200', 'is_group' => false],

            // ─── PASIVOS ───────────────────────────────────────
            ['code' => '2000', 'name' => 'PASIVOS',                      'type' => 'Pasivo',    'subtype' => 'Corriente',     'parent' => null,   'is_group' => true],

            ['code' => '2100', 'name' => 'PASIVOS CORRIENTES',           'type' => 'Pasivo',    'subtype' => 'Corriente',     'parent' => '2000', 'is_group' => true],
            ['code' => '2101', 'name' => 'Préstamos bancarios c/p',      'type' => 'Pasivo',    'subtype' => 'Corriente',     'parent' => '2100', 'is_group' => false],
            ['code' => '2102', 'name' => 'Documentos por pagar',         'type' => 'Pasivo',    'subtype' => 'Corriente',     'parent' => '2100', 'is_group' => false],
            ['code' => '2103', 'name' => 'Sueldos y salarios por pagar', 'type' => 'Pasivo',    'subtype' => 'Corriente',     'parent' => '2100', 'is_group' => false],
            ['code' => '2104', 'name' => 'Impuestos por pagar',          'type' => 'Pasivo',    'subtype' => 'Corriente',     'parent' => '2100', 'is_group' => true],
            ['code' => '2104-01', 'name' => 'IVA por pagar',             'type' => 'Pasivo',    'subtype' => 'Corriente',     'parent' => '2104', 'is_group' => false],
            ['code' => '2104-02', 'name' => 'ISR por pagar',             'type' => 'Pasivo',    'subtype' => 'Corriente',     'parent' => '2104', 'is_group' => false],
            ['code' => '2105', 'name' => 'Proveedores',                  'type' => 'Pasivo',    'subtype' => 'Corriente',     'parent' => '2100', 'is_group' => false],

            ['code' => '2200', 'name' => 'PASIVOS NO CORRIENTES',        'type' => 'Pasivo',    'subtype' => 'No Corriente',  'parent' => '2000', 'is_group' => true],
            ['code' => '2201', 'name' => 'Préstamos a largo plazo',      'type' => 'Pasivo',    'subtype' => 'No Corriente',  'parent' => '2200', 'is_group' => false],

            // ─── PATRIMONIO ────────────────────────────────────
            ['code' => '3000', 'name' => 'PATRIMONIO',                   'type' => 'Patrimonio', 'subtype' => 'Corriente',     'parent' => null,   'is_group' => true],
            ['code' => '3101', 'name' => 'Capital social',               'type' => 'Patrimonio', 'subtype' => 'Corriente',     'parent' => '3000', 'is_group' => false],
            ['code' => '3102', 'name' => 'Utilidades retenidas',         'type' => 'Patrimonio', 'subtype' => 'Corriente',     'parent' => '3000', 'is_group' => false],
            ['code' => '3103', 'name' => 'Utilidad del ejercicio',       'type' => 'Patrimonio', 'subtype' => 'Corriente',     'parent' => '3000', 'is_group' => false],

            // ─── INGRESOS ──────────────────────────────────────
            ['code' => '4000', 'name' => 'INGRESOS',                     'type' => 'Ingreso',   'subtype' => 'Operativo',     'parent' => null,   'is_group' => true],
            ['code' => '4100', 'name' => 'Ventas por servicios',         'type' => 'Ingreso',   'subtype' => 'Operativo',     'parent' => '4000', 'is_group' => false],
            ['code' => '4200', 'name' => 'Otros ingresos',               'type' => 'Ingreso',   'subtype' => 'No Operativo',  'parent' => '4000', 'is_group' => false],

            // ─── COSTOS ────────────────────────────────────────
            ['code' => '5000', 'name' => 'COSTOS',                       'type' => 'Costo',     'subtype' => 'Operativo',     'parent' => null,   'is_group' => true],
            ['code' => '5100', 'name' => 'Costo de venta',               'type' => 'Costo',     'subtype' => 'Operativo',     'parent' => '5000', 'is_group' => false],

            // ─── GASTOS ────────────────────────────────────────
            ['code' => '6000', 'name' => 'GASTOS',                       'type' => 'Gasto',     'subtype' => 'Operativo',     'parent' => null,   'is_group' => true],
            ['code' => '6100', 'name' => 'Gastos de administración',     'type' => 'Gasto',     'subtype' => 'administrativo', 'parent' => '6000', 'is_group' => false],
            ['code' => '6200', 'name' => 'Gastos de venta',              'type' => 'Gasto',     'subtype' => 'Venta',         'parent' => '6000', 'is_group' => false],
            ['code' => '6300', 'name' => 'Gastos financieros',           'type' => 'Gasto',     'subtype' => 'Financiero',    'parent' => '6000', 'is_group' => false],
            ['code' => '6400', 'name' => 'Gastos no operativos',         'type' => 'Gasto',     'subtype' => 'No Operativo',  'parent' => '6000', 'is_group' => false],
        ];

        $inserted = [];

        foreach ($accounts as $data) {
            $account_id = $data['parent'] ? ($inserted[$data['parent']] ?? null) : null;

            $account = Account::create([
                'code'       => $data['code'],
                'name'       => $data['name'],
                'type'       => $data['type'],
                'subtype'    => $data['subtype'],
                'account_id'  => $account_id,
                'is_group'   => $data['is_group'],
                'is_default' => true,
            ]);

            $inserted[$data['code']] = $account->id;
        }
    }
}
