<?php

use App\Livewire\Admin\Calendar\Calendar;
use App\Livewire\Admin\Customer\CustomerCreate;
use App\Livewire\Admin\Customer\CustomerIndex;
use App\Livewire\Admin\Customer\CustomerShow;
use App\Livewire\Admin\Customer\CustomerUpdate;
use App\Livewire\Admin\Event\EventIndex;
use App\Livewire\Admin\Practice\PracticeCreate;
use App\Livewire\Admin\Practice\PracticeIndex;
use App\Livewire\Admin\Practice\PracticeShow;
use App\Livewire\Admin\Practice\PracticeUpdate;
use App\Livewire\Admin\Setting\SettingIndex;
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

    // Calendar Route
    Route::get('calendar', Calendar::class)->name('calendar')->middleware('can:access calendar');

    // Event Routes
    Route::get('events', EventIndex::class)->name('event.index')->middleware('can:access events');

    // Team Member Routes
    Route::get('users/team', TeamIndex::class)->name('user.team.index')->middleware('can:access team members');
    Route::get('users/team/create', TeamCreate::class)->name('user.team.create')->middleware('can:create team members');
    Route::get('users/team/{id}', TeamShow::class)->name('user.team.show')->middleware('can:view team members');
    Route::get('users/team/{id}/edit', TeamUpdate::class)->name('user.team.edit')->middleware('can:update team members');

    // Customer Routes
    Route::get('customers', CustomerIndex::class)->name('customer.index')->middleware('can:access customers');
    Route::get('customers/create', CustomerCreate::class)->name('customer.create')->middleware('can:create customers');
    Route::get('customers/{id}', CustomerShow::class)->name('customer.show')->middleware('can:view customers');
    Route::get('customers/{id}/edit', CustomerUpdate::class)->name('customer.edit')->middleware('can:update customers');

    // Practice Routes
    Route::get('practices/create', PracticeCreate::class)->name('practice.create')->middleware('can:create practices');
    Route::get('practices/{slug?}', PracticeIndex::class)->name('practice.index')->middleware('can:access practices');
    Route::get('practices/details/{id}', PracticeShow::class)->name('practice.show')->middleware('can:view practices');
    Route::get('practices/{id}/edit', PracticeUpdate::class)->name('practice.edit')->middleware('can:update practices');

    // Settings Routes
    Route::get('settings', SettingIndex::class)->name('setting.index')->middleware('can:access settings');
});

Route::middleware(['auth'])->group(function () {
    // Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
