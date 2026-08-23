<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BadgeStudioController extends Controller
{
    private function guard(): void
    {
        abort_unless(session('admin_logado'), 401);
    }

    public function index()
    {
        $this->guard();
        $templates = Schema::hasTable('badge_templates')
            ? DB::table('badge_templates')->orderByDesc('is_default')->orderBy('name')->get()
            : collect();

        return response()->json([
            'templates' => $templates->map(fn ($t) => [
                'id'=>$t->id,'name'=>$t->name,'slug'=>$t->slug,'scope_unit'=>$t->scope_unit,
                'orientation'=>$t->orientation,'width_mm'=>(float)$t->width_mm,'height_mm'=>(float)$t->height_mm,
                'front'=>json_decode($t->front_json, true) ?: [],'back'=>json_decode($t->back_json ?: '[]', true) ?: [],
                'is_default'=>(bool)$t->is_default,'active'=>(bool)$t->active,'updated_at'=>$t->updated_at,
            ])->values(),
            'fields' => $this->fields(),
            'sample' => $this->sampleData(),
        ]);
    }

    public function save(Request $request)
    {
        $this->guard();
        abort_unless(Schema::hasTable('badge_templates'), 409, 'Execute as migrations do Badge Studio.');
        $data = $request->validate([
            'id'=>'nullable|integer','name'=>'required|string|max:120','scope_unit'=>'nullable|string|max:150',
            'orientation'=>'required|in:portrait,landscape','width_mm'=>'required|numeric|min:30|max:200',
            'height_mm'=>'required|numeric|min:30|max:200','front'=>'required|array','back'=>'nullable|array',
            'is_default'=>'nullable|boolean','active'=>'nullable|boolean',
        ]);
        $id = $data['id'] ?? null;
        $payload = [
            'name'=>$data['name'],'scope_unit'=>$data['scope_unit'] ?? null,'orientation'=>$data['orientation'],
            'width_mm'=>$data['width_mm'],'height_mm'=>$data['height_mm'],
            'front_json'=>json_encode($data['front'], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'back_json'=>json_encode($data['back'] ?? [], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'is_default'=>(bool)($data['is_default'] ?? false),'active'=>(bool)($data['active'] ?? true),
            'updated_at'=>now(),
        ];
        if ($payload['is_default']) DB::table('badge_templates')->update(['is_default'=>false]);
        if ($id) {
            DB::table('badge_templates')->where('id',$id)->update($payload);
        } else {
            $payload['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));
            $payload['created_by'] = (string)(session('admin_nome') ?: session('admin_usuario') ?: 'admin');
            $payload['created_at'] = now();
            $id = DB::table('badge_templates')->insertGetId($payload);
        }
        return response()->json(['success'=>true,'id'=>$id]);
    }

    public function delete(int $id)
    {
        $this->guard();
        DB::table('badge_templates')->where('id',$id)->delete();
        return response()->json(['success'=>true]);
    }

    public function sample(Request $request)
    {
        $this->guard();
        return response()->json($this->sampleData((int)$request->query('id',0)));
    }

    private function fields(): array
    {
        return [
            ['key'=>'nome','label'=>'Nome completo'],['key'=>'matricula','label'=>'Matrícula'],
            ['key'=>'cargo','label'=>'Cargo'],['key'=>'unidade','label'=>'Unidade'],
            ['key'=>'email_corporativo','label'=>'E-mail corporativo'],['key'=>'cpf','label'=>'CPF'],
            ['key'=>'data_nascimento','label'=>'Data de nascimento'],['key'=>'foto','label'=>'Foto do colaborador'],
        ];
    }

    private function sampleData(int $id = 0): array
    {
        $row = Schema::hasTable('colaboradores')
            ? DB::table('colaboradores')->when($id, fn($q)=>$q->where('id',$id))->orderBy('nome')->first()
            : null;
        $photo = null;
        if ($row) {
            foreach (['foto','foto_path','foto_url','avatar'] as $c) {
                if (Schema::hasColumn('colaboradores',$c) && !empty($row->{$c})) { $photo = $row->{$c}; break; }
            }
        }
        return [
            'id'=>$row->id ?? null,'nome'=>$row->nome ?? 'Maria de Souza','matricula'=>$row->matricula ?? 'ENF-00248',
            'cargo'=>$row->cargo ?? 'Analista Administrativo','unidade'=>$row->unidade ?? 'Unidade Central',
            'email_corporativo'=>$row->email_corporativo ?? 'maria.souza@enfas.com.br','cpf'=>$row->cpf ?? '***.***.***-**',
            'data_nascimento'=>$row->data_nascimento ?? null,'foto'=>$photo,
        ];
    }
}
