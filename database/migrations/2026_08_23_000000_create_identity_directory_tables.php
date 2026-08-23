<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('identity_directory_users')) Schema::create('identity_directory_users', function(Blueprint $t){$t->id();$t->string('directory_key',128)->unique();$t->string('username')->index();$t->string('user_principal_name')->nullable()->index();$t->string('display_name')->nullable();$t->string('email')->nullable()->index();$t->string('employee_id')->nullable()->index();$t->string('department')->nullable()->index();$t->string('title')->nullable();$t->string('phone')->nullable();$t->string('mobile')->nullable();$t->text('manager_dn')->nullable();$t->text('distinguished_name')->nullable();$t->longText('groups_json')->nullable();$t->string('profile')->default('Operador');$t->boolean('is_active')->default(true)->index();$t->timestamp('synced_at')->nullable();$t->timestamps();});
        if (!Schema::hasTable('identity_directory_groups')) Schema::create('identity_directory_groups', function(Blueprint $t){$t->id();$t->string('directory_key',128)->unique();$t->string('name')->index();$t->text('distinguished_name')->nullable();$t->text('description')->nullable();$t->timestamp('synced_at')->nullable();$t->timestamps();});
        if (Schema::hasTable('usuarios_admin')) Schema::table('usuarios_admin', function(Blueprint $t){if(!Schema::hasColumn('usuarios_admin','identity_source'))$t->string('identity_source',20)->default('local');if(!Schema::hasColumn('usuarios_admin','ldap_directory_key'))$t->string('ldap_directory_key',128)->nullable()->index();if(!Schema::hasColumn('usuarios_admin','ldap_dn'))$t->text('ldap_dn')->nullable();if(!Schema::hasColumn('usuarios_admin','ativo'))$t->boolean('ativo')->default(true)->index();if(!Schema::hasColumn('usuarios_admin','ldap_synced_at'))$t->timestamp('ldap_synced_at')->nullable();});
    }
    public function down(): void { Schema::dropIfExists('identity_directory_groups'); Schema::dropIfExists('identity_directory_users'); }
};
