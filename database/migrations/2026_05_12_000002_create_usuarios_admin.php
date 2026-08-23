<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        }
    }
    public function down(): void {}
};
