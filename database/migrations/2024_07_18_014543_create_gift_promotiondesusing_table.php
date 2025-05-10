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
        Schema::create('gift_promotiondesusing', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->integer('IDuser')->length(11)->default('0');
            $table->unsignedInteger('idGiftPromotion')->length(11);
            $table->foreign('idGiftPromotion')
            ->references('id')
            ->on('gift_promotion')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('DateUsingCode',150);
            $table->string('IDOrderCode',250);
            $table->string('MaGiftPromotion',150);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_promotiondesusing');
    }
};
