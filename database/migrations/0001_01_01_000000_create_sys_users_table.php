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
        Schema::create('sys_users', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->autoIncrement();
            $table->timestamps();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->nullable()->index();
            $table->rememberToken();
        });

        Schema::create('sys_password_reset_tokens', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable();

            $table->string('email')->primary();
            $table->string('token');
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
    }
};