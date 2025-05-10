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
        Schema::create('product_desc', function (Blueprint $table) {
            $table->bigIncrements('id')->length(32);
            $table->unsignedBigInteger('product_id')->length(20);
            $table->foreign('product_id')
            ->references('product_id')
            ->on('product')
            ->onUpdate('cascade')
            ->onDelete('cascade');



            $table->string('title',250);
            $table->longText('description')->nullable();
            $table->text('gift_desc')->nullable();
            $table->text('video_desc')->nullable();
            $table->text('tech_desc')->nullable();
            $table->text('option')->nullable();
            $table->text('short')->nullable();
            $table->string('start_date_promotion',50);
            $table->string('end_date_promotion',50);
            $table->integer('status_promotion')->length(11);
            $table->string('shortcode',250)->nullable()->default('NULL');
            $table->string('key_search',250)->nullable()->default('NULL');
            $table->string('friendly_url',250);
            $table->string('friendly_title',250)->nullable()->default('NULL');
            $table->string('metakey',250)->nullable()->default('NULL');
            $table->text('metadesc')->nullable();
            $table->string('lang',50)->default('vi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_desc');
    }
};
