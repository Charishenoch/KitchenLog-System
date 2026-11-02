<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make password nullable so Google OAuth users (who have no password) can be stored.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password', 255)->nullable(false)->change();
        });
    }
};
