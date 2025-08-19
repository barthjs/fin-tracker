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
        Schema::create('transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->dateTime('date_time')->index();

            $table->bigInteger('amount')->default(0);
            $table->string('destination')->nullable()->index();
            $table->string('notes')->nullable();

            $table->foreignUlid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('category_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('sys_users')->cascadeOnDelete();
        });
    }
};
