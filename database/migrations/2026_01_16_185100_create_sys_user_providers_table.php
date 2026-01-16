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
        Schema::create('sys_user_providers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();

            $table->foreignUlid('user_id')
                ->index()
                ->constrained('sys_users')
                ->cascadeOnDelete();
            $table->string('provider_name');
            $table->string('provider_id');

            $table->unique(['provider_name', 'provider_id']);
        });
    }
};
