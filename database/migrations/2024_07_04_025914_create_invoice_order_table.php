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
        Schema::create('invoice_order', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->unsignedInteger('order_id')->length(11);
            $table->foreign('order_id')
            ->references('order_id')
            ->on('order_sum')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('taxCodeCompany', 200)->nullable()->default('NULL');
            $table->string('nameCompany', 200)->nullable()->default('NULL');
            $table->string('emailCompany', 200)->nullable()->default('NULL');
            $table->string('addressCompany',500)->default('NULL');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_order');
    }
};
