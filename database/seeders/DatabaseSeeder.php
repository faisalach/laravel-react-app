<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $user   = \App\Models\User::create([
            'username' => 'fortest',
            'password' => Hash::make("fortest123"),
        ]);

        $vehicle    = \App\Models\Vehicles::create([
            'user_id' => $user->id,
            'vehicle_brand' => "Honda",
            'vehicle_model' => "Vario 150",
            'number_plate' => "B 4327 SCH",
        ]);

        $odometer    = \App\Models\Odometer_logs::create([
            'vehicle_id' => $vehicle->id,
            'odometer' => "44000"
        ]);

        
    }
}
