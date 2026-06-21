<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('securities', function (Blueprint $table): void {
            $table->string('region')->nullable()->index();
            $table->string('sector')->nullable()->index();
            $table->string('country', 2)->nullable()->index();
            $table->string('exchange')->nullable();
            $table->decimal('ter', 5, 3)->nullable();
            $table->string('website')->nullable();
        });
    }
};
