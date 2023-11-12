<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenances extends Model
{
    use HasFactory;

    public function vehicle()
    {
        return $this->belongsTo(Vehicles::class,"vehicle_id","id");
    }

    public function detail_maintenance()
    {
        return $this->hasMany(Detail_maintenance::class,"maintenance_id","id");
    }

}
