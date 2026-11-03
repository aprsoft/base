<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxeSeeder extends Seeder
{
    public function run()
    {
        DB::table('taxes')->insert([
            [
                'name' => 'normal',
                'porcentaje' => 19,
            ],
            [
                'name' => 'exento',
                'porcentaje' => 1,
            ],
        ]);
    }
}
