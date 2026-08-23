<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SignatureController extends Controller
{
    public function getSignature(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');
        
        $signature = DB::table('user_signatures')
            ->where('email', $email)
            ->where('token', $token)
            ->first();
        
        if(!$signature) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'signature' => $signature->html_signature
        ]);
    }
}
