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
        Schema::create('securities', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->timestamps();

            $table->string('name')->index();
            $table->string('isin')->nullable();
            $table->string('symbol')->nullable();
            $table->decimal('price', 18, 6)->default(0);
            $table->decimal('total_quantity', 18, 6)->default(0);
            $table->text('description')->nullable();

            $table->string('logo')->nullable();
            $table->string('color');
            $table->boolean('active')->default(true)->index();

            $table->unsignedInteger('type_id')->nullable()->index();
            $table->foreign('type_id')->references('id')->on('security_types')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedTinyInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('sys_users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('securities');
    }
};
