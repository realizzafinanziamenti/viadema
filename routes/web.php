<?php

use App\Livewire\Admin\Customer\CustomerIndex;
use App\Livewire\Admin\Customer\CustomerShow;
use App\Livewire\Admin\Team\TeamCreate;
use App\Livewire\Admin\Team\TeamIndex;
use App\Livewire\Admin\Team\TeamShow;
use App\Livewire\Admin\Team\TeamUpdate;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::permanentRedirect('/', 'login');

Route::prefix('admin/')->middleware(['auth', 'verified'])->group(function () {
    // Dasboard Route
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Team Member Routes
    Route::get('users/team', TeamIndex::class)->name('user.team.index')->middleware('can:access team members');
    Route::get('users/team/create', TeamCreate::class)->name('user.team.create')->middleware('can:create team members');
    Route::get('users/team/{id}', TeamShow::class)->name('user.team.show')->middleware('can:view team members');
    Route::get('users/team/{id}/edit', TeamUpdate::class)->name('user.team.edit')->middleware('can:update team members');

    // Customer Routes
    Route::get('customers', CustomerIndex::class)->name('customer.index')->middleware('can:access customers');
    // Route::get('customers/create', \App\Livewire\Admin\Customer\CustomerCreate::class)->name('customer.create')->middleware('can:create customers');
    Route::get('customers/{id}', CustomerShow::class)->name('customer.show')->middleware('can:view customers');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
