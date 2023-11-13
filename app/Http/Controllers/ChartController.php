<?php

namespace App\Http\Controllers;

use App\Models\Fuel_logs;
use App\Models\Maintenances;
use App\Models\Odometer_logs;
use App\Models\Vehicles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
	public function get_data_chart_kilometer(){
		$range_month	= 6;
		$month_arr		= $this->get_range_month($range_month);


		$data 			= [];
		$vehicles 		= Vehicles::all();
		foreach($vehicles as $vehicle){
			$kilometers_arr 	= [];
			foreach($month_arr as $key 	=> $month_name){
				$split 	= explode("-",$key);
				$year 	= $split[0];
				$month 	= $split[1];

				$data_odometers 	= Odometer_logs::select(DB::raw("(MAX(odometer)-MIN(odometer)) as odometer"))
				->where(DB::raw("YEAR(created_at)"),$year)
				->where(DB::raw("MONTH(created_at)"),$month)
				->where("vehicle_id",$vehicle->id)
				->first();

				$kilometers_arr[] 	= !empty($data_odometers->odometer) ? $data_odometers->odometer : 0;
			}


			$data[] 	= [
				"name" 	=> $vehicle->vehicle_brand . " " .$vehicle->vehicle_model,
				"data" 	=> $kilometers_arr,
			];
		}
		
		return [
			"month"     => array_values($month_arr),
			"data"      => $data
		];
	}

	public function get_data_chart_fuel(){
		$range_month	= 6;
		$month_arr		= $this->get_range_month($range_month);


		$data 			= [];
		$vehicles 		= Vehicles::all();
		foreach($vehicles as $vehicle){
			$fuel_arr 	= [];
			foreach($month_arr as $key 	=> $month_name){
				$split 	= explode("-",$key);
				$year 	= $split[0];
				$month 	= $split[1];

				$data_fuel 	= Fuel_logs::select(DB::raw("SUM(number_of_liter) as total_liter"))
				->where(DB::raw("YEAR(filling_date)"),$year)
				->where(DB::raw("MONTH(filling_date)"),$month)
				->where("vehicle_id",$vehicle->id)
				->first();

				$fuel_arr[] 	= !empty($data_fuel->total_liter) ? $data_fuel->total_liter : 0;
			}


			$data[] 	= [
				"name" 	=> $vehicle->vehicle_brand . " " .$vehicle->vehicle_model,
				"data" 	=> $fuel_arr,
			];
		}
		
		return [
			"month"     => array_values($month_arr),
			"data"      => $data
		];
	}

	public function get_data_chart_fc(){
		$range_month	= 6;
		$month_arr		= $this->get_range_month($range_month);

		$data 			= [];

		$kilometers 	= $this->get_data_chart_kilometer()["data"];
		$fuels 			= $this->get_data_chart_fuel()["data"];

		foreach($kilometers as $key => $km){
			$fc_arr 		= [];
			$fuel_arr 		= $fuels[$key]["data"];
			$kilometer_arr 	= $km["data"];

			for($i = 0; $i < count($kilometer_arr);$i++){
				if(!empty($fuel_arr[$i])){
					$fc_arr[] 	= $kilometer_arr[$i] / $fuel_arr[$i];
				}else{
					$fc_arr[] 	= 0;
				}
			}

			$data[] 	= [
				"name" 	=> $km["name"],
				"data" 	=> $fc_arr
			];
		}
		

		return response()->json([
			"month"     => array_values($month_arr),
			"data"      => $data
		]);
	}

	public function get_range_month($range_month){
		$dateStart      = date("Y-m-d");
		$month_arr      = [];
		
		for($i = $range_month; $i >= 0;$i--){
			$idx 	= date("Y-m",strtotime("-".$i." month",strtotime($dateStart)));
			$month_arr[$idx] = date("M",strtotime("-".$i." month",strtotime($dateStart)));
		}
		return $month_arr;
	}
}
