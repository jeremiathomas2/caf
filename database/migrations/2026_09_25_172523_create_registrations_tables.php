<?php

use App\Enums\PaymentStatus;
use App\Enums\RegistrationRole;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32)->unique();

            $table->string('group_name');
            $table->string('category');
            $table->string('role_type')->default(RegistrationRole::Singers->value);
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->unsignedSmallInteger('members_count')->default(1);

            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone', 30)->nullable();

            $table->string('performance_link')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->default(RegistrationStatus::Submitted->value);
            $table->string('payment_status')->default(PaymentStatus::Unpaid->value);
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('source')->default('web');
            $table->json('tags')->nullable();

            $table->decimal('score_total', 6, 2)->nullable();
            $table->unsignedSmallInteger('score_votes')->default(0);

            $table->boolean('is_public')->default(false);
            $table->text('bio')->nullable();
            $table->string('image_url')->nullable();
            $table->string('art_gradient')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['season_id', 'status']);
            $table->index(['season_id', 'payment_status']);
            $table->index('group_name');
            $table->index('contact_email');
        });

        Schema::create('registration_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('part')->nullable();
            $table->boolean('is_lead')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['registration_id', 'sort_order']);
        });

        Schema::create('registration_status_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_label')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['registration_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_status_events');
        Schema::dropIfExists('registration_members');
        Schema::dropIfExists('registrations');
    }
};
