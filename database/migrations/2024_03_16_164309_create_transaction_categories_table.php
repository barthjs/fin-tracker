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
        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->autoIncrement();

            $table->string('name')->index();
            $table->string('type')->nullable()->index();

            $table->unsignedTinyInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('user_id')->on('sys_users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_categories');
    }
};
