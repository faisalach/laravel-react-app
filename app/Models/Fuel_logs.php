<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fuel_logs extends Model
{
    use HasFactory;

    public function vehicle()
    {
        return $this->belongsTo(Vehicles::class,"vehicle_id","id");
    }

}
