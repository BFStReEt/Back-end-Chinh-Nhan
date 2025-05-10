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
        Schema::create('promotion_desc', function (Blueprint $table) {
            $table->bigIncrements('id')->length(32);
            $table->unsignedBigInteger('promotion_id')->length(32);
            $table->foreign('promotion_id')
            ->references('promotion_id')
            ->on('promotion')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('title',250);
            $table->longText('description')->nullable();
            $table->text('short')->nullable();
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
        Schema::dropIfExists('promotion_desc');
    }
};
