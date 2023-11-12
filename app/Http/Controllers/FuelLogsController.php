<?php

namespace App\Http\Controllers;

use App\Models\Fuel_logs;
use App\Models\Maintenances;
use App\Models\Odometer_logs;
use App\Models\Vehicles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FuelLogsController extends Controller
{
    public function get_data(Request $request,$vehicle_id){

        // validasi user request
        $user       = Auth::user();
        $vehicle    = Vehicles::find($vehicle_id);
        if($vehicle->user_id !== $user->id){
            return null;
        }

        // inputan
        $limit      = $request->input("limit");
        $offset     = $request->input("offset");

        $fuel_logs  = Fuel_logs::select();
        $fuel_logs->where("vehicle_id",$vehicle_id);
        $fuel_logs->orderBy("filling_date","DESC");

        $result     = $fuel_logs->get();

        foreach($result as $row){
            $before_fuel_logs       = Fuel_logs::where("filling_date","<",$row->filling_date)
            ->orderBy("filling_date","DESC")
            ->limit(1)
            ->first();

            if(!empty($before_fuel_logs)){
                $row->fuel_consumption  = ($row->odometer - $before_fuel_logs->odometer) / $row->number_of_liter;
            }else{
                $row->fuel_consumption  = "-";
            }
        }

        return $result;
    }

    public function insert(Request $request,$vehicle_id){
        $data   = new Fuel_logs();
        $user   = Auth::user();

        $vehicle    = Vehicles::find($vehicle_id);
        if($vehicle->user_id !== $user->id){
            return response([
                "message" => "Data not Found"
            ],422);
        }

        $request->validate([
            'fuel_name' => 'required',
            'price_per_liter' => 'required',
            'total_price' => 'required',
            'odometer' => 'required',
            'filling_date' => 'required|date',
        ]);

        $data->vehicle_id         = $vehicle_id;
        $data->fuel_name          = $request->input("fuel_name");
        $data->price_per_liter    = $request->input("price_per_liter");
        $data->total_price        = $request->input("total_price");
        $data->number_of_liter    = $data->total_price / $data->price_per_liter;
        $data->odometer           = $request->input("odometer");
        $data->filling_date       = $request->input("filling_date");


        if($data->save()){
            $odometer_logs              = new Odometer_logs;
            $odometer_logs->vehicle_id  = $vehicle_id;
            $odometer_logs->odometer    = $data->odometer;
            $odometer_logs->save();

            return response([
                "message" => "Successfuly insert data"
            ]);
        }else{
            return response([
                "message" => "Failed, please try again"
            ]);
        }
    }

    public function update(Request $request,$id){
        $data   = Fuel_logs::find($id);
        $user   = Auth::user();

        if(empty($data)){
            return response([
                "message" => "Data not Found"
            ],422);
        }

        $vehicle    = Vehicles::find($data->vehicle_id);
        if($vehicle->user_id !== $user->id){
            return response([
                "message" => "Data not Found"
            ],422);
        }

        $request->validate([
            'fuel_name' => 'required',
            'price_per_liter' => 'required',
            'total_price' => 'required',
            'odometer' => 'required',
            'filling_date' => 'required|date',
        ]);
        $old_odometer               = $data->odometer;

        $data->fuel_name          = $request->input("fuel_name");
        $data->price_per_liter    = $request->input("price_per_liter");
        $data->total_price        = $request->input("total_price");
        $data->number_of_liter    = $data->total_price / $data->price_per_liter;
        $data->odometer           = $request->input("odometer");
        $data->filling_date       = $request->input("filling_date");

        if($data->save()){
            $odometer_logs              = Odometer_logs::where("vehicle_id",$data->vehicle_id)
            ->where("odometer",$old_odometer)->first();
            $odometer_logs->odometer    = $data->odometer;
            $odometer_logs->save();

            return response([
                "message" => "Successfuly update data"
            ]);
        }else{
            return response([
                "message" => "Failed, Please try again"
            ],422);
        }
    }

    public function delete(Request $request,$id){
        $data   = Fuel_logs::find($id);
        $user   = Auth::user();

        if(empty($data)){
            return response([
                "message" => "Data not Found"
            ],422);
        }

        $vehicle    = Vehicles::find($data->vehicle_id);
        if($vehicle->user_id !== $user->id){
            return response([
                "message" => "Data not Found"
            ],422);
        }

        $old_odometer               = $data->odometer;
        if($data->delete()){
            $odometer_logs              = Odometer_logs::where("vehicle_id",$data->vehicle_id)
            ->where("odometer",$old_odometer)->first();
            $odometer_logs->delete();

            return response([
                "message" => "Successfuly delete data"
            ]);
        }else{
            return response([
                "message" => "Failed, Please try again"
            ],422);
        }
    }
}
