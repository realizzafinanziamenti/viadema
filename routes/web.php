<?php

use App\Livewire\Admin\Calendar\Calendar;
use App\Livewire\Admin\Customer\CustomerCreate;
use App\Livewire\Admin\Customer\CustomerIndex;
use App\Livewire\Admin\Customer\CustomerShow;
use App\Livewire\Admin\Customer\CustomerUpdate;
use App\Livewire\Admin\Dashboard\Dashboard;
use App\Livewire\Admin\Event\EventIndex;
use App\Livewire\Admin\FormDocument\FormDocumentIndex;
use App\Livewire\Admin\Lead\LeadCreate;
use App\Livewire\Admin\Lead\LeadIndex;
use App\Livewire\Admin\Lead\LeadShow;
use App\Livewire\Admin\Lead\LeadUpdate;
use App\Livewire\Admin\Practice\PracticeCreate;
use App\Livewire\Admin\Practice\PracticeIndex;
use App\Livewire\Admin\Practice\PracticeShow;
use App\Livewire\Admin\Practice\PracticeUpdate;
use App\Livewire\Admin\Profile\ProfileShow;
use App\Livewire\Admin\Profile\ProfileUpdate;
use App\Livewire\Admin\Profile\ProfileUpdatePassword;
use App\Livewire\Admin\Setting\SettingIndex;
use App\Livewire\Admin\Simulator\SimulatorIndex;
use App\Livewire\Admin\User\UserCreate;
use App\Livewire\Admin\User\UserIndex;
use App\Livewire\Admin\User\UserShow;
use App\Livewire\Admin\User\UserUpdate;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::permanentRedirect('/', 'login');

Route::prefix('/')->middleware(['auth', 'verified'])->group(function () {
    // Dasboard Route
    Route::get('dashboard', Dashboard::class)->name('dashboard')->middleware('can:access dashboard');

    // Calendar Route
    Route::get('calendar', Calendar::class)->name('calendar')->middleware('can:access calendar');

    // Event Routes
    Route::get('events', EventIndex::class)->name('event.index')->middleware('can:access events');

    // User Routes
    Route::get('users', UserIndex::class)->name('user.index')->middleware('can:access users');
    Route::get('users/create', UserCreate::class)->name('user.create')->middleware('can:create users');
    Route::get('users/{id}', UserShow::class)->name('user.show')->middleware('can:view users');
    Route::get('users/{id}/edit', UserUpdate::class)->name('user.edit')->middleware('can:update users');

    // Customer Routes
    Route::get('customers', CustomerIndex::class)->name('customer.index')->middleware('can:access customers');
    Route::get('customers/create/', CustomerCreate::class)->name('customer.create')->middleware('can:create customers');
    Route::get('customers/{id}', CustomerShow::class)->name('customer.show')->middleware(['can:view customers', 'customer.type:customer']);
    Route::get('customers/{id}/edit', CustomerUpdate::class)->name('customer.edit')->middleware(['can:update customers', 'customer.type:customer']);

    // Practice Routes
    Route::get('practices/create/{token?}', PracticeCreate::class)->name('practice.create')->middleware('can:create practices');
    Route::get('practices/{slug?}', PracticeIndex::class)->name('practice.index')->middleware('can:access practices');
    Route::get('practices/details/{id}', PracticeShow::class)->name('practice.show')->middleware('can:view practices');
    Route::get('practices/{id}/edit', PracticeUpdate::class)->name('practice.edit')->middleware('can:update practices');

    // Simulator Routes
    Route::get('simulator', SimulatorIndex::class)->name('simulator.index')->middleware('can:access simulator');

    // Lead Routes
    Route::get('leads', LeadIndex::class)->name('lead.index')->middleware('can:access leads');
    Route::get('leads/create', LeadCreate::class)->name('lead.create')->middleware('can:create leads');
    Route::get('leads/{id}', LeadShow::class)->name('lead.show')->middleware(['can:view leads', 'customer.type:lead']);
    Route::get('leads/{id}/edit', LeadUpdate::class)->name('lead.edit')->middleware(['can:update leads', 'customer.type:lead']);

    // Document Routes
    Route::get('form-documents', FormDocumentIndex::class)->name('form-document.index')->middleware('can:access form documents');

    // Settings Routes
    Route::get('settings', SettingIndex::class)->name('setting.index')->middleware('can:access settings');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', ProfileShow::class)->name('profile.show')->middleware('can:view profile');
    Route::get('/profile/edit', ProfileUpdate::class)->name('profile.edit')->middleware('can:update profile');
    Route::get('/profile/edit-password', ProfileUpdatePassword::class)->name('profile.edit.password')->middleware('can:update profile');

    // Route::redirect('settings', 'settings/profile');

    // Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    // Volt::route('settings/password', 'settings.password')->name('settings.password');
    // Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
