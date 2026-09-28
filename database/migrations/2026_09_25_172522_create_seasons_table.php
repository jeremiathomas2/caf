<?php

use App\Enums\SeasonState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('number')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('theme')->nullable();
            $table->string('tagline')->nullable();
            $table->string('state')->default(SeasonState::Draft->value);
            $table->boolean('is_current')->default(false);

            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamp('registration_opens_at')->nullable();
            $table->timestamp('registration_closes_at')->nullable();
            $table->timestamp('early_bird_closes_at')->nullable();

            $table->char('currency', 3)->default('TZS');
            $table->char('secondary_currency', 3)->default('USD');
            $table->decimal('per_head_fee', 14, 2)->default(0);
            $table->decimal('early_bird_fee', 14, 2)->nullable();
            $table->unsignedTinyInteger('min_partial_payment_pct')->default(50);
            $table->unsignedInteger('rounding_increment')->default(100);
            $table->decimal('usd_fx_rate', 12, 4)->nullable();

            $table->text('summary')->nullable();
            $table->timestamps();

            $table->index('state');
            $table->index('is_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
