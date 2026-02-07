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
        Schema::create('notification_assignments', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('notification_target_id')
                ->index()
                ->constrained('notification_targets')
                ->cascadeOnDelete();

            $table->ulid('notifiable_id');
            $table->string('notifiable_type');

            $table->string('event_type')->comment('Enum value of \App\Enums\NotificationEventType');

            $table->index(['notifiable_type', 'notifiable_id', 'event_type'], 'notifiable_event_index');
        });
    }
};
