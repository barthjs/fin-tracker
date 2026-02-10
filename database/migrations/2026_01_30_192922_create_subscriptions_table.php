<?php

declare(strict_types=1);

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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();

            $table->foreignUlid('account_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUlid('category_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->decimal('amount', 18)->default(0)->index();

            $table->string('period_unit')->index();
            $table->unsignedTinyInteger('period_frequency')->default(1);
            $table->unsignedTinyInteger('day_of_month');

            $table->date('started_at');
            $table->date('next_payment_date')->index();
            $table->date('ended_at')->nullable();

            $table->boolean('auto_generate_transaction')->default(true);
            $table->timestamp('last_generated_at')->nullable()->index();

            $table->boolean('remind_before_payment')->default(false);
            $table->unsignedTinyInteger('reminder_days_before')->default(3);
            $table->timestamp('last_reminded_at')->nullable()->index();

            $table->string('logo')->nullable();
            $table->string('color');
            $table->boolean('is_active')->default(true);

            $table->index(['account_id', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['is_active', 'auto_generate_transaction', 'next_payment_date'], 'sub_scheduler_index');
            $table->index(['is_active', 'remind_before_payment', 'next_payment_date'], 'sub_reminder_index');
        });
    }
};
