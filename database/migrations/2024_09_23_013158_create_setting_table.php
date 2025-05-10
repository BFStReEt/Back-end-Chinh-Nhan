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
        Schema::create('setting', function (Blueprint $table) {
            $table->increments('icon_id')->length(11);
            $table->string('title',250)->nullable()->default('NULL');
            $table->string('meta_desc',250)->nullable()->default('NULL');
            $table->string('meta_extra',250)->nullable()->default('NULL');
            $table->text('script')->nullable()->default('NULL');
            $table->string('google_analytics_id',250)->nullable()->default('NULL');
            $table->string('google_maps_api_id',250)->nullable()->default('NULL');
            $table->string('charset',250)->nullable()->default('NULL');
            $table->string('favicon',250)->nullable()->default('NULL');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting');
    }
};
