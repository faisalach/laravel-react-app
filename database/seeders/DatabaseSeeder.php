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
            'username'  => 'fortest',
            'email'     => 'fortest@gmail.com',
            'password'  => Hash::make("fortest123"),
        ]);

        $motorcycles = [
            [
                'user_id' => $user->id,
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'Vario 150',
                'number_plate' => 'B 4327 SCH',
            ],
            [
                'user_id' => $user->id,
                'vehicle_brand' => 'Yamaha',
                'vehicle_model' => 'Nmax 155',
                'number_plate' => 'B 5678 DEF',
            ],
            [
                'user_id' => $user->id,
                'vehicle_brand' => 'Suzuki',
                'vehicle_model' => 'Satria F150',
                'number_plate' => 'B 9012 GHI',
            ]
        ];
    
        foreach ($motorcycles as $motorcycle) {
            $vehicle    = \App\Models\Vehicles::create($motorcycle);
            $odometer   = rand(1000,200000);

            \App\Models\Odometer_logs::create([
                "vehicle_id"    => $vehicle->id,
                "odometer"      => $odometer,
                "record_at"     => date("Y-m-01 00:00:00",strtotime("-6 month"))
            ]);


            for($i = 0;$i < 10;$i++){

                    $fuel_name_arr      = ["Pertamax","Pertalite","Shell Super","Shell V-Power"];

                    $random_keys        = array_rand($fuel_name_arr);

                    $int                = rand(strtotime(date("Y-m-d H:i:s",strtotime("-6 month"))),time());
                    $string             = date("Y-m-d H:i:s",$int);

                    $data	                    = new \App\Models\Fuel_logs();
                    $data->vehicle_id			= $vehicle->id;
                    $data->fuel_name			= $fuel_name_arr[$random_keys];
                    $data->price_per_liter		= rand(100,150) * 100;
                    $data->total_price			= rand(10,50) * 1000;
                    $data->number_of_liter		= $data->total_price / $data->price_per_liter;
                    $data->odometer				= $odometer + (rand(10,50) * ($i + 1));
                    $data->filling_date			= $string;
                    $data->save();

                    $odometer_logs					= new \App\Models\Odometer_logs;
                    $odometer_logs->vehicle_id		= $vehicle->id;
                    $odometer_logs->odometer		= $data->odometer;
                    $odometer_logs->record_at  		= $data->filling_date;
                    $odometer_logs->data_from		= "fuel";
                    $odometer_logs->data_from_id	= $data->id;
                    $odometer_logs->save();

                    $odometer   = $data->odometer;
                }
        }
        
    }
}
