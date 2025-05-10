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
        Schema::create('contact_config_desc', function (Blueprint $table) {
            $table->increments('id')->length(10);
            $table->unsignedInteger('contact_id')->length(10);
            $table->foreign('contact_id')
            ->references('contact_id')
            ->on('contact_config')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('title', 250)->nullable()->default('NULL');
            $table->string('map_desc', 150)->nullable()->default('NULL');
            $table->string('map_address', 250)->nullable()->default('NULL');
            $table->string('lang', 150)->nullable()->default('vi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_config_desc');
    }
};
