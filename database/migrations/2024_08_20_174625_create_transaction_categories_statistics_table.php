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
        Schema::create('transaction_categories_statistics', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year')->nullable()->index();

            $table->decimal('jan', 14, 4)->default(0.00)->index();
            $table->decimal('feb', 14, 4)->default(0.00)->index();
            $table->decimal('mar', 14, 4)->default(0.00)->index();
            $table->decimal('apr', 14, 4)->default(0.00)->index();
            $table->decimal('may', 14, 4)->default(0.00)->index();
            $table->decimal('jun', 14, 4)->default(0.00)->index();
            $table->decimal('jul', 14, 4)->default(0.00)->index();
            $table->decimal('aug', 14, 4)->default(0.00)->index();
            $table->decimal('sep', 14, 4)->default(0.00)->index();
            $table->decimal('oct', 14, 4)->default(0.00)->index();
            $table->decimal('nov', 14, 4)->default(0.00)->index();
            $table->decimal('dec', 14, 4)->default(0.00)->index();

            $table->unsignedInteger('category_id')->index();
            $table->foreign('category_id')->references('id')->on('transaction_categories')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
