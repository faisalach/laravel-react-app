<?php

namespace App\Http\Controllers;

use App\Models\Odometer_logs;
use App\Models\Vehicles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class VehicleController extends Controller
{
    public function get_data(Request $request){
        $user   = Auth::user();
        $data   = Vehicles::select()->with(["last_odometer"]);

        $limit          = $request->input("limit");
        $offset         = $request->input("offset");
        $where          = $request->input("where");
        
        $data->where("user_id",$user->id);

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

    public function get_by_id(Request $request,$vehicle_id){
        $data   = Vehicles::where('id',$vehicle_id)->with(["last_odometer"])->first();
        $user   = Auth::user();

        if (empty($data)){
            return $data;
        }

        if($data->user_id != $user->id){
            return null;
        }

        return $data;
    }

    public function insert(Request $request){
        $request->validate([
            'vehicle_brand' => 'required',
            'vehicle_model' => 'required',
            'number_plate'  => 'required|max:15',
            'odometer'      => 'required|numeric',
        ]);

        $data   = new Vehicles();
        $user   = Auth::user();

        $data->user_id          = $user->id;
        $data->vehicle_brand    = $request->input("vehicle_brand");
        $data->vehicle_model    = $request->input("vehicle_model");
        $data->number_plate     = $request->input("number_plate");

        if($data->save()){
            $odometer_logs              = new Odometer_logs;
            $odometer_logs->vehicle_id  = $data->id;
            $odometer_logs->odometer    = $request->input("odometer");
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

    public function update(Request $request,$vehicle_id){
        $data   = Vehicles::where("id",$vehicle_id)->with(["last_odometer"])->first();
        $user   = Auth::user();

        if(empty($data)){
            return response([
                "message" => "Data not Found"
            ],422);
        }

        if($user->id !== $data->user_id){
            return response([
                "message" => "Data not Found"
            ],422);
        }

        if(empty($data->last_odometer)){
            $request->validate([
                'vehicle_brand' => 'required',
                'vehicle_model' => 'required',
                'number_plate'  => 'required|max:15',
                'odometer'      => 'required|numeric',
            ]);
        } else {
            $request->validate([
                'vehicle_brand' => 'required',
                'vehicle_model' => 'required',
                'number_plate'  => 'required|max:15',
            ]);
        }

        $data->vehicle_brand   = $request->input("vehicle_brand");
        $data->vehicle_model   = $request->input("vehicle_model");
        $data->number_plate    = $request->input("number_plate");

        if($data->save()){

            if(empty($data->last_odometer)){
                $odometer_logs              = new Odometer_logs;
                $odometer_logs->vehicle_id  = $data->id;
                $odometer_logs->odometer    = $request->input("odometer");
                $odometer_logs->save();
            }

            return response([
                "message" => "Successfuly update data",
            ]);
        }else{
            return response([
                "message" => "Failed, please try again"
            ]);
        }
    }

    public function delete(Request $request,$vehicle_id){

        $data   = Vehicles::find($vehicle_id);
        $user   = Auth::user();

        if(empty($data)){
            return response([
                "message" => "Data not Found"
            ],422);
        }

        if($user->id !== $data->user_id){
            return response([
                "message" => "Data not Found"
            ],422);
        }

        if(Vehicles::where("user_id",$data->user_id)->count() <= 1){
            return response([
                "message" => "Cant delete this vehicle"
            ],422);
        }


        if($data->delete()){
            return response([
                "message" => "Successfuly delete data"
            ]);
        }else{
            return response([
                "message" => "Failed, please try again"
            ]);
        }
    }
}
