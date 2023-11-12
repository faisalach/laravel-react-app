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
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->increments("id");
            $table->integer("vehicle_id")->unsigned();
            $table->string("fuel_name",100);
            $table->integer("price_per_liter");
            $table->integer("total_price");
            $table->float("number_of_liter");
            $table->integer("odometer");
            $table->timestamp("filling_date")->useCurrent();
            
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
        Schema::dropIfExists('fuel_logs');
    }
};
