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
            $table->unsignedInteger('transaction_id')->autoIncrement();

            $table->date('date');
            $table->decimal('amount');
            $table->string('destination')->nullable()->index();
            $table->text('notes')->nullable()->index();

            $table->unsignedSmallInteger('bank_account_id')->nullable()->index();
            $table->foreign('bank_account_id')->references('bank_account_id')->on('bank_accounts');

            $table->unsignedInteger('category_id')->nullable()->index();
            $table->foreign('category_id')->references('category_id')->on('transaction_categories');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_account_transactions');
    }
};
