<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CheckBotStatus
{
    public function handle(Request $request, Closure $next)
    {
        $status = DB::table('bot_statuses')->where('id', 1)->value('status') ?? 'disconnected';

        if ($status !== 'connected') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp bot is not connected. Please login first.'
                ], 403);
            }

            return redirect()->route('bot.login');
        }

        return $next($request);
    }
}
