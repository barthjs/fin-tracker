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
        Schema::create('notification_targets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();

            $table->foreignUlid('user_id')
                ->index()
                ->constrained('sys_users')
                ->cascadeOnDelete();

            $table->string('name')->index();
            $table->string('type')->index()->comment('Enum value of \App\Enums\NotificationTargetType');
            $table->text('configuration')->comment('Encrypted JSON');

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->index(['user_id', 'is_active']);
        });
    }
};
