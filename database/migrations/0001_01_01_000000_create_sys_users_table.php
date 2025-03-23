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
        Schema::create('sys_users', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->autoIncrement();
            $table->timestamps();

            $table->string('first_name')->nullable()->index();
            $table->string('last_name')->nullable()->index();
            $table->string('name')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->boolean('verified')->default(false)->index();
            $table->boolean('is_admin')->default(false)->index();
            $table->boolean('active')->default(true)->index();
            $table->rememberToken();
        });

        Schema::create('sys_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_users');
    }
};
