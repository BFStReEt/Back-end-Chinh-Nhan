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
        Schema::create('order_sum', function (Blueprint $table) {
            $table->increments('order_id')->length(11);
            $table->string('order_code', 255);
            $table->string('MaKH')->nullable()->default('NULL');
            $table->string('d_name',250)->nullable()->default('');
            $table->string('d_address',150)->nullable()->default('');
            $table->string('d_country',150)->nullable()->default('NULL');
            $table->string('d_city',150)->nullable()->default('NULL');
            $table->string('d_phone',150)->nullable()->default('');
            $table->string('d_email',150)->nullable()->default('');
            $table->string('c_name',250);
            $table->string('c_address',250);
            $table->string('c_country',150)->nullable()->default('NULL');
            $table->string('c_city',150)->nullable()->default('NULL');
            $table->string('c_phone',150);
            $table->string('c_email',150);
            $table->integer('s_price')->length(11)->default('0');
            $table->bigInteger('total_cart')->length(20)->default('0');
            $table->bigInteger('total_price')->length(20)->nullable();
            $table->string('shipping_method',50)->nullable()->default('');
            $table->string('payment_method',50)->nullable()->default('');
            $table->unsignedInteger('status')->length(11);
            $table->foreign('status')
            ->references('status_id')
            ->on('order_status')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('date_order',150)->nullable()->default('');
            $table->string('ship_date',150)->nullable()->default('');
            $table->string('comment',250)->nullable()->default('');
            $table->text('note')->nullable();
            $table->integer('menu_order')->length(11)->default('0');
            $table->tinyInteger('display')->length(4)->default('1');
            $table->string('lang',50)->default('vi');
            $table->integer('mem_id')->length(11)->default('0');
            $table->bigInteger('CouponDiscout')->length(20)->default('0');
            $table->integer('diem_use')->length(11)->nullable()->default('0');
            $table->integer('diem_tich')->length(11)->nullable()->default('0');
            $table->integer('status_diem')->length(11)->nullable();
            $table->string('careate_at');
            $table->string('update_at');
            $table->string('time_pay');
            $table->string('time_deli');
            $table->string('time_done');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_sum');
    }
};
