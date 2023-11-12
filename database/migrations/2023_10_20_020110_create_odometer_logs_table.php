<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('odometer_logs', function (Blueprint $table) {
            $table->increments("id");
            $table->integer("vehicle_id")->unsigned();
            $table->integer("odometer");
            $table->string("data_from",100);
            $table->integer("data_from_id");
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete("cascade");
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odometer_logs');
    }
};
