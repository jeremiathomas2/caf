<?php

use App\Enums\ContentType;
use App\Enums\PublishStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('type')->default(ContentType::Page->value);
            $table->string('status')->default(PublishStatus::Draft->value);
            $table->longText('body')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('hero_image_url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->boolean('is_in_footer')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('programme_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->date('event_date');
            $table->string('stage');
            $table->string('stage_location')->nullable();
            $table->time('starts_at');
            $table->time('ends_at')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('confirmed');
            $table->boolean('is_public')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['season_id', 'event_date', 'stage']);
        });

        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('tier');
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_public')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['season_id', 'sort_order']);
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('author_name');
            $table->string('author_role')->nullable();
            $table->text('quote');
            $table->string('avatar_url')->nullable();
            $table->boolean('is_public')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['season_id', 'sort_order']);
        });

        Schema::create('faq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->boolean('is_public')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['season_id', 'sort_order']);
        });

        Schema::create('terms_clauses', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('position');
            $table->string('title');
            $table->text('body');
            $table->timestamps();

            $table->unique('position');
        });

        Schema::create('site_values', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        Schema::create('site_objectives', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        Schema::create('site_activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        Schema::create('impact_stats', function (Blueprint $table) {
            $table->id();
            $table->string('figure');
            $table->string('label');
            $table->text('body')->nullable();
            $table->string('kind')->default('season');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_details')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_public')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role_title');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_url')->nullable();
            $table->boolean('is_public')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        Schema::create('gallery_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('image_url');
            $table->string('alt_text')->nullable();
            $table->boolean('is_public')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['season_id', 'sort_order']);
        });

        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('route_name');
            $table->string('url')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_items');
        Schema::dropIfExists('gallery_slides');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('impact_stats');
        Schema::dropIfExists('site_activities');
        Schema::dropIfExists('site_objectives');
        Schema::dropIfExists('site_values');
        Schema::dropIfExists('terms_clauses');
        Schema::dropIfExists('faq_items');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('sponsors');
        Schema::dropIfExists('programme_slots');
        Schema::dropIfExists('content_pages');
    }
};
