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
            $table->ulid('id')->primary();
            $table->timestamps();

            $table->string('first_name')->nullable()->index();
            $table->string('last_name')->nullable()->index();
            $table->string('full_name')->storedAs("
                NULLIF(
                    COALESCE(first_name, '') ||
                    CASE
                        WHEN first_name IS NOT NULL AND last_name IS NOT NULL
                        THEN ' '
                        ELSE ''
                    END ||
                    COALESCE(last_name, ''),
                '')
            ")->nullable()->index();
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->string('locale')->default('en');

            $table->string('password');
            $table->rememberToken();
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();

            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_verified')->default(false)->index();
            $table->boolean('is_admin')->default(false);
        });

        Schema::create('sys_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUlid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }
};
