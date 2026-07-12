<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('email_otps', function (Blueprint $table) {
            if (!Schema::hasColumn('email_otps', 'name')) {
                $table->string('name')->nullable()->after('email');
            }
        });
    }

    public function down()
    {
        Schema::table('email_otps', function (Blueprint $table) {
            if (Schema::hasColumn('email_otps', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
