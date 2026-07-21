<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('cash')->index();
            $table->string('currency', 3)->default('IDR');
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->boolean('is_archived')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('category')->nullable()->index();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('IDR');
            $table->text('note')->nullable();
            $table->date('occurred_on')->index();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['wallet_id', 'occurred_on']);
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->decimal('monthly_limit', 14, 2);
            $table->string('currency', 3)->default('IDR');
            $table->timestamps();

            $table->unique('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('wallets');
    }
};
