<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-neutral-100 antialiased">
    <div class="relative flex items-center justify-center w-full h-screen overflow-hidden">

        <div
            class="absolute top-0 left-0 w-full h-full z-0 pointer-events-none select-none overflow-hidden bg-white opacity-50">
            <video class="absolute top-0 left-0 min-w-full min-h-full object-cover" autoplay muted loop playsinline
                disablepictureinpicture controlslist="nodownload noremoteplayback noplaybackrate nofullscreen"
                tabindex="-1">
                <source src="{{ asset('videos/login-animated-background.mp4') }}" type="video/mp4" />
            </video>
        </div>

        <div class="flex w-full max-w-[544px] flex-col gap-6 z-10">
            <div class="flex flex-col gap-6">
                <div class="rounded-4xl border bg-white text-stone-800 shadow-xs">
                    <div class="px-10 py-8">{{ $slot }}</div>
                </div>
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
