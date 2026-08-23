<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('badge_templates')) return;
        Schema::create('badge_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('scope_unit')->nullable();
            $table->string('orientation', 20)->default('portrait');
            $table->decimal('width_mm', 6, 2)->default(54.00);
            $table->decimal('height_mm', 6, 2)->default(86.00);
            $table->longText('front_json');
            $table->longText('back_json')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_templates');
    }
};
