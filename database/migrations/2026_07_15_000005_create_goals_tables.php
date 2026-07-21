<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('personal')->index();
            $table->string('metric_label')->nullable();
            $table->decimal('target_amount', 14, 2);
            $table->decimal('current_amount', 14, 2)->default(0);
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('auto_track')->default(false);
            $table->date('due_date')->nullable();
            $table->boolean('is_archived')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
