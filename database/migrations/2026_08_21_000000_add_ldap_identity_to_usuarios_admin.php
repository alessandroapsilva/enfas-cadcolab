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

        $admin = DB::table('usuarios_admin')->where('usuario', 'admin')->first();
        if ($admin && Hash::check('Enfas@2026', $admin->senha)) {
            DB::table('usuarios_admin')->where('id', $admin->id)->update([
                'senha' => Hash::make(Str::random(64)),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('usuarios_admin', function (Blueprint $table) {
            $table->dropColumn(['ldap_dn', 'auth_source', 'last_login_at']);
        });
    }
};
