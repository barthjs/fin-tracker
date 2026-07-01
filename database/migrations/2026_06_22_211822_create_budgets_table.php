<?php

declare(strict_types=1);

use App\Enums\PeriodUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->timestamps();

            $table->foreignUlid('category_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('amount', 18)->default(0);

            $table->string('period_unit')->default(PeriodUnit::Month->value)->index();
            $table->unsignedTinyInteger('period_frequency')->default(1);

            $table->date('starts_at');
            $table->date('ends_at')->nullable();

            $table->unsignedTinyInteger('warning_threshold')->nullable();

            $table->boolean('notify_on_exceed')->default(false);
            $table->timestamp('last_warned_at')->nullable();
            $table->timestamp('last_exceeded_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->index(['category_id', 'is_active']);
            $table->index(['is_active', 'notify_on_exceed'], 'budget_notify_index');
        });
    }
};
