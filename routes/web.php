<?php

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ManageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/log', [LogController::class, 'index'])->middleware(['auth', 'verified'])->name('log');

// Route::resource('manage', ManageController::class)->middleware(['auth', 'verified']);

// Route::post('/manage/start', [ManageController::class, 'startBot'])->middleware(['auth', 'verified'])->name('bot.start');
// Route::post('/manage/stop', [ManageController::class, 'stopBot'])->middleware(['auth', 'verified'])->name('bot.stop');
// Route::get('/manage/status', [ManageController::class, 'status'])->middleware(['auth', 'verified'])->name('bot.status');

Route::resource('schedules', ScheduleController::class)->middleware(['auth', 'verified']);
Route::resource('contacts', ContactController::class)->middleware(['auth', 'verified']);
Route::resource('categories', CategoryController::class)->middleware(['auth', 'verified']);

Route::post('/bot/logout', [BotController::class, 'logout'])->middleware(['auth', 'verified'])->name('bot.logout');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/{contact}', [DashboardController::class, 'update'])->name('dashboard.update');
});

Route::post('/logs', function (Request $request) {
    Log::create([
        'message' => $request->message,
    ]);
    return response()->json(['status' => 'ok']);
});