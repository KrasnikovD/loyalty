<?php

namespace App\Http\Middleware;

use App\Models\Users;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthenticateClientByToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $tokenHash = Str::substr($request->header('Authorization'), Str::length('Basic '));
        $user = Users::where([
            [DB::raw("md5(token)"), '=', $tokenHash],
            ['type', '=', Users::TYPE_USER],
            ['archived', '=', 0]
        ]);
        if (!$user->count()) {
            return response()->json('Unauthorized.', 401);
        }
        Auth::login($user->first());
        return $next($request);
    }
}
