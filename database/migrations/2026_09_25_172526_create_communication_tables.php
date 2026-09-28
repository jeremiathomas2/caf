<?php

use App\Enums\CampaignStatus;
use App\Enums\ThreadStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('subject');
            $table->string('channel');
            $table->string('status')->default(ThreadStatus::Open->value);
            $table->string('contact_name');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();

            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('unread_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['season_id', 'status']);
            $table->index('last_message_at');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_type')->default('contact');
            $table->string('sender_label');
            $table->text('body');
            $table->string('channel');
            $table->timestamp('sent_at');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['message_thread_id', 'sent_at']);
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('channel');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->json('segment')->nullable();
            $table->string('status')->default(CampaignStatus::Draft->value);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->decimal('cost', 12, 2)->default(0);
            $table->char('currency', 3)->default('TZS');
            $table->timestamps();

            $table->index(['season_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('message_threads');
    }
};
