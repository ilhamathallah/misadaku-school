<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KwitansiController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', function () {
    return redirect('/redirect-by-role');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->get('/redirect-by-role', function () {
    $user = Auth::user();

    if ($user->role === 'admin') {
        return redirect('/admin');
    }

    if ($user->role === 'treasurer') {
        return redirect('/treasurer');
    }

    if ($user->role === 'student') {
        return redirect('/student');
    }

    if ($user->role === 'guest') {
        return redirect('/waiting-approval');
    }

    abort(403, 'Unauthorized.');
});

Route::get('/waiting-approval', function () {
    return view('waiting-approval');
})->middleware(['auth'])->name('waiting-approval');

// kwitansi admin, treasurer
Route::get('/kwitansi/student-payment/{id}', [KwitansiController::class, 'show'])->name('kwitansi.student-payment');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/'); // arahkan ke halaman login atau home
})->name('logout');

require __DIR__ . '/auth.php';
