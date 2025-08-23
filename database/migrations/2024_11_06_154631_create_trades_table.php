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

            $table->dateTime('trade_date')->index();
            $types = array_column(TradeType::cases(), 'value');
            $table->enum('type', $types)->default(TradeType::Buy)->index();
            $table->decimal('total_amount', 18, 6)
                ->storedAs("
                    CASE
                        WHEN type = 'buy' THEN (price * quantity + tax + fee)
                        WHEN type = 'sell' THEN (price * quantity - (tax + fee))
                        ELSE 0
                    END
                ");
            $table->decimal('quantity', 18, 6)->default(0);
            $table->decimal('price', 18, 6)->default(0);
            $table->decimal('tax', 18, 6)->default(0);
            $table->decimal('fee', 18, 6)->default(0);
            $table->string('notes')->nullable();

            $table->foreignUlid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('portfolio_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('security_id')->constrained()->cascadeOnDelete();

            $table->index(['account_id', 'type']);
            $table->index(['portfolio_id', 'type']);
            $table->index(['security_id', 'type']);

            $table->index(['account_id', 'trade_date']);
            $table->index(['portfolio_id', 'trade_date']);
            $table->index(['security_id', 'trade_date']);
        });
    }
};
