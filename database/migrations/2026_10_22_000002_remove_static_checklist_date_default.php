<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_items', fn (Blueprint $table) => $table->date('for_date')->default(null)->change());
    }

    public function down(): void
    {
        // Original literal is deployment-specific. Rollback reinstates a literal for that day.
        Schema::table('checklist_items', fn (Blueprint $table) => $table->date('for_date')->default(now()->toDateString())->change());
    }
};
