<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table): void {
            $table->boolean('notify_on_low_balance')->default(false);
            $table->decimal('low_balance_threshold', 18)->default(0);
            $table->timestamp('low_balance_notified_at')->nullable();
        });
    }
};
