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
        Schema::create('contact_config', function (Blueprint $table) {
            $table->increments('contact_id')->length(10);
            $table->string('company', 150)->nullable()->default('NULL');
            $table->string('address', 150)->nullable()->default('NULL');
            $table->string('phone', 250)->nullable()->default('NULL');
            $table->string('fax', 150)->nullable()->default('NULL');
            $table->string('email', 150)->nullable()->default('NULL');
            $table->string('email_order', 150)->nullable()->default('NULL');
            $table->string('website', 150)->nullable()->default('NULL');
            $table->string('work_time', 250)->nullable()->default('NULL');
            $table->string('map_lat', 150)->nullable()->default('NULL');
            $table->string('map_lng', 150)->nullable()->default('NULL');
            $table->integer('menu_order')->length(10)->default('0');
            $table->tinyInteger('display')->length(3)->default('1');
            $table->integer('date_post')->length(10)->default('0');
            $table->integer('date_update')->length(10)->default('0');
            $table->integer('adminid')->length(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_config');
    }
};
