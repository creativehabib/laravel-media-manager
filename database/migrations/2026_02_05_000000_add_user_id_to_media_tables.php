<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('media_folders', function (Blueprint $table) {
            if (! Schema::hasColumn('media_folders', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index()->after('name');
            }
        });

        Schema::table('media_files', function (Blueprint $table) {
            if (! Schema::hasColumn('media_files', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('media_folders', function (Blueprint $table) {
            if (Schema::hasColumn('media_folders', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });

        Schema::table('media_files', function (Blueprint $table) {
            if (Schema::hasColumn('media_files', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
