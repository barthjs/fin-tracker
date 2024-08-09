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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->autoIncrement();
            $table->timestamps();

            $table->string('name')->index();
            $table->decimal('balance')->nullable();

            $table->unsignedTinyInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('sys_users')->cascadeOnDelete()->cascadeOnUpdate();;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
