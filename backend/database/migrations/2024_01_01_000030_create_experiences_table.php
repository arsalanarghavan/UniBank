<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('field_id')->nullable()->constrained('fields')->nullOnDelete();
            $table->foreignId('major_id')->nullable()->constrained('majors')->nullOnDelete();
            $table->foreignId('professor_id')->nullable()->constrained('professors')->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->text('teaching_style')->nullable();
            $table->text('notes')->nullable();
            $table->text('project')->nullable();
            $table->boolean('attendance_required')->nullable();
            $table->text('attendance_details')->nullable();
            $table->text('exam')->nullable();
            $table->text('conclusion')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->string('teaching_rating', 20)->nullable();
            $table->string('exam_difficulty', 20)->nullable();
            $table->unsignedTinyInteger('overall_rating')->nullable();
            $table->boolean('has_notes')->nullable();
            $table->boolean('has_project')->nullable();
            $table->boolean('has_exam')->nullable();
            $table->bigInteger('admin_message_id')->nullable();
            $table->bigInteger('admin_chat_id')->nullable();
            $table->bigInteger('channel_message_id')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
