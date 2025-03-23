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
            $table->unsignedInteger('id')->autoIncrement();
            $table->dateTime('date_time')->index();

            $table->bigInteger('total_amount')->default(0);
            $table->decimal('quantity', 18, 6)->default(0);
            $table->decimal('price', 18, 6)->default(0);
            $table->decimal('tax', 13)->default(0);
            $table->decimal('fee', 13)->default(0);
            $types = array_column(TradeType::cases(), 'name');
            $table->enum('type', $types)->index();
            $table->string('notes')->nullable();

            $table->unsignedSmallInteger('account_id')->index();
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedSmallInteger('portfolio_id')->index();
            $table->foreign('portfolio_id')->references('id')->on('portfolios')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedInteger('security_id')->index();
            $table->foreign('security_id')->references('id')->on('securities')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedTinyInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('sys_users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
