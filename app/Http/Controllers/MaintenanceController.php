<?php

namespace App\Http\Controllers;

use App\Models\Maintenances;
use App\Models\Detail_maintenance;
use App\Models\Odometer_logs;
use App\Models\Vehicles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
		$data->orderBy("maintenance_date","DESC");

		$result 	= $data->get();

		foreach($result as $key => $row){
			$row->total_price = 0;

			if(!empty($row->detail_maintenance)){
				foreach($row->detail_maintenance as $dm){
					$row->total_price += $dm->price;
				}
			}

		}

		return $result;

	}

	public function get_reminder(Request $request,$vehicle_id){
		// validasi user request
		$user       = Auth::user();
		$vehicle    = Vehicles::find($vehicle_id);
		if($vehicle->user_id !== $user->id){
			return null;
		}

		$odometer_log	= Odometer_logs::where("vehicle_id",$vehicle->id)
		->orderBy("odometer","DESC")
		->limit(1)
		->first();
		
		$detail_maintenance		= Detail_maintenance::select("detail_maintenance.*");
		$detail_maintenance->join("maintenances","maintenance_id","=","maintenances.id");
		$detail_maintenance->where("vehicle_id",$vehicle_id);
		$detail_maintenance->where(function($query){
			$query->where(function($query2){
				$query2->where("reminder_on_date","!=",null);
				$query2->where("reminder_on_date",">=",DB::raw("DATE(NOW())"));
			});
			$query->orWhere(function($query2){
				$query2->where("reminder_on_kilometer","!=",null);
				if(!empty($odometer_log)){
					$query2->where("reminder_on_kilometer",">=",$odometer_log->odometer);
				}
			});
		});

		$detail_maintenance->orderBy("reminder_on_kilometer","ASC");
		$detail_maintenance->orderBy("reminder_on_date","ASC");

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

	public function get_by_id(Request $request,$maintenance_id){
		$data   = Maintenances::where('id',$maintenance_id)->with(["detail_maintenance"])->first();
		$user   = Auth::user();
		
		if (empty($data)){
			return $data;
		}
		
		$vehicle 	= Vehicles::find($data->vehicle_id);
		if($vehicle->user_id != $user->id){
			return null;
		}

		return $data;
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
			'detail_maintenance' => 'required',
			'detail_maintenance.*' => 'required',
			'detail_maintenance.*.title' => 'required',
			'detail_maintenance.*.price' => 'required',
		]);

		$data->vehicle_id           = $vehicle_id;
		$data->odometer             = $request->input("odometer");
		$data->maintenance_date     = $request->input("maintenance_date");

		if($data->save()){
			
			foreach($request->input("detail_maintenance") as $key => $row){
				$detail_data                        = new Detail_maintenance;
				$detail_data->maintenance_id        = $data->id;
				$detail_data->title					= $row["title"];
				$detail_data->price					= $row["price"];
				if(!empty($row["reminder_on_kilometer"])){
					$detail_data->reminder_on_kilometer = $row["reminder_on_kilometer"];
				}
				if(!empty($row["reminder_on_date"])){
					$detail_data->reminder_on_date 		= $row["reminder_on_date"];
				}
				$detail_data->save();
			}
			
			$odometer_logs                  = new Odometer_logs;
			$odometer_logs->vehicle_id      = $data->vehicle_id;
			$odometer_logs->odometer        = $data->odometer;
			$odometer_logs->record_at  		= $data->maintenance_date;
			$odometer_logs->data_from	    = "maintenance";
			$odometer_logs->data_from_id	= $data->id;
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
		$data   = Maintenances::find($id);
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
			'odometer'  => 'required|numeric',
			'maintenance_date' => 'required',
			'detail_maintenance' => 'required',
			'detail_maintenance.*' => 'required',
			'detail_maintenance.*.title' => 'required',
			'detail_maintenance.*.price' => 'required',
		]);

		$data->odometer             = $request->input("odometer");
		$data->maintenance_date     = $request->input("maintenance_date");

		if($data->save()){

			$input_detail_maintenance 	= $request->input("detail_maintenance");
			$isUpdateIdDetailMaintenance_arr 	= [];
			
			foreach($input_detail_maintenance as $row){
				$id 				= !empty($row["id"]) ? $row["id"] : "";

				$detail_data		= Detail_maintenance::find($id);
				if(empty($detail_data)){
					$detail_data	= new Detail_maintenance;
				}

				$detail_data->maintenance_id        = $data->id;
				$detail_data->title					= $row["title"];
				$detail_data->price					= $row["price"];
				if(!empty($row["reminder_on_kilometer"])){
					$detail_data->reminder_on_kilometer = $row["reminder_on_kilometer"];
				}
				if(!empty($row["reminder_on_date"])){
					$detail_data->reminder_on_date 		= $row["reminder_on_date"];
				}

				$detail_data->save();

				$isUpdateIdDetailMaintenance_arr[] 	= $detail_data->id;
			}

			$deleteDetailMaintenance 	= Detail_maintenance::select()
			->whereNotIn("id",$isUpdateIdDetailMaintenance_arr)
			->where("maintenance_id",$data->id);
			$deleteDetailMaintenance->delete();
			
			$odometer_logs			= Odometer_logs::where("vehicle_id",$data->vehicle_id)
			->where("data_from","maintenance")
			->where("data_from_id",$data->id)
			->first();
			
			if(!empty($odometer_logs)){
				$odometer_logs->record_at  	= $data->maintenance_date;
				$odometer_logs->odometer    = $data->odometer;
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

	public function delete(Request $request,$id){
		$data	= Maintenances::find($id);
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
		
		$old_id					= $data->id;
		if($data->delete()){
			
			$odometer_logs		= Odometer_logs::where("vehicle_id",$data->vehicle_id)
			->where("data_from","maintenance")
			->where("data_from_id",$old_id)
			->first();
			if(!empty($odometer_logs)){
				$odometer_logs->delete();
			}

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
