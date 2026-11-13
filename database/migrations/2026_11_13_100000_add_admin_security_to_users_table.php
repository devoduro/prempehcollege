<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            // Encrypted by the model; text because ciphertext is longer than the secret.
            $t->text('app_authentication_secret')->nullable()->after('password');
            $t->text('app_authentication_recovery_codes')->nullable()->after('app_authentication_secret');
            $t->boolean('is_active')->default(true)->after('app_authentication_recovery_codes');
            $t->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['app_authentication_secret', 'app_authentication_recovery_codes', 'is_active', 'last_login_at']);
        });
    }
};
