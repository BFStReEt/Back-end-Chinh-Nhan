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
        Schema::create('product_group', function (Blueprint $table) {
            $table->bigIncrements('id_group')->length(11);
            $table->bigInteger('product_main')->length(11);
            $table->bigInteger('product_child')->length(11);
            $table->integer('date_post')->length(11);
            $table->integer('date_update')->length(11);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_group');
    }
};
