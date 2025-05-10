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
        Schema::create('setting_smtp_security', function (Blueprint $table) {
            $table->increments('id')->length(11);
            $table->string('method',250)->nullable()->default('NULL');
            $table->string('host',250)->nullable()->default('NULL');
            $table->string('port',250)->nullable()->default('NULL');
            $table->string('username',250)->nullable()->default('NULL');
            $table->string('password',250)->nullable()->default('NULL');
            $table->string('from_name',500)->nullable()->default('NULL');
            $table->string('password_security',250)->nullable()->default('NULL');
            $table->string('time_cache',250)->nullable()->default('NULL');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_smtp');
    }
};
