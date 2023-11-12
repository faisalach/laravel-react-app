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
        Schema::create('detail_maintenance', function (Blueprint $table) {
            $table->increments("id");
            $table->integer("maintenance_id")->unsigned();
            $table->string("title");
            $table->integer("price");
            $table->integer("reminder_on_kilometer")->nullable();
            $table->date("reminder_on_date")->nullable();
            $table->foreign('maintenance_id')->references('id')->on('maintenances')->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_maintenance');
    }
};
