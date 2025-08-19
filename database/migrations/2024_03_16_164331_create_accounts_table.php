<?php

declare(strict_types=1);

use App\Enums\Currency;
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
        Schema::create('accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();

            $table->string('name')->index();
            $table->bigInteger('balance')->default(0)->index();
            $currencies = array_column(Currency::cases(), 'value');
            $table->enum('currency', $currencies)->default(Currency::USD->name)->index();
            $table->text('description')->nullable();

            $table->string('logo')->nullable();
            $table->string('color');
            $table->boolean('active')->default(true)->index();

            $table->foreignUlid('user_id')->constrained('sys_users')->cascadeOnDelete();
        });
    }
};
