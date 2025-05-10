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
        Schema::create('coupondes', function (Blueprint $table) {
            $table->increments('idCouponDes')->length(11);
            $table->string('MaCouponDes', 250);
            $table->integer('SoLanSuDungDes')->length(11);
            $table->integer('SoLanConLaiDes')->length(11);
            $table->integer('StatusDes')->length(11)->default('0');
            $table->string('DateCreateDes', 150);
            $table->unsignedInteger('idCoupon')->length(11);
            $table->foreign('idCoupon')
            ->references('id')
            ->on('coupon')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->integer('Max')->length(11)->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupondes');
    }
};
