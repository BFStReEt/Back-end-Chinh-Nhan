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
        Schema::create('order_address', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->unsignedInteger('order_id')->length(11);
            $table->foreign('order_id')
            ->references('order_id')
            ->on('order_sum')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('district', 200)->nullable()->default('NULL');
            $table->string('ward', 200)->nullable()->default('NULL');
            $table->string('province', 200)->nullable()->default('NULL');
            $table->string('address',500)->default('NULL');
            $table->string('from_day')->length(255)->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_address');
    }
};
