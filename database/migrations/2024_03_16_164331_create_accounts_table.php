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

            $table->string('name')->index();
            $table->decimal('balance', 18)->default(0);
            $table->char('currency', 3)->default(Currency::EUR->value)->index();
            $table->text('description')->nullable();

            $table->string('logo')->nullable();
            $table->string('color');
            $table->boolean('is_active')->default(true)->index();

            $table->foreignUlid('user_id')->constrained('sys_users')->cascadeOnDelete();

            $table->timestamps();
        });
    }
};
