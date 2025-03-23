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
            $table->id();
            $table->smallInteger('year')->nullable()->index();

            $table->bigInteger('jan')->default(0)->index();
            $table->bigInteger('feb')->default(0)->index();
            $table->bigInteger('mar')->default(0)->index();
            $table->bigInteger('apr')->default(0)->index();
            $table->bigInteger('may')->default(0)->index();
            $table->bigInteger('jun')->default(0)->index();
            $table->bigInteger('jul')->default(0)->index();
            $table->bigInteger('aug')->default(0)->index();
            $table->bigInteger('sep')->default(0)->index();
            $table->bigInteger('oct')->default(0)->index();
            $table->bigInteger('nov')->default(0)->index();
            $table->bigInteger('dec')->default(0)->index();

            $table->unsignedInteger('category_id')->index();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_statistics');
    }
};
