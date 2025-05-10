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
        Schema::create('product_cat_option_desc', function (Blueprint $table) {
            $table->integer('id',11);
            $table->unsignedInteger('op_id')->lenght(11);
            $table->foreign('op_id')
            ->references('op_id')
            ->on('product_cat_option')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('title',150);
            $table->string('slug',250)->nullable()->default('NULL');
            $table->text('description')->nullable();
            $table->string('lang',50)->default('vi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_cat_option_desc');
    }
};
