<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class BotStatusReversal
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $status = DB::table('bot_statuses')->where('id', 1)->value('status') ?? 'disconnected';

        if ($status == 'connected') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp bot is connected already.'
                ], 403);
            }

            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
