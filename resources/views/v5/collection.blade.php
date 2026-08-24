@extends('layouts.cadcolab-v5')
@section('content')
<div class="v5-card">
    <div class="v5-section-head"><div><h2>{{ $module['label'] }}</h2><small style="color:var(--v5-muted)">Cadastros corporativos centralizados, auditáveis e protegidos por perfil.</small></div><div style="display:flex;gap:8px;align-items:center"><span class="v5-badge">{{ count($dados['regs'] ?? []) }} registros</span>@if($canManageCollection ?? false)<button type="button" class="v5-badge" style="border:0;cursor:pointer" onclick="openCollectionEditor()"><i class="fa-solid fa-plus"></i>&nbsp;Novo</button>@endif</div></div>
    <div style="overflow:auto">
        @php $rows=$dados['regs'] ?? []; $hiddenColumns=['senha','password','remember_token','identity_dn','ldap_dn']; $columns=$rows ? array_slice(array_values(array_diff(array_keys($rows[0]),$hiddenColumns)),0,8) : []; @endphp
        <table class="v5-table"><thead><tr>@foreach($columns as $col)<th>{{ str_replace('_',' ',$col) }}</th>@endforeach<th>Ações</th></tr></thead><tbody>
        @forelse($rows as $row)
            <tr>@foreach($columns as $col)<td>{{ is_scalar($row[$col] ?? null) ? \Illuminate\Support\Str::limit((string)($row[$col] ?? ''),60) : '-' }}</td>@endforeach
            <td style="white-space:nowrap">
                @if(isset($row['id']))
                    @if($canManageCollection ?? false)<button type="button" title="Editar" onclick="openCollectionEditor({{ (int)$row['id'] }})" style="border:1px solid var(--v5-border);background:transparent;color:#70d7ff;border-radius:8px;padding:6px 8px;cursor:pointer"><i class="fa-solid fa-pen"></i></button>@endif
                    @if(in_array(app(\App\Services\ModuleRegistryService::class)->role(), ['admin','ti'], true))<form method="POST" action="/acao" style="display:inline" onsubmit="return confirm('Confirmar exclusão permanente deste registro?')">@csrf<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="{{ $row['id'] }}"><input type="hidden" name="table" value="{{ $tableName ?? $page }}"><button type="submit" title="Excluir" style="border:1px solid var(--v5-border);background:transparent;color:#ff8996;border-radius:8px;padding:6px 8px;cursor:pointer"><i class="fa-solid fa-trash"></i></button></form>@endif
                @endif
            </td></tr>
        @empty<tr><td colspan="9" style="color:var(--v5-muted)">Nenhum registro encontrado.</td></tr>@endforelse
        </tbody></table>
    </div>
</div>
@if($canManageCollection ?? false)
<dialog id="collectionEditor" style="width:min(680px,calc(100vw - 30px));border:1px solid var(--v5-border);border-radius:16px;background:#111827;color:#fff;padding:0;box-shadow:0 28px 80px rgba(0,0,0,.55)">
    <form method="POST" action="/acao" id="collectionForm" style="padding:20px">@csrf
        <input type="hidden" name="action" value="save_{{ $tableName }}"><input type="hidden" name="table" value="{{ $tableName }}"><input type="hidden" name="return_page" value="{{ $page }}"><input type="hidden" name="id" value="">
        <div class="v5-section-head"><div><h2 id="collectionEditorTitle" style="margin:0">Novo registro</h2><small style="color:var(--v5-muted)">Os campos disponíveis seguem a estrutura real do banco.</small></div><button type="button" onclick="document.getElementById('collectionEditor').close()" style="border:0;background:transparent;color:#fff;cursor:pointer;font-size:18px">×</button></div>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
            @foreach($collectionFields ?? [] as $field)
            <label style="display:grid;gap:6px;font-size:11px;color:var(--v5-muted)"><span>{{ $field['label'] }}</span><input name="{{ $field['name'] }}" type="{{ $field['type'] }}" {{ $field['required'] ? 'required' : '' }} autocomplete="off" style="background:#0c1320;border:1px solid var(--v5-border);color:#fff;border-radius:9px;padding:10px 11px"></label>
            @endforeach
        </div>
        <div style="display:flex;justify-content:flex-end;gap:9px;margin-top:18px"><button type="button" class="v5-badge" style="border:0;cursor:pointer" onclick="document.getElementById('collectionEditor').close()">Cancelar</button><button type="submit" style="border:0;border-radius:9px;background:var(--v5-primary);color:#fff;padding:10px 16px;font-weight:800;cursor:pointer">Salvar alterações</button></div>
    </form>
</dialog>
@endif
@endsection
@push('scripts')
<script>
const collectionRows = @json($collectionRows ?? []);
function openCollectionEditor(id = null) {
    const dialog = document.getElementById('collectionEditor');
    const form = document.getElementById('collectionForm');
    if (!dialog || !form) return;
    form.reset(); form.elements.id.value = id || '';
    document.getElementById('collectionEditorTitle').textContent = id ? 'Editar registro' : 'Novo registro';
    const row = id ? collectionRows[id] : null;
    if (form.elements.senha) form.elements.senha.required = !id;
    if (row) Object.entries(row).forEach(([key,value]) => { if (form.elements[key] && form.elements[key].type !== 'password') form.elements[key].value = value ?? ''; });
    dialog.showModal();
}
</script>
@endpush
