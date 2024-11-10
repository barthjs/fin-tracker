<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('securities_portfolios_r', function (Blueprint $table) {
            $table->unsignedInteger('security_id');
            $table->foreign('security_id')->references('id')->on('securities')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedSmallInteger('portfolio_id');
            $table->foreign('portfolio_id')->references('id')->on('portfolios')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('securities_portfolios_r');
    }
};
