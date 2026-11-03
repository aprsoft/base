<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        DB::table('services')->insert(
            [
                
            ['id' => '1','name' => 'CARGO FIJO','taxe_id' => '2','typeservice_id' => '1','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'BASICOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '4340','iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => NULL],
            ['id' => '2','name' => 'TRAMO 1 (1 a 15 METROS CUBICOS]','taxe_id' => '2','typeservice_id' => '1','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'BASICOS','tramo_inicial' => '0','tramo_final' => '15','es_tramo' => '1','valor' => '600','iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => NULL],
            ['id' => '3','name' => 'TRAMO 2 (16 a 25 METROS CUBICOS]','taxe_id' => '2','typeservice_id' => '1','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'BASICOS','tramo_inicial' => '16','tramo_final' => '25','es_tramo' => '1','valor' => '650','iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => NULL],
            ['id' => '4','name' => 'TRAMO 3 (26 a 30 METROS CUBICOS]','taxe_id' => '2','typeservice_id' => '1','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'BASICOS','tramo_inicial' => '26','tramo_final' => '30','es_tramo' => '1','valor' => '800','iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => NULL],
            ['id' => '5','name' => 'TRAMO 4 (31 A 40 METROS CUBICOS]','taxe_id' => '2','typeservice_id' => '1','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'BASICOS','tramo_inicial' => '30','tramo_final' => '40','es_tramo' => '1','valor' => '2000','iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => NULL],
            ['id' => '6','name' => 'INSTALACION MEDIDOR','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '400000','iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => '2025-12-19 17:58:21'],
            ['id' => '7','name' => 'CORTE Y REPOSICION','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '15000','iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => '2021-05-12 09:27:42'],
            ['id' => '8','name' => 'ABONO MEDIDOR','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '20000','iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => '2025-12-05 18:34:15'],
            ['id' => '9','name' => 'CONVENIO','taxe_id' => '2','typeservice_id' => '4','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SUBSIDIO','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => NULL,'iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => NULL],
            ['id' => '10','name' => 'DESCUENTO','taxe_id' => '2','typeservice_id' => '4','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SUBSIDIO','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => NULL,'iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => NULL],
            ['id' => '11','name' => 'SUBSIDIO','taxe_id' => '2','typeservice_id' => '4','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SUBSIDIO','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => NULL,'iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => NULL],
            ['id' => '12','name' => 'CONSUMO','taxe_id' => '2','typeservice_id' => '4','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SUBSIDIO','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => NULL,'iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => NULL],
            ['id' => '13','name' => 'TEXTO LIBRE','taxe_id' => '2','typeservice_id' => '3','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'PROVEEDORES','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => NULL,'iva' => NULL,'total' => NULL,'created_at' => NULL,'updated_at' => NULL],
            ['id' => '14','name' => 'ABONO  MEDIDOR','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '100000','iva' => NULL,'total' => NULL,'created_at' => '2021-05-25 13:32:44','updated_at' => '2026-01-13 19:31:06'],
            ['id' => '15','name' => 'TRASLADO MEDIDOR','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '150000','iva' => NULL,'total' => NULL,'created_at' => '2021-05-26 13:40:05','updated_at' => '2024-02-09 12:15:44'],
            ['id' => '16','name' => 'TRASLADO MEDIDOR','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '150000','iva' => NULL,'total' => NULL,'created_at' => '2021-08-02 10:58:43','updated_at' => '2022-12-27 12:16:38'],
            ['id' => '17','name' => 'INSTALACION DE MEDIDOR CON CRUCE DE CALLE','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '500000','iva' => NULL,'total' => NULL,'created_at' => '2021-11-10 09:14:39','updated_at' => '2025-01-20 11:56:21'],
            ['id' => '18','name' => 'MULTA POR HURTO DE AGUA POTABLE','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '100000','iva' => NULL,'total' => NULL,'created_at' => '2022-04-22 09:46:22','updated_at' => '2022-04-22 09:46:22'],
            ['id' => '19','name' => 'CORTE SUMINISTRO','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '10000','iva' => NULL,'total' => NULL,'created_at' => '2022-05-09 11:52:00','updated_at' => '2023-06-29 12:52:21'],
            ['id' => '20','name' => 'REPOSICION SUMINISTRO','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '10000','iva' => NULL,'total' => NULL,'created_at' => '2022-05-09 11:52:35','updated_at' => '2023-06-29 12:52:40'],
            ['id' => '21','name' => 'MULTA INASISTENCIA REUNION','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '10000','iva' => NULL,'total' => NULL,'created_at' => '2022-06-20 12:30:53','updated_at' => '2022-06-20 12:30:53'],
            ['id' => '22','name' => 'CAMBIO MEDIDOR Y REPARACION','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '53000','iva' => NULL,'total' => NULL,'created_at' => '2023-06-05 10:26:16','updated_at' => '2024-08-30 10:45:50'],
            ['id' => '23','name' => 'REPARACION MEDIDORES','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '70000','iva' => NULL,'total' => NULL,'created_at' => '2023-09-28 13:07:54','updated_at' => '2023-09-28 13:07:54'],
            ['id' => '24','name' => 'INSTALACION DE MEDIDOR','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '400000','iva' => NULL,'total' => NULL,'created_at' => '2024-06-04 12:33:06','updated_at' => '2024-06-04 12:33:06'],
            ['id' => '25','name' => 'DIFERENCIA CONSUMO','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '12250','iva' => NULL,'total' => NULL,'created_at' => '2024-09-02 11:32:18','updated_at' => '2025-02-27 09:44:56'],
            ['id' => '26','name' => 'ABONO  MEDIDOR','taxe_id' => '2','typeservice_id' => '2','servicestate_id' => '1','estado_servicio' => 'VIGENTE','tipo_servicio' => 'SERVICIOS','tramo_inicial' => NULL,'tramo_final' => NULL,'es_tramo' => NULL,'valor' => '300000','iva' => NULL,'total' => NULL,'created_at' => '2025-03-17 12:24:26','updated_at' => '2025-11-06 14:43:45']

               
            ]);
    }
}
