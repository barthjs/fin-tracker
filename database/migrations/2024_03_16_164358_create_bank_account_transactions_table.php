<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_account_transactions', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->dateTime('date_time')->index();

            $table->decimal('amount', 14, 4)->default(0.00)->index();
            $table->string('destination')->nullable()->index();
            $table->string('notes')->nullable();

            $table->unsignedSmallInteger('bank_account_id')->nullable()->index();
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedInteger('category_id')->nullable()->index();
            $table->foreign('category_id')->references('id')->on('transaction_categories')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
