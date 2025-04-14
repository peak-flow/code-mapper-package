<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_maps', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('namespace')->nullable();
            $table->string('file_path');
            $table->json('methods')->nullable();
            $table->json('properties')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        
        Schema::create('class_map_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->json('class_names');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('class_maps');
        Schema::dropIfExists('class_map_groups');
    }
};
