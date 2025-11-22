<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            if (!Schema::hasColumn('media_files', 'width')) {
                $table->integer('width')->nullable()->after('mime_type');
            }
            if (!Schema::hasColumn('media_files', 'height')) {
                $table->integer('height')->nullable()->after('width');
            }
        });
    }

    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            if (Schema::hasColumn('media_files', 'height')) {
                $table->dropColumn('height');
            }
            if (Schema::hasColumn('media_files', 'width')) {
                $table->dropColumn('width');
            }
        });
    }
};
