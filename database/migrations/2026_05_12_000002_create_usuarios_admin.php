<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    public function up(): void {
        if(!Schema::hasTable('usuarios_admin')) {
            Schema::create('usuarios_admin', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->string('email')->nullable();
                $table->string('usuario')->unique();
                $table->string('senha');
                $table->string('perfil')->default('Operador');
                $table->unsignedBigInteger('pre_registro_id')->nullable();
                $table->timestamps();
            });

            // Bootstrap local é opcional e nunca possui senha fixa no repositório.
            $bootstrapPassword = env('CADCOLAB_BOOTSTRAP_ADMIN_PASSWORD');
            if (!empty($bootstrapPassword)) {
                DB::table('usuarios_admin')->insert([
                    'nome' => env('CADCOLAB_BOOTSTRAP_ADMIN_NAME', 'Administrador CADCOLAB'),
                    'email' => env('CADCOLAB_BOOTSTRAP_ADMIN_EMAIL'),
                    'usuario' => env('CADCOLAB_BOOTSTRAP_ADMIN_USER', 'admin'),
                    'senha' => Hash::make($bootstrapPassword),
                    'perfil' => 'TI',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    public function down(): void {}
};
