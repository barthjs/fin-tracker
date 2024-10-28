<?php

use App\Enums\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->autoIncrement();
            $table->timestamps();

            $table->string('name')->index();
            $table->decimal('balance', 13, 4)->default(0.00)->index();
            $currencies = array_column(Currency::cases(), 'value');
            $table->enum('currency', $currencies)->default(Currency::USD->name)->index();
            $table->text('description')->nullable();

            $table->boolean('active')->default(true)->index();

            $table->unsignedTinyInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('sys_users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
