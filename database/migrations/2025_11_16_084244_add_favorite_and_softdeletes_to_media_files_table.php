<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            if (!Schema::hasColumn('media_files', 'is_favorite')) {
                $table->boolean('is_favorite')->default(false)->after('visibility');
            }

            if (!Schema::hasColumn('media_files', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            if (Schema::hasColumn('media_files', 'is_favorite')) {
                $table->dropColumn('is_favorite');
            }

            if (Schema::hasColumn('media_files', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
