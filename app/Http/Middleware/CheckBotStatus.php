<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckBotStatus
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Request bot status from your API
            $appUrl = config('app.url'); 
            $response = Http::get($appUrl.'/api/whatsapp/bot-status');
            
            // Assume response is like { "status": "connected" } or "disconnected"
            $status = $response->json()['status'] ?? 'disconnected';
            
            if ($status !== 'connected') {
                // Redirect to bot login page if not connected
                return redirect()->route('bot.login'); 
            }
        } catch (\Exception $e) {
            // In case of API failure, treat as disconnected
            return redirect()->route('bot.login');
        }

        return $next($request);
    }
}
