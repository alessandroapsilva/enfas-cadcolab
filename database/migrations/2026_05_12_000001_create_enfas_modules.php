<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if(!Schema::hasTable('unidades')) {
            Schema::create('unidades', function (Blueprint $table) { $table->id(); $table->string('nome'); $table->string('endereco')->nullable(); $table->string('telefone')->nullable(); $table->text('template_assinatura')->nullable(); $table->timestamps(); });
        }
        if(!Schema::hasTable('setores')) {
            Schema::create('setores', function (Blueprint $table) { $table->id(); $table->string('nome'); $table->string('responsavel')->nullable(); $table->string('ramal')->nullable(); $table->timestamps(); });
        }
        if(!Schema::hasTable('cargos')) {
            Schema::create('cargos', function (Blueprint $table) { $table->id(); $table->string('nome'); $table->unsignedBigInteger('perfil_id')->nullable(); $table->timestamps(); });
        }
        if(!Schema::hasTable('sistemas_hc')) {
            Schema::create('sistemas_hc', function (Blueprint $table) { $table->id(); $table->string('nome'); $table->string('link')->nullable(); $table->timestamps(); });
        }
        
        // Atualizando Colaboradores com os novos campos
        Schema::table('colaboradores', function (Blueprint $table) {
            if(!Schema::hasColumn('colaboradores', 'telefone')) { $table->string('telefone')->nullable(); }
            if(!Schema::hasColumn('colaboradores', 'email_pessoal')) { $table->string('email_pessoal')->nullable(); }
            if(!Schema::hasColumn('colaboradores', 'unidade_id')) { $table->unsignedBigInteger('unidade_id')->nullable(); }
            if(!Schema::hasColumn('colaboradores', 'setor_id')) { $table->unsignedBigInteger('setor_id')->nullable(); }
            if(!Schema::hasColumn('colaboradores', 'cargo_id')) { $table->unsignedBigInteger('cargo_id')->nullable(); }
            if(!Schema::hasColumn('colaboradores', 'username_criado')) { $table->string('username_criado')->nullable(); }
        });
    }
    public function down(): void {}
};
