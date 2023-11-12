<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicles extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','vehicle_brand','vehicle_model','number_plate']; 

    public function fuel_logs()
    {
        return $this->hasMany(Fuel_logs::class,"vehicle_id","id");
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenances::class,"vehicle_id","id");
    }

    public function odometer_logs()
    {
        return $this->hasMany(Odometer_logs::class,"vehicle_id","id");
    }

    public function last_odometer()
    {
        return $this->hasOne(Odometer_logs::class,"vehicle_id","id")->orderBy('odometer_logs.created_at', 'desc');
    }

}
