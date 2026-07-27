<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('university_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_category_id')->constrained('university_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained('universities')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['university_id', 'name']);
        });

        Schema::create('degree_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('fields', function (Blueprint $table) {
            $table->foreignId('faculty_id')->nullable()->after('id')->constrained('faculties')->nullOnDelete();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('degree_level_id')->nullable()->after('major_id')->constrained('degree_levels')->nullOnDelete();
        });

        Schema::table('professors', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('name');
        });

        Schema::create('faculty_professor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->foreignId('professor_id')->constrained('professors')->cascadeOnDelete();
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
            $table->unique(['faculty_id', 'professor_id']);
        });

        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_id')->constrained('professors')->cascadeOnDelete();
            $table->foreignId('university_id')->constrained('universities')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['professor_id', 'university_id', 'course_id'], 'teaching_assign_unique');
        });

        Schema::create('professor_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_id')->constrained('professors')->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title')->nullable();
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('experiences', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->after('user_id')->constrained('universities')->nullOnDelete();
            $table->foreignId('faculty_id')->nullable()->after('university_id')->constrained('faculties')->nullOnDelete();
            $table->foreignId('degree_level_id')->nullable()->after('course_id')->constrained('degree_levels')->nullOnDelete();
            $table->string('teaching_type', 30)->nullable()->after('teaching_style');
            $table->json('contact_methods')->nullable()->after('conclusion');
        });

        Schema::create('experience_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')->constrained('experiences')->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('bots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained('universities')->cascadeOnDelete();
            $table->string('platform', 20);
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->text('token')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->json('ui_layout')->nullable();
            $table->timestamps();
            $table->unique(['university_id', 'platform']);
        });

        Schema::create('bot_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_id')->constrained('bots')->cascadeOnDelete();
            $table->string('type', 30)->default('publish');
            $table->string('channel_id');
            $table->string('channel_link')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('bot_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_id')->constrained('bots')->cascadeOnDelete();
            $table->string('key');
            $table->text('value');
            $table->timestamps();
            $table->unique(['bot_id', 'key']);
        });

        Schema::table('bot_texts', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->foreignId('bot_id')->nullable()->after('id')->constrained('bots')->cascadeOnDelete();
            $table->string('locale', 5)->default('fa')->after('key');
            $table->unique(['bot_id', 'key', 'locale']);
        });

        Schema::table('required_channels', function (Blueprint $table) {
            $table->dropUnique(['channel_id']);
            $table->foreignId('bot_id')->nullable()->after('id')->constrained('bots')->cascadeOnDelete();
            $table->string('platform', 20)->nullable()->after('bot_id');
            $table->unique(['bot_id', 'channel_id']);
        });

        Schema::table('fields', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['faculty_id', 'name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('bale_id')->nullable()->unique()->after('telegram_id');
            $table->foreignId('signup_university_id')->nullable()->after('locale')->constrained('universities')->nullOnDelete();
            $table->string('signup_platform', 20)->nullable()->after('signup_university_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signup_university_id');
            $table->dropColumn(['bale_id', 'signup_platform']);
        });
        Schema::table('required_channels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bot_id');
            $table->dropColumn('platform');
        });
        Schema::table('bot_texts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bot_id');
            $table->dropColumn('locale');
        });
        Schema::dropIfExists('bot_settings');
        Schema::dropIfExists('bot_channels');
        Schema::dropIfExists('bots');
        Schema::dropIfExists('experience_attachments');
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('university_id');
            $table->dropConstrainedForeignId('faculty_id');
            $table->dropConstrainedForeignId('degree_level_id');
            $table->dropColumn(['teaching_type', 'contact_methods']);
        });
        Schema::dropIfExists('professor_links');
        Schema::dropIfExists('teaching_assignments');
        Schema::dropIfExists('faculty_professor');
        Schema::table('professors', function (Blueprint $table) {
            $table->dropColumn('bio');
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('degree_level_id');
        });
        Schema::table('fields', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faculty_id');
        });
        Schema::dropIfExists('degree_levels');
        Schema::dropIfExists('faculties');
        Schema::dropIfExists('universities');
        Schema::dropIfExists('university_categories');
    }
};
