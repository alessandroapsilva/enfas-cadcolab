<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Tabela de Configurações
        if(!Schema::hasTable('configuracoes')) {
            Schema::create('configuracoes', function (Blueprint $table) {
                $table->id();
                $table->string('chave')->unique();
                $table->longText('valor')->nullable();
                $table->timestamps();
            });
        }

        // Tabela de Logs de Auditoria ICP-BR
        if(!Schema::hasTable('logs_auditoria')) {
            Schema::create('logs_auditoria', function (Blueprint $table) {
                $table->id();
                $table->string('usuario_admin');
                $table->string('acao');
                $table->text('detalhes');
                $table->string('codigo_controle'); // ICP-BR Hash
                $table->timestamp('data_hora')->useCurrent();
            });
        }

        // Tabela de Logs de Erros
        if(!Schema::hasTable('logs_erros')) {
            Schema::create('logs_erros', function (Blueprint $table) {
                $table->id();
                $table->string('modulo');
                $table->text('mensagem');
                $table->timestamp('data_hora')->useCurrent();
            });
        }

        // Tabela principal (Substituindo pre_registros por colaboradores para o padrão novo)
        if(!Schema::hasTable('colaboradores')) {
            Schema::create('colaboradores', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary(); // ID Aleatório 6 dígitos
                $table->string('nome_completo');
                $table->string('cpf')->unique();
                $table->string('matricula')->nullable();
                $table->date('data_nascimento')->nullable();
                $table->string('telefone')->nullable();
                $table->string('email_pessoal')->nullable();
                $table->string('username_criado')->nullable(); // Para o M365 (@enfas.com.br)
                $table->string('status')->default('pendente');
                $table->string('m365_perfil')->nullable();
                $table->string('m365_apps')->nullable();
                $table->text('dados_extras')->nullable();
                $table->timestamps();
            });
        }
    }
    public function down(): void {
        // Não faremos drop em produção para garantir segurança
    }
};
