@extends('layouts.cadcolab-v5')
@push('styles')
<link rel="stylesheet" href="/cadcolab-badge-studio.css?v=500a1">
@endpush
@section('content')
<section class="content-area">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="v5-card" style="padding:40px;text-align:center;color:var(--v5-muted)"><i class="fa-solid fa-circle-notch fa-spin"></i> Carregando Designer de Crachás…</div>
</section>
@endsection
@push('scripts')
<script src="/cadcolab-badge-studio.js?v=500a1"></script>
@endpush
