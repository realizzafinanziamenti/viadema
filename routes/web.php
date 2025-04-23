<?php

use App\Livewire\Admin\Team\TeamCreate;
use App\Livewire\Admin\Team\TeamIndex;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::permanentRedirect('/', 'login');

Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {
    // Dasboard Route
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Team Member Routes
    Route::get('/team', TeamIndex::class)->name('team.index')->middleware('can:access team members');
    Route::get('/team/create', TeamCreate::class)->name('team.create')->middleware('can:create team members');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
