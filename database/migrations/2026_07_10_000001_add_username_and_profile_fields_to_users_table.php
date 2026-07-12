<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('provider_avatar');
            }
            if (!Schema::hasColumn('users', 'school')) {
                $table->string('school')->nullable()->after('profile_picture');
            }
            if (!Schema::hasColumn('users', 'course')) {
                $table->string('course')->nullable()->after('school');
            }
            if (!Schema::hasColumn('users', 'year_level')) {
                $table->string('year_level')->nullable()->after('course');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'year_level')) {
                $table->dropColumn('year_level');
            }
            if (Schema::hasColumn('users', 'course')) {
                $table->dropColumn('course');
            }
            if (Schema::hasColumn('users', 'school')) {
                $table->dropColumn('school');
            }
            if (Schema::hasColumn('users', 'profile_picture')) {
                $table->dropColumn('profile_picture');
            }
            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            }
        });
    }
};
