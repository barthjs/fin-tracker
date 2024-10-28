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
        Schema::create('category_statistics', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year')->nullable()->index();

            $table->decimal('jan', 13, 4)->default(0.00)->index();
            $table->decimal('feb', 13, 4)->default(0.00)->index();
            $table->decimal('mar', 13, 4)->default(0.00)->index();
            $table->decimal('apr', 13, 4)->default(0.00)->index();
            $table->decimal('may', 13, 4)->default(0.00)->index();
            $table->decimal('jun', 13, 4)->default(0.00)->index();
            $table->decimal('jul', 13, 4)->default(0.00)->index();
            $table->decimal('aug', 13, 4)->default(0.00)->index();
            $table->decimal('sep', 13, 4)->default(0.00)->index();
            $table->decimal('oct', 13, 4)->default(0.00)->index();
            $table->decimal('nov', 13, 4)->default(0.00)->index();
            $table->decimal('dec', 13, 4)->default(0.00)->index();

            $table->unsignedInteger('category_id')->index();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
