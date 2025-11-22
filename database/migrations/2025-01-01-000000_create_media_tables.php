<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->string('color')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alt')->nullable();
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->string('disk')->default(config('mediamanager.default_disk'));
            $table->string('path');
            $table->string('mime_type')->nullable()->index();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('visibility')->default('public')->index();
            $table->string('random_hash')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('media_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('media_file_tag', function (Blueprint $table) {
            $table->foreignId('media_file_id')->constrained('media_files')->cascadeOnDelete();
            $table->foreignId('media_tag_id')->constrained('media_tags')->cascadeOnDelete();
            $table->primary(['media_file_id', 'media_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_file_tag');
        Schema::dropIfExists('media_tags');
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('media_folders');
    }
};
