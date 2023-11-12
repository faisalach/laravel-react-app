<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail_maintenance extends Model
{
    use HasFactory;

    protected $table = "detail_maintenance";

    public function maintenance()
    {
        return $this->belongsTo(Maintenances::class,"maintenance_id","id");
    }

}
