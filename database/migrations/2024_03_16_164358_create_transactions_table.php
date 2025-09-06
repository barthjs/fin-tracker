<?php

declare(strict_types=1);

use App\Enums\TransactionType;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->dateTime('date_time')->index();
            $types = array_column(TransactionType::cases(), 'value');
            $table->enum('type', $types)->default(TransactionType::Expense->value)->index();
            $table->decimal('amount', 18)->default(0);
            $table->string('payee')->nullable()->index();
            $table->string('notes')->nullable();

            $table->foreignUlid('account_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('transfer_account_id')->nullable()->constrained('accounts')->cascadeOnDelete();
            $table->foreignUlid('category_id')->nullable()->constrained()->cascadeOnDelete();

            $table->index(['account_id', 'type']);

            $table->index(['account_id', 'date_time']);
            $table->index(['category_id', 'date_time']);
        });
    }
};
