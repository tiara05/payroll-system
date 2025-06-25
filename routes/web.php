<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalaryRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/', [SalaryRequestController::class, 'index'])->name('dashboard');

    Route::get('/salary/create', [SalaryRequestController::class, 'create'])->name('salary.create');
    Route::post('/salary', [SalaryRequestController::class, 'store'])->name('salary.store');

    Route::post('/salary/{salaryRequest}/approve', [SalaryRequestController::class, 'approve'])->name('salary.approve');
    Route::post('/salary/{salaryRequest}/reject', [SalaryRequestController::class, 'reject'])->name('salary.reject');

    Route::post('/salary/{salaryRequest}/pay', [SalaryRequestController::class, 'processPayment'])->name('salary.pay');

    Route::post('/notifications/read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read');

});

require __DIR__.'/auth.php';
