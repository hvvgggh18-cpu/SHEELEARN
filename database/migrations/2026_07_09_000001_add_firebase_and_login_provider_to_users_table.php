<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'firebase_uid')) {
                $table->string('firebase_uid')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('users', 'login_provider')) {
                $table->string('login_provider')->nullable()->after('provider_name')->index();
            }
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'firebase_uid')) {
                $table->dropColumn('firebase_uid');
            }
            if (Schema::hasColumn('users', 'login_provider')) {
                $table->dropColumn('login_provider');
            }
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }
};
