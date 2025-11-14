<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // CollabFlow Routes
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', function () { return view('projects.index'); })->name('index');
        Route::get('/create', function () { return view('projects.create'); })->name('create');
        Route::get('/{id}', function ($id) { return view('projects.show', compact('id')); })->name('show');
    });

    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', function () { return view('tasks.index'); })->name('index');
        Route::get('/{id}', function ($id) { return view('tasks.show', compact('id')); })->name('show');
    });

    Route::get('/schedule', function () { return view('schedule.index'); })->name('schedule.index');
    Route::get('/profile', function () { return view('profile.index'); })->name('profile');
    Route::get('/help', function () { return view('help.index'); })->name('help');
    Route::get('/settings', function () { return view('settings.index'); })->name('settings');

    // Original settings routes
    Route::redirect('settings-old', 'settings-old/profile');
    Volt::route('settings-old/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings-old/password', 'settings.password')->name('password.edit');
    Volt::route('settings-old/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings-old/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

require __DIR__.'/auth.php';
