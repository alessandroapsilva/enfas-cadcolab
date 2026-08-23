<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('identity_directory_users')) {
            Schema::create('identity_directory_users', function (Blueprint $table) {
                $table->id();
                $table->string('directory_key', 128)->unique();
                $table->string('username')->index();
                $table->string('user_principal_name')->nullable()->index();
                $table->string('display_name')->nullable();
                $table->string('email')->nullable()->index();
                $table->string('employee_id')->nullable()->index();
                $table->string('department')->nullable()->index();
                $table->string('title')->nullable();
                $table->string('phone')->nullable();
                $table->string('mobile')->nullable();
                $table->text('manager_dn')->nullable();
                $table->text('distinguished_name')->nullable();
                $table->longText('groups_json')->nullable();
                $table->string('profile')->default('Operador');
                $table->boolean('is_active')->default(true)->index();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('identity_directory_groups')) {
            Schema::create('identity_directory_groups', function (Blueprint $table) {
                $table->id();
                $table->string('directory_key', 128)->unique();
                $table->string('name')->index();
                $table->text('distinguished_name')->nullable();
                $table->text('description')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('usuarios_admin')) {
            Schema::table('usuarios_admin', function (Blueprint $table) {
                if (!Schema::hasColumn('usuarios_admin', 'identity_source')) $table->string('identity_source', 20)->default('local')->after('perfil');
                if (!Schema::hasColumn('usuarios_admin', 'ldap_directory_key')) $table->string('ldap_directory_key', 128)->nullable()->index()->after('identity_source');
                if (!Schema::hasColumn('usuarios_admin', 'ldap_dn')) $table->text('ldap_dn')->nullable()->after('ldap_directory_key');
                if (!Schema::hasColumn('usuarios_admin', 'ativo')) $table->boolean('ativo')->default(true)->index()->after('ldap_dn');
                if (!Schema::hasColumn('usuarios_admin', 'ldap_synced_at')) $table->timestamp('ldap_synced_at')->nullable()->after('ativo');
                if (!Schema::hasColumn('usuarios_admin', 'last_login_at')) $table->timestamp('last_login_at')->nullable()->after('ldap_synced_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_directory_groups');
        Schema::dropIfExists('identity_directory_users');
    }
};
