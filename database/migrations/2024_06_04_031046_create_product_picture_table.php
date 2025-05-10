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
        Schema::create('product_pictured', function (Blueprint $table) {
            $table->increments('id')->length(11);
           

            $table->unsignedBigInteger('product_id')->length(20);
            $table->foreign('product_id')
            ->references('product_id')
            ->on('product')
            ->onUpdate('cascade')
            ->onDelete('cascade');


            // $table->unsignedInteger('pid')->length(20);
            // $table->foreign('pid')
            // ->references('product_id')
            // ->on('product')
            // ->onUpdate('cascade')
            // ->onDelete('cascade');
            $table->string('pic_name',150)->nullable()->default('NULL');
            $table->string('picture',150);
            $table->integer('menu_order')->length(11)->default('0');
            $table->tinyInteger('display')->length(4)->default('1');
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
        Schema::dropIfExists('product_picture');
    }
};
