<?php

use App\Enums\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('number', 32)->unique();

            $table->char('currency', 3)->default('TZS');
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->decimal('amount_waived', 14, 2)->default(0);
            $table->string('status')->default(InvoiceStatus::Issued->value);
            $table->string('method')->nullable();

            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['season_id', 'status']);
            $table->index(['registration_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 64);
            $table->string('method');
            $table->char('currency', 3)->default('TZS');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('status')->default('success');
            $table->timestamp('paid_at');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['registration_id', 'paid_at']);
            $table->index('reference');
        });

        Schema::create('invoice_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_adjustments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
    }
};
