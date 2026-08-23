<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommunicationLogController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(session('admin_logado'), 401);
        abort_unless(in_array(session('admin_perfil'), ['TI','Admin'], true), 403);
    }

    public function emails(Request $request)
    {
        $this->authorizeAdmin();
        if (!Schema::hasTable('communication_email_logs')) {
            return response()->json(['items'=>[], 'stats'=>['total'=>0,'sent'=>0,'failed'=>0]]);
        }

        $limit = max(1, min((int)$request->query('limit', 50), 200));
        $query = DB::table('communication_email_logs')->orderByDesc('id');
        if ($status = $request->query('status')) $query->where('status', $status);
        if ($recipient = trim((string)$request->query('recipient'))) $query->where('recipient', 'like', '%'.$recipient.'%');

        $items = $query->limit($limit)->get();
        $stats = [
            'total' => DB::table('communication_email_logs')->count(),
            'sent' => DB::table('communication_email_logs')->where('status','sent')->count(),
            'failed' => DB::table('communication_email_logs')->where('status','failed')->count(),
        ];

        return response()->json(['items'=>$items,'stats'=>$stats]);
    }
}
