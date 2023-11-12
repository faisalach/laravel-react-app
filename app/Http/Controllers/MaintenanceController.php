<?php

namespace App\Http\Controllers;

use App\Models\Maintenances;
use App\Models\Detail_maintenance;
use App\Models\Odometer_logs;
use App\Models\Vehicles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    public function get_data(Request $request,$vehicle_id){
        $data       = Maintenances::select()->with(["detail_maintenance"]);
        $user       = Auth::user();

        $limit      = $request->input("limit");
        $offset     = $request->input("offset");
        $where      = $request->input("where");
        
        $data->where("vehicle_id",$vehicle_id);

        if(!empty($limit)){
            $data->limit($limit);
        }
        if(!empty($offset)){
            $data->offset($offset);
        }

        if(!empty($where)){
            $data->where($where);
        }

        return $data->get();

    }

    public function get_reminder(Request $request,$vehicle_id){
        // validasi user request
        $user       = Auth::user();
        $vehicle    = Vehicles::find($vehicle_id);
        if($vehicle->user_id !== $user->id){
            return null;
        }
        
        $detail_maintenance       = Detail_maintenance::select("detail_maintenance.*");

        $detail_maintenance->join("maintenances","maintenance_id","=","maintenances.id");
        $detail_maintenance->where("vehicle_id",$vehicle_id);
        $detail_maintenance->where(function($query){
            $query->where("reminder_on_date","!=",null);
            $query->orWhere("reminder_on_kilometer","!=",null);
        });

        $limit      = $request->input("limit");
        $offset     = $request->input("offset");
        if(!empty($limit)){
            $detail_maintenance->limit($limit);
        }
        if(!empty($offset)){
            $detail_maintenance->offset($offset);
        }


        return $detail_maintenance->get();

    }

    public function insert(Request $request,$vehicle_id){
        $data   = new Maintenances();
        $user   = Auth::user();

        $vehicle    = Vehicles::find($vehicle_id);
        if($vehicle->user_id !== $user->id){
            return response([
                "message" => "Data not Found"
            ],422);
        }

        $request->validate([
            'odometer'  => 'required|numeric',
            'maintenance_date' => 'required',
            'title.*' => 'required',
            'price.*' => 'required',
        ]);

        $data->vehicle_id           = $vehicle_id;
        $data->odometer             = $request->input("odometer");
        $data->maintenance_date     = $request->input("maintenance_date");

        if($data->save()){
            
            foreach($request->input("title") as $key => $title){
                $detail_data                        = new Detail_maintenance;
                $detail_data->maintenance_id        = $data->id;
                $detail_data->title                 = $request->input("title")[$key];
                $detail_data->price                 = $request->input("price")[$key];
                $detail_data->reminder_on_kilometer = $request->input("reminder_on_kilometer")[$key];
                $detail_data->reminder_on_date      = $request->input("reminder_on_date")[$key];
                $detail_data->save();
            }
            
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
}
