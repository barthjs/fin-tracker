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
        Schema::create('transactions', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->dateTime('date_time')->index();

            $table->decimal('amount', 13, 4)->default(0.00)->index();
            $table->string('destination')->nullable()->index();
            $table->string('notes')->nullable();

            $table->unsignedSmallInteger('account_id')->nullable()->index();
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedInteger('category_id')->nullable()->index();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedTinyInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('sys_users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
