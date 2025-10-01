<?php
namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WhatsAppController extends Controller
{
    public function store(Request $request)
    {
        if ($request->qr) {
            Cache::put('whatsapp_qr', $request->qr, now()->addMinutes(5));
        } else {
            Cache::forget('whatsapp_qr');
        }

        return response()->json(['status' => 'ok']);
    }

    public function show()
    {
        $qr = Cache::get('whatsapp_qr');
        return response()->json([
            'qr' => $qr,
            'connected' => $qr ? false : true,
        ]);
    }

    public function botInfo(Request $request)
    {
        // Contoh: simpan ke database atau session
        $number = $request->input('number');
        $name   = $request->input('name');

        // Misalnya disimpan ke tabel `bots`
        Bot::updateOrCreate(
            ['id' => 1], // atau pakai bot_id unik
            ['number' => $number, 'name' => $name]
        );

        return response()->json(['status' => 'success', 'number' => $number, 'name' => $name]);
    }

}
