<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypedocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('typedocuments')->insert([
            ['id' => 1,  'name' => 'AVISO DE COBRO', 'transacciontype' => 'INGRESO',       'tipo' => null,             'codigo' => null, 'abrev' => null,   'created_at' => now(), 'updated_at' => now()],
            ['id' => 2,  'name' => 'NOTA DE VENTA',  'transacciontype' => 'INGRESO',       'tipo' => null,             'codigo' => null, 'abrev' => null,   'created_at' => now(), 'updated_at' => now()],
            ['id' => 3,  'name' => 'NOTA DE COMPRA', 'transacciontype' => 'EGRESO',        'tipo' => null,             'codigo' => null, 'abrev' => null,   'created_at' => now(), 'updated_at' => now()],
            ['id' => 4,  'name' => 'ORDEN DE COMPRA','transacciontype' => 'EGRESO',        'tipo' => null,             'codigo' => null, 'abrev' => null,   'created_at' => now(), 'updated_at' => now()],
            ['id' => 5,  'name' => 'ORDEN DE TRABAJO','transacciontype' => 'INGRESO',      'tipo' => null,             'codigo' => null, 'abrev' => null,   'created_at' => now(), 'updated_at' => now()],
            ['id' => 6,  'name' => 'COMPROBANTE',   'transacciontype' => 'INGRESO',       'tipo' => null,             'codigo' => null, 'abrev' => null,   'created_at' => now(), 'updated_at' => now()],
            ['id' => 7,  'name' => 'NOTA DE EGRESO','transacciontype' => 'EGRESO',        'tipo' => null,             'codigo' => null, 'abrev' => 'NE',   'created_at' => now(), 'updated_at' => now()],
            ['id' => 8,  'name' => 'NOTA DE INGRESO','transacciontype' => 'INGRESO',      'tipo' => null,             'codigo' => null, 'abrev' => null,   'created_at' => now(), 'updated_at' => now()],
            ['id' => 9,  'name' => 'COMPROBANTE DE VUELTO','transacciontype' => 'INGRESO','tipo' => null,             'codigo' => null, 'abrev' => null,   'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'name' => 'VALE',           'transacciontype' => 'EGRESO',       'tipo' => null,             'codigo' => null, 'abrev' => null,   'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'name' => 'TRIBUTARIO',     'transacciontype' => 'NO APLICA',    'tipo' => null,             'codigo' => null, 'abrev' => null,   'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'name' => 'BOLETA EXENTA',  'transacciontype' => 'INGRESO',      'tipo' => 'TRIBUTARIO',     'codigo' => 41,   'abrev' => 'BO(E)','created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'name' => 'NOTA DE CREDITO','transacciontype' => 'EGRESO',       'tipo' => 'TRIBUTARIO',     'codigo' => 61,   'abrev' => 'NC',  'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'name' => 'NOTA DE DEBITO', 'transacciontype' => 'NO APLICA',    'tipo' => 'TRIBUTARIO',     'codigo' => 56,   'abrev' => 'ND',  'created_at' => now(), 'updated_at' => now()],
            ['id' => 15, 'name' => 'FACTURA',        'transacciontype' => 'EGRESO',       'tipo' => 'TRIBUTARIO',     'codigo' => 33,   'abrev' => 'FA',  'created_at' => now(), 'updated_at' => now()],
            ['id' => 16, 'name' => 'FACTURA EXENTA', 'transacciontype' => 'INGRESO',      'tipo' => 'TRIBUTARIO',     'codigo' => 34,   'abrev' => 'FA(E)','created_at' => now(), 'updated_at' => now()],
            ['id' => 17, 'name' => 'BOLETA',         'transacciontype' => 'INGRESO',      'tipo' => 'TRIBUTARIO',     'codigo' => 39,   'abrev' => 'BO',  'created_at' => now(), 'updated_at' => now()],
            ['id' => 18, 'name' => 'GUIA DE DESPACHO','transacciontype' => 'NO APLICA',   'tipo' => 'TRIBUTARIO',     'codigo' => 52,   'abrev' => 'GD',  'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

