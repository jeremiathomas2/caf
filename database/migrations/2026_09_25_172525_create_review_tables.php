<?php

use App\Enums\AssignmentStatus;
use App\Enums\RoundStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->string('status')->default(RoundStatus::Upcoming->value);
            $table->boolean('is_blind')->default(true);
            $table->string('aggregation')->default('Trimmed mean (drop high & low)');
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['season_id', 'sequence']);
        });

        Schema::create('rubric_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('max_points');
            $table->unsignedTinyInteger('weight');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['season_id', 'sort_order']);
        });

        Schema::create('judges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('role_title')->default('Judge');
            $table->string('bio')->nullable();
            $table->string('photo_url')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['season_id', 'is_active']);
        });

        Schema::create('review_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('judge_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(AssignmentStatus::Pending->value);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['review_round_id', 'registration_id', 'judge_id'], 'review_assignments_unique');
            $table->index(['judge_id', 'status']);
        });

        Schema::create('review_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rubric_criterion_id')->constrained()->cascadeOnDelete();
            $table->decimal('points', 6, 2);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['review_assignment_id', 'rubric_criterion_id'], 'review_scores_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_scores');
        Schema::dropIfExists('review_assignments');
        Schema::dropIfExists('judges');
        Schema::dropIfExists('rubric_criteria');
        Schema::dropIfExists('review_rounds');
    }
};
