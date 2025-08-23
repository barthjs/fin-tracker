<?php

declare(strict_types=1);

use App\Enums\SecurityType;
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
        Schema::create('securities', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('name')->index();
            $table->string('isin')->nullable()->index();
            $table->string('symbol')->nullable()->index();
            $types = array_column(SecurityType::cases(), 'value');
            $table->enum('type', $types)->default(SecurityType::Stock->value)->index();
            $table->decimal('price', 18, 6)->default(0);
            $table->decimal('total_quantity', 18, 6)->default(0);
            $table->decimal('market_value', 18, 6)->storedAs('price * total_quantity');
            $table->text('description')->nullable();

            $table->string('logo')->nullable();
            $table->string('color');
            $table->boolean('is_active')->default(true)->index();

            $table->foreignUlid('user_id')->constrained('sys_users')->cascadeOnDelete();

            $table->timestamps();
        });
    }
};
