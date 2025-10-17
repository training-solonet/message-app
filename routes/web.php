<?php

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ManageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DashboardController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::get('/', function () {
    return view('welcome');
})->middleware('bot.reversal')->name('bot.login');

Route::get('/log', [LogController::class, 'index'])->middleware(['auth', 'verified'])->name('log');

// Route::resource('manage', ManageController::class)->middleware(['auth', 'verified']);

// Route::post('/manage/start', [ManageController::class, 'startBot'])->middleware(['auth', 'verified'])->name('bot.start');
// Route::post('/manage/stop', [ManageController::class, 'stopBot'])->middleware(['auth', 'verified'])->name('bot.stop');
// Route::get('/manage/status', [ManageController::class, 'status'])->middleware(['auth', 'verified'])->name('bot.status');

Route::resource('schedules', ScheduleController::class)->middleware(['auth', 'verified', 'check.bot']);
Route::resource('contacts', ContactController::class)->middleware(['auth', 'verified', 'check.bot']);
Route::resource('categories', CategoryController::class)->middleware(['auth', 'verified', 'check.bot']);

Route::post('/whatsapp/logout', [BotController::class, 'logoutBot'])->name('whatsapp.logout');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'check.bot'
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

Route::post('/chats/{contact_id}/read', [HistoryController::class, 'markAsRead'])->name('chats.markAsRead');
Route::post('/histories/{history}/toggle-note', [HistoryController::class, 'toggleNote']);
Route::patch('/schedules/{schedule}/toggle-status', [ScheduleController::class, 'toggleStatus'])
    ->name('schedules.toggleStatus');

Route::middleware(['logged.in'])->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');
});