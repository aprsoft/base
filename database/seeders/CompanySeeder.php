<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('companies')->insert(
            [
                'apikey' => '928e15a2d14d4a6292345f04960f4bd3', 
                'rut_emisor' => '76795561-8',       
                'razon_social' => 'HAULMER SPA',             
                'giro_emisor' => 'VENTA AL POR MENOR EN EMPRESAS DE VENTA A DISTANCIA VÍA INTERNET; COMERCIO ELEC', 
                'acteco' => 479100,   
                'direccion_origen' => 'ARTURO PRAT 527   CURICO',
                'comuna_origen' => 'Curico',  
                'codigo_sii_sucursal' => '81303347',
                'url'=> 'https://dev-api.haulmer.com/v2/dte/document',
                'url_document_received'=>'https://dev-api.haulmer.com/v2/dte/document/received',
                'url_document'=>'https://dev-api.haulmer.com/v2/dte/document/'             
            ]);
    }
}

