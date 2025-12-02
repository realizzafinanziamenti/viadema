<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
}; ?>

<div class="flex flex-col gap-6">
    {{-- Logo --}}
    <div class="flex items-end justify-center py-9">
        <img src="{{ asset('images/viadema-logo.png') }}" alt="Logo" class="h-[136px] w-auto">
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col px-24 pb-9 gap-4">
        <!-- Email Address -->
        <div class="flex flex-col gap-0.5">
            <flux:input wire:model="email" type="email" required autofocus autocomplete="email" placeholder="Email"
                size="lg" />
            <flux:error name="email" />
        </div>

        <!-- Password -->
        <div class="relative">
            <flux:input type="password" placeholder="Password" wire:model="password" required
                autocomplete="current-password" viewable size="lg" />

            @if (Route::has('password.request'))
                <div class="flex items-center justify-end mt-1 pe-1">
                    <a class="text-xs hover:font-bold" :href="route('password.request')" wire:navigate>
                        Password dimenticata?
                    </a>
                </div>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" label="Ricordami" />

        <div class="flex items-center justify-center">
            <flux:button variant="primary" type="submit"
                class="uppercase px-16 bg-blue-custom hover:bg-blue-custom-hover">
                Accedi
            </flux:button>
        </div>
    </form>
</div>
