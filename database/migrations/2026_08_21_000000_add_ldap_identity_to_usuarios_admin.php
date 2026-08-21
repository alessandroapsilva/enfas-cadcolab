<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuarios_admin', function (Blueprint $table) {
            $table->string('ldap_dn', 1024)->nullable()->after('perfil');
            $table->string('auth_source', 20)->default('local')->after('ldap_dn');
            $table->timestamp('last_login_at')->nullable()->after('auth_source');
        });

        // Invalida a conta padrão histórica. Uma conta de contingência deve ser
        // criada explicitamente com o comando cadcolab:admin.
        DB::table('usuarios_admin')->where('usuario', 'admin')->update([
            'usuario' => 'admin-desativado-'.Str::lower(Str::random(8)),
            'senha' => Str::random(96),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('usuarios_admin', function (Blueprint $table) {
            $table->dropColumn(['ldap_dn', 'auth_source', 'last_login_at']);
        });
    }
};
