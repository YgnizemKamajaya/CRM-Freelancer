<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->string('banner_gradient')->nullable()->after('metric_label');
            $table->string('banner_path')->nullable()->after('banner_gradient');
            $table->boolean('is_pinned')->default(false)->index()->after('is_archived');
        });
    }

    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn(['banner_gradient', 'banner_path', 'is_pinned']);
        });
    }
};
