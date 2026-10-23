<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

            User::factory()->create([                          
                'name' => 'SuperAdministrador',            
                'email' => 'superadmin@aprsoft.cl',
            ])->assignRole('SuperAdmin');

        User::factory()->create([            
            'name' => 'Administrador',            
            'email' => 'admin@aprsoft.cl',
        ])->assignRole('Admin');

        User::factory(9)->create();

        
    }
}
