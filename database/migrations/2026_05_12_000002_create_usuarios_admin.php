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
                $table->string('perfil')->default('Operador'); // TI, RH, Admin
                $table->unsignedBigInteger('pre_registro_id')->nullable(); // Vinculo com Colaborador
                $table->timestamps();
            });

            // Injetar o usuário master automaticamente
            DB::table('usuarios_admin')->insert([
                'nome' => 'Alessandro Silva',
                'email' => 'alessandro@enfas.com.br',
                'usuario' => 'admin',
                'senha' => Hash::make('Enfas@2026'),
                'perfil' => 'TI',
                'created_at' => now()
            ]);
        }
    }
    public function down(): void {}
};
