@props(['title' => 'Un projet ? Parlons-en.', 'text' => null])
<section class="wrap mt-24">
    <div class="unit text-emerald-400/60 relative overflow-hidden rounded-xl bg-navy-900 px-6 py-12 text-white sm:px-12 lg:px-16 rack-lines">
        <div class="absolute inset-y-0 right-0 w-1/2 grid-dots-dark opacity-60 [mask-image:linear-gradient(90deg,transparent,#000)]" aria-hidden="true"></div>
        <div class="relative grid gap-8 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-7">
                <p class="label text-emerald-400 mb-3 flex items-center gap-2"><span class="led led-on"></span> Réponse au plus tard le jour ouvré suivant</p>
                <h2 class="h-section text-white">{{ $title }}</h2>
                <p class="lead mt-4 text-ardoise-light">{{ $text ?? 'Décrivez votre besoin, nous revenons vers vous avec une proposition à prix fixe. '.setting('hours') }}</p>
            </div>
            <div class="lg:col-span-5 flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row lg:justify-end">
                <a href="{{ route('quote') }}" class="btn-primary">Composer une estimation</a>
                <a href="{{ whatsapp_url('Bonjour SOUMATIC, j\'ai un projet à vous présenter.') }}" target="_blank" rel="noopener" class="btn-ghost-dark">
                    <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.5l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 12 12 0 0 0 4.6 4 5.3 5.3 0 0 0 3.2.7 2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3Z"/></svg>
                    WhatsApp · {{ setting('phone') }}
                </a>
            </div>
        </div>
    </div>
</section>
