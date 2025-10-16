<?php

use App\Models\Log;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\WhatsAppController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/schedules', [ScheduleController::class, 'showSchedules']);
Route::get('/whatsapp/qr', [WhatsAppController::class, 'show']);
Route::post('/whatsapp/qr', [WhatsAppController::class, 'store']);
Route::post('/whatsapp/bot-info', [WhatsAppController::class, 'botInfo']);
Route::post('/histories', [HistoryController::class, 'store']);
Route::get('/histories/{contact_number}', [HistoryController::class, 'historiesByContact']);

Route::post('/logs', function (Request $request) {
    Log::create([
        'message' => $request->message,
    ]);
    return response()->json(['status' => 'ok']);
});

Route::post('/whatsapp/bot-status', [WhatsAppController::class, 'botStatus']);
Route::post('/contacts/{id}/mark-read', [HistoryController::class, 'markAsRead']);

Route::get('/contacts/by-category/{id}', function ($id) {
    return Contact::where('category_id', $id)->get();
});