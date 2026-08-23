<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('identity_directory_settings')) {
            Schema::create('identity_directory_settings', function (Blueprint $table) {
                $table->id();
                $table->string('setting_key', 120)->unique();
                $table->text('setting_value')->nullable();
                $table->boolean('is_encrypted')->default(false);
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_directory_settings');
    }
};
