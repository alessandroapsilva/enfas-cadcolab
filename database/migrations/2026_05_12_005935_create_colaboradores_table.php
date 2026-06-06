<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('colaboradores', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); 
            $table->string('nome');
            $table->string('cpf')->unique();
            $table->date('data_nascimento');
            $table->string('matricula')->nullable();
            $table->string('unidade')->nullable();
            $table->string('cargo')->nullable();
            $table->string('email_corporativo')->nullable();
            $table->string('status_m365')->default('pendente');
            $table->string('licenca_type')->default('Business Basic');
            $table->timestamps();
        });

        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();
            $table->longText('valor')->nullable();
            $table->timestamps();
        });

        Schema::create('logs_auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('usuario_admin');
            $table->string('acao');
            $table->text('detalhes');
            $table->string('codigo_controle');
            $table->timestamps();
        });
    }
    public function down(): void { 
        Schema::dropIfExists('colaboradores'); 
        Schema::dropIfExists('configuracoes'); 
        Schema::dropIfExists('logs_auditoria'); 
    }
};
