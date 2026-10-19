<footer class="relative mt-24 bg-ink text-ardoise-light rack-lines">
    <div class="wrap pt-16 pb-10">
        <div class="grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <img src="{{ asset('brand/logo-blanc.svg') }}" alt="SOUMATIC" class="h-12 w-auto" width="1000" height="300">
                <p class="mt-6 max-w-md text-[15px] leading-relaxed">{{ setting('baseline') }}</p>
                <dl class="spec spec-dark mt-8">
                    <dt>Gérant</dt><dd>{{ setting('manager_name') }}</dd>
                    <dt>RCCM</dt><dd class="font-mono text-[13px]">{{ setting('rccm') }}</dd>
                    <dt>NIF</dt><dd class="font-mono text-[13px]">{{ setting('nif') }}</dd>
                    <dt>Siège</dt><dd>{{ setting('address') }}, {{ setting('city') }}, {{ setting('country') }}</dd>
                    <dt>Horaires</dt><dd>{{ typo(setting('hours')) }}</dd>
                </dl>
            </div>
            <div class="lg:col-span-7 grid gap-10 sm:grid-cols-3">
                <div>
                    <p class="label text-emerald-400 mb-4">Solutions</p>
                    <ul class="space-y-2.5 text-[15px]">
                        @foreach ($navSolutions as $solution)
                            <li><a href="{{ route('solutions.show', $solution) }}" class="hover:text-white transition">{{ $solution->name }} <span class="text-ardoise-light/80">· {{ $solution->tagline }}</span></a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="label text-emerald-400 mb-4">Prestations</p>
                    <ul class="space-y-2.5 text-[15px]">
                        @foreach ($navCategories as $category)
                            <li><a href="{{ route('services.category', $category) }}" class="hover:text-white transition">{{ $category->name }}</a></li>
                        @endforeach
                        <li><a href="{{ route('quote') }}" class="text-white hover:text-emerald-400 transition font-medium">Demander un devis →</a></li>
                    </ul>
                </div>
                <div>
                    <p class="label text-emerald-400 mb-4">Contact</p>
                    <ul class="space-y-2.5 text-[15px]">
                        <li><a href="tel:{{ preg_replace('/\s+/', '', setting('phone', '')) }}" class="hover:text-white transition">{{ setting('phone') }}</a></li>
                        <li><a href="{{ whatsapp_url('Bonjour SOUMATIC,') }}" target="_blank" rel="noopener" class="hover:text-white transition">WhatsApp</a></li>
                        <li><a href="mailto:{{ setting('email') }}" class="hover:text-white transition break-all">{{ setting('email') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Formulaire de contact</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white transition">À propos</a></li>
                        <li><a href="{{ route('references.index') }}" class="hover:text-white transition">Références</a></li>
                        <li><a href="{{ route('posts.index') }}" class="hover:text-white transition">Actualités</a></li>
                        @if (setting('facebook_url'))<li><a href="{{ setting('facebook_url') }}" target="_blank" rel="noopener" class="hover:text-white transition">Facebook</a></li>@endif
                        @if (setting('linkedin_url'))<li><a href="{{ setting('linkedin_url') }}" target="_blank" rel="noopener" class="hover:text-white transition">LinkedIn</a></li>@endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="vent mt-14 text-ardoise-light" aria-hidden="true"></div>

        <div class="mt-6 flex flex-col gap-3 text-[12px] font-mono tracking-wide sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} {{ setting('company_name') }} · {{ setting('city') }}, {{ setting('country') }}</p>
            <p class="text-ardoise-light/80">{{ typo(setting('footer_note')) }}</p>
            <p><a href="{{ route('legal') }}" class="hover:text-white transition">Mentions légales</a></p>
        </div>
    </div>
</footer>
