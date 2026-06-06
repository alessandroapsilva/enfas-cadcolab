<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('colaboradores', function (Blueprint $table) {
            $cols = ['telefone', 'email_pessoal', 'unidade_id', 'setor_id', 'cargo_id', 'username_criado', 'status_m365', 'm365_perfil', 'data_admissao', 'jornada_escala', 'ferias_status', 'ferias_inicio', 'ferias_fim', 'observacoes_rh'];
            foreach($cols as $col) {
                if (!Schema::hasColumn('colaboradores', $col)) {
                    $table->string($col)->nullable();
                }
            }
        });
    }
    public function down(): void {}
};
