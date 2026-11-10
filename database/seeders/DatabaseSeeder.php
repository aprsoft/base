<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

 
    public function run(): void
    {
        $this->call([
            //TaxeSeeder::class,
            //ServiceSeeder::class,            
            //CustomerSeeder::class,
            //TypedocumentSeeder::class,
            CompanySeeder::class,
            AdminRolePermissionSeeder::class,
            ExecutiveRolePermissionSeeder::class,
            UserSeeder::class,
        ]);
    }
}
