<?php

declare(strict_types=1);

use App\Enums\TransactionGroup;
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
            $table->unsignedInteger('id')->autoIncrement();
            $table->timestamps();

            $table->string('name')->index();

            $groups = array_column(TransactionGroup::cases(), 'name');
            $table->enum('group', $groups)->default(TransactionGroup::transfers->name)->index();
            $types = array_column(TransactionType::cases(), 'name');
            $table->enum('type', $types)->default(TransactionType::transfer->name)->index();

            $table->string('color');
            $table->boolean('active')->default(true)->index();

            $table->unsignedTinyInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('sys_users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
