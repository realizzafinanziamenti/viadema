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
    Route::get('users/team', TeamIndex::class)->name('user.team.index')->middleware('can:access team members');
    Route::get('users/team/create', TeamCreate::class)->name('user.team.create')->middleware('can:create team members');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
