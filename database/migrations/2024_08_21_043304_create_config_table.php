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
        Schema::create('config', function (Blueprint $table) {
            $table->id();
            $table->string('priceOfPoint',255)->nullable();
            $table->string('productOfPage',255)->nullable();
            $table->string('width',255)->nullable();
            $table->tinyInteger('displayPicture')->length(4)->default('1');
            $table->string('picture',255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config');
    }
};
