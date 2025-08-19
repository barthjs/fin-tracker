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
            $table->timestamps();

            $table->string('name')->index();
            $table->string('isin')->nullable()->index();
            $table->string('symbol')->nullable()->index();
            $table->decimal('price', 18, 6)->default(0);
            $table->decimal('total_quantity', 18, 6)->default(0);
            $table->decimal('market_value', 18, 6)->default(0);
            $table->text('description')->nullable();

            $groups = array_column(SecurityType::cases(), 'name');
            $table->enum('type', $groups)->default(SecurityType::STOCK->name)->index();
            $table->string('logo')->nullable();
            $table->string('color');
            $table->boolean('active')->default(true)->index();

            $table->foreignUlid('user_id')->constrained('sys_users')->cascadeOnDelete();
        });
    }
};
