@extends('layouts.cadcolab-v5')
@section('content')
<div class="v5-card">
    <div class="v5-section-head"><div><h2>{{ $module['label'] }}</h2><small style="color:var(--v5-muted)">Visualização consolidada no shell v5.</small></div><span class="v5-badge">{{ count($dados['regs'] ?? []) }} registros</span></div>
    <div style="overflow:auto">
        @php $rows=$dados['regs'] ?? []; $columns=$rows ? array_slice(array_keys($rows[0]),0,8) : []; @endphp
        <table class="v5-table"><thead><tr>@foreach($columns as $col)<th>{{ str_replace('_',' ',$col) }}</th>@endforeach<th>Ações</th></tr></thead><tbody>
        @forelse($rows as $row)
            <tr>@foreach($columns as $col)<td>{{ is_scalar($row[$col] ?? null) ? \Illuminate\Support\Str::limit((string)($row[$col] ?? ''),60) : '-' }}</td>@endforeach
            <td style="white-space:nowrap">
                @if(isset($row['id']))
                    <form method="POST" action="/acao" style="display:inline" onsubmit="return confirm('Confirmar exclusão deste registro?')">@csrf<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="{{ $row['id'] }}"><input type="hidden" name="table" value="{{ $tableName ?? $page }}"><button type="submit" title="Excluir" style="border:1px solid var(--v5-border);background:transparent;color:#ff8996;border-radius:8px;padding:6px 8px;cursor:pointer"><i class="fa-solid fa-trash"></i></button></form>
                @endif
            </td></tr>
        @empty<tr><td colspan="9" style="color:var(--v5-muted)">Nenhum registro encontrado.</td></tr>@endforelse
        </tbody></table>
    </div>
</div>
@endsection
