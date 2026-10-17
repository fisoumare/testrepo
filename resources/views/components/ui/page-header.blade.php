@props(['code' => null, 'kicker' => null, 'title', 'dark' => false])
<section class="{{ $dark ? 'bg-ink text-white rack-lines' : 'bg-paper border-b border-navy-900/8' }}">
    <div class="wrap py-14 sm:py-20">
        <div class="grid gap-8 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-8">
                @if ($code || $kicker)
                    <p class="label mb-4 flex items-center gap-3 {{ $dark ? 'text-emerald-400' : 'text-emerald-700' }}">
                        @if ($code)<span class="rounded-sm border px-1.5 py-0.5 {{ $dark ? 'border-emerald-400/40' : 'border-emerald-700/30' }}">{{ $code }}</span>@endif
                        @if ($kicker)<span>{{ $kicker }}</span>@endif
                    </p>
                @endif
                <h1 class="h-display text-4xl sm:text-5xl lg:text-6xl {{ $dark ? 'text-white' : 'text-navy-900' }}">{{ $title }}</h1>
                @if (trim($slot))
                    <p class="lead mt-6 max-w-2xl {{ $dark ? 'text-ardoise-light' : '' }}">{{ $slot }}</p>
                @endif
            </div>
            @isset($aside)
                <div class="lg:col-span-4">{{ $aside }}</div>
            @endisset
        </div>
    </div>
</section>
