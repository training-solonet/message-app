<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class BotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function logout()
    {
        $sessionsPath = base_path('sessions');

        try{
            File::deleteDirectory($sessionsPath);
            dd($sessionsPath, File::exists($sessionsPath));

            return redirect()->route('manage.index')->with('message', 'Bot logged out. All sessions cleared.');
        }
        catch(\Exception $err){
            return redirect()->route('manage.index')->with('message', 'Failed to log out: '.$err->getMessage());
        }
    }

    public function logoutBot(Request $request)
    {
        try {
            // Update status bot di database
            DB::table('bot_statuses')->updateOrInsert(
                ['id' => 1],
                [
                    'status' => 'disconnected',
                    'updated_at' => now(),
                ]
            );

            // Kirim sinyal logout ke Node.js
            $laravelApi = env('APP_URL') . '/api';
            $response = Http::post("{$laravelApi}/bot/logout", [
                'action' => 'logout',
                'source' => 'laravel',
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Cek respons dari Node.js
            if ($response->successful()) {
                return redirect()->back()->with('success', 'Logout signal sent to WhatsApp bot.');
            } else {
                return redirect()->back()->with('error', 'Bot did not respond properly.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send logout signal: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to logout WhatsApp Bot.');
        }
    }
}
