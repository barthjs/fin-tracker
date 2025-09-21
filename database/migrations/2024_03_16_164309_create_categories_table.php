<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
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
        Schema::create('categories', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('name')->index();
            $groups = array_column(CategoryGroup::cases(), 'value');
            $table->enum('group', $groups)->default(CategoryGroup::VarExpenses->value)->index();
            $types = array_column(TransactionType::cases(), 'value');
            $table->enum('type', $types)->default(TransactionType::Expense->value)->index();

            $table->string('color');
            $table->boolean('is_active')->default(true)->index();

            $table->foreignUlid('user_id')->constrained('sys_users')->cascadeOnDelete();

            $table->timestamps();
        });
    }
};
