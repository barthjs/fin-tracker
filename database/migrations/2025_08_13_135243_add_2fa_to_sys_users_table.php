<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sys_users', function (Blueprint $table) {
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        });

        $oldSecrets = DB::table('breezy_sessions')
            ->get(['authenticatable_id', 'two_factor_secret', 'two_factor_recovery_codes']);

        foreach ($oldSecrets as $oldSecret) {
            $app_authentication_secret = unserialize(Crypt::decryptString($oldSecret->two_factor_secret));

            $two_factor_recovery_codes = array_map(
                fn (string $code): string => Hash::make($code),
                json_decode(unserialize(Crypt::decryptString($oldSecret->two_factor_recovery_codes)), true)
            );
            $two_factor_recovery_codes = json_encode($two_factor_recovery_codes, JSON_UNESCAPED_UNICODE);

            DB::table('sys_users')
                ->where('id', '=', $oldSecret->authenticatable_id)
                ->update([
                    'app_authentication_secret' => Crypt::encryptString($app_authentication_secret),
                    'app_authentication_recovery_codes' => Crypt::encryptString($two_factor_recovery_codes),
                ]);
        }

        Schema::dropIfExists('breezy_sessions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
