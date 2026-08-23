<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('identity_directory_settings')) Schema::create('identity_directory_settings', function(Blueprint $t){$t->id();$t->string('setting_key')->unique();$t->longText('setting_value')->nullable();$t->boolean('is_encrypted')->default(false);$t->string('updated_by')->nullable();$t->timestamps();});
    }
    public function down(): void { Schema::dropIfExists('identity_directory_settings'); }
};
