<?php

declare(strict_types=1);

use App\Enums\TradeType;
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
        Schema::create('trades', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->dateTime('date_time')->index();

            $table->bigInteger('total_amount')->default(0);
            $table->decimal('quantity', 18, 6)->default(0);
            $table->decimal('price', 18, 6)->default(0);
            $table->decimal('tax', 13)->default(0);
            $table->decimal('fee', 13)->default(0);
            $types = array_column(TradeType::cases(), 'name');
            $table->enum('type', $types)->index();
            $table->string('notes')->nullable();

            $table->foreignUlid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('portfolio_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('security_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('sys_users')->cascadeOnDelete();
        });
    }
};
