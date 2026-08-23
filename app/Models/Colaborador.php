<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Colaborador extends Model {
    protected $table = 'colaboradores';
    public $incrementing = false;
    protected $fillable = ['id','nome','cpf','data_nascimento','matricula','unidade','cargo','status_m365','licenca_type'];

    public static function boot() {
        parent::boot();
        static::creating(function ($model) { if (empty($model->id)) { $model->id = rand(100000, 999999); } });
    }

    public static function log($usuario, $acao, $detalhes) {
        $letras = strlen(preg_replace('/[^a-zA-Z]/', '', $usuario));
        $soma = (int)date('Y') + (int)date('m') + (int)date('d') + (int)date('H') + $letras;
        $codigo = 'ICP-BR.' . date('YmdHis') . '.' . $soma;
        DB::table('logs_auditoria')->insert(['usuario_admin'=>$usuario, 'acao'=>$acao, 'detalhes'=>$detalhes, 'codigo_controle'=>$codigo]);
    }
}
