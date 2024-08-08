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
        Schema::create('sys_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('sys_failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('failed_at')->useCurrent();
            $table->text('queue');

            $table->string('uuid')->unique();
            $table->text('connection');
            $table->longText('payload');
            $table->longText('exception');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
