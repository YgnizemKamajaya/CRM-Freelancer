<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('lead')->index();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_contacted_at')->nullable()->index();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'name']);
            $table->index(['user_id', 'email']);
        });

        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('value', 14, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->string('stage')->default('new_inquiry')->index();
            $table->unsignedTinyInteger('probability')->default(0);
            $table->string('source')->nullable();
            $table->date('expected_close_date')->nullable()->index();
            $table->timestamp('next_follow_up_at')->nullable()->index();
            $table->string('status')->default('open')->index();
            $table->text('lost_reason')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'client_id']);
            $table->index(['user_id', 'stage']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('planned')->index();
            $table->string('priority')->default('medium')->index();
            $table->decimal('budget', 14, 2)->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'client_id']);
            $table->index(['user_id', 'deal_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number');
            $table->date('issue_date');
            $table->date('due_date')->nullable()->index();
            $table->string('currency', 3)->default('IDR');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->string('status')->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'invoice_number']);
            $table->index(['user_id', 'client_id']);
            $table->index(['user_id', 'project_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('follow_up')->index();
            $table->string('status')->default('pending')->index();
            $table->string('priority')->default('medium')->index();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'client_id']);
            $table->index(['user_id', 'deal_id']);
            $table->index(['user_id', 'project_id']);
            $table->index(['user_id', 'invoice_id']);
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('general_note')->index();
            $table->text('body');
            $table->timestamp('activity_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'client_id']);
            $table->index(['user_id', 'deal_id']);
            $table->index(['user_id', 'project_id']);
            $table->index(['user_id', 'invoice_id']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name')->nullable();
            $table->string('business_email')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('business_whatsapp')->nullable();
            $table->string('business_website')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('default_currency', 3)->default('IDR');
            $table->string('invoice_prefix')->default('INV');
            $table->unsignedInteger('invoice_next_number')->default(1);
            $table->decimal('default_tax_percentage', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('clients');
    }
};
