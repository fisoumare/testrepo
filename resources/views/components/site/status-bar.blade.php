<div class="bg-ink text-ardoise-light">
    <div class="wrap flex h-8 items-center justify-between gap-4 text-[11px] font-mono tracking-[0.08em]">
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="led led-on" aria-hidden="true"></span>
            <span class="uppercase text-emerald-400">Systèmes opérationnels</span>
            <span class="hidden sm:inline text-ink-line">|</span>
            <span class="hidden sm:inline uppercase" x-data="clock" x-text="'Conakry ' + time + ' GMT'">Conakry · GMT</span>
        </div>
        <div class="flex items-center gap-4 min-w-0">
            <span class="hidden md:inline uppercase truncate">Support {{ setting('hours_short', 'Lun–ven · 8 h 30 – 17 h 30') }}</span>
            <a href="tel:{{ preg_replace('/\s+/', '', setting('phone', '')) }}" class="uppercase text-white hover:text-emerald-400 transition whitespace-nowrap">{{ setting('phone') }}</a>
        </div>
    </div>
</div>
