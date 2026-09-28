<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('auditor')->after('email');
            $table->string('phone', 30)->nullable()->after('role');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('avatar_path');
            $table->string('two_factor_secret', 255)->nullable()->after('last_login_at');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn([
                'role', 'phone', 'avatar_path', 'last_login_at',
                'two_factor_secret', 'two_factor_confirmed_at',
            ]);
        });
    }
};
