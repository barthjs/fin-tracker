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
        Schema::create('portfolios', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->autoIncrement();
            $table->timestamps();

            $table->string('name')->index();
            $table->decimal('market_value', 18, 6)->default(0)->index();
            $table->text('description')->nullable();

            $table->string('logo')->nullable();
            $table->string('color');
            $table->boolean('active')->default(true)->index();

            $table->unsignedTinyInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('sys_users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
