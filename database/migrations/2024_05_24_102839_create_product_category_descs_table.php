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
        Schema::create('product_category_desc', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->unsignedInteger('cat_id')->length(11);
            $table->foreign('cat_id')
            ->references('cat_id')
            ->on('product_category')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('cat_name',250);
            $table->string('home_title',250)->nullable()->default('NULL');
            $table->longText('description')->nullable();
            $table->string('friendly_url',250);
            $table->string('friendly_title',250)->nullable()->default('NULL');
            $table->string('metakey',250)->nullable()->default('NULL');
            $table->text('metadesc')->nullable();
            $table->string('lang',50)->default('vi');
            $table->text('script_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category_descs');
    }
};
