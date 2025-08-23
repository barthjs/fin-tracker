<?php

declare(strict_types=1);

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
        Schema::create('category_statistics', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('category_id')->constrained('categories')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->index(['category_id', 'year']);

            $table->decimal('jan', 18)->default(0)->index();
            $table->decimal('feb', 18)->default(0)->index();
            $table->decimal('mar', 18)->default(0)->index();
            $table->decimal('apr', 18)->default(0)->index();
            $table->decimal('may', 18)->default(0)->index();
            $table->decimal('jun', 18)->default(0)->index();
            $table->decimal('jul', 18)->default(0)->index();
            $table->decimal('aug', 18)->default(0)->index();
            $table->decimal('sep', 18)->default(0)->index();
            $table->decimal('oct', 18)->default(0)->index();
            $table->decimal('nov', 18)->default(0)->index();
            $table->decimal('dec', 18)->default(0)->index();
        });
    }
};
