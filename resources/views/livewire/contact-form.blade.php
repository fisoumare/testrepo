<div>
    @if ($sent)
        {{-- ===== CONFIRMATION ===== --}}
        <div id="contact-confirmation" class="unit text-emerald-400/70 rounded-lg border border-white/10 bg-ink p-6 text-white rack-lines focus:outline-none sm:p-8" role="status" aria-live="polite" tabindex="-1">
            <p class="label flex items-center gap-2 text-emerald-400"><span class="led led-on"></span> Message enregistré</p>
            <h2 class="mt-4 text-2xl font-semibold tracking-tight sm:text-3xl text-balance">Merci {{ $name }}, votre message <span class="font-mono text-emerald-400">{{ $reference }}</span> est bien reçu.</h2>
            <p class="mt-4 leading-relaxed text-ardoise-light text-pretty">Nous vous répondons au plus tard le jour ouvré suivant, {{ setting('hours') }}. Un accusé de réception vient d'être envoyé à <span class="font-medium text-white">{{ $email }}</span>.</p>
            <dl class="spec spec-dark mt-6">
                <dt>Référence</dt><dd class="font-mono text-[13px]">{{ $reference }}</dd>
                <dt>Objet</dt><dd>{{ $subject }}</dd>
            </dl>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ $this->whatsappUrl() }}" target="_blank" rel="noopener" class="btn-primary">
                    <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.5l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 12 12 0 0 0 4.6 4 5.3 5.3 0 0 0 3.2.7 2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3Z"/></svg>
                    Poursuivre sur WhatsApp
                </a>
                <button type="button" wire:click="restart" class="btn-ghost-dark">Envoyer un autre message</button>
            </div>
        </div>
    @else
        {{-- ===== FORMULAIRE ===== --}}
        <form id="contact-formulaire" wire:submit="submit" novalidate class="relative grid gap-5 sm:grid-cols-2" aria-labelledby="formulaire-titre">
            <h2 id="formulaire-titre" class="sr-only">Formulaire de contact</h2>

            <x-ui.form-field name="name" label="Nom et prénom" required wire:model.live.blur="name" autocomplete="name" maxlength="120" />
            <x-ui.form-field name="organization" label="Organisation" wire:model.live.blur="organization" autocomplete="organization" maxlength="120" placeholder="Établissement, entreprise, administration…" />
            <x-ui.form-field name="email" type="email" label="Adresse e-mail" required wire:model.live.blur="email" autocomplete="email" inputmode="email" maxlength="190" />
            <x-ui.form-field name="phone" type="tel" label="Téléphone" wire:model.live.blur="phone" autocomplete="tel" inputmode="tel" maxlength="30" hint="De préférence un numéro joignable sur WhatsApp." />
            <x-ui.form-field name="subject" type="select" label="Objet" required wire:model.live="subject" :options="$subjects" :value="$subject" class="sm:col-span-2" />
            <x-ui.form-field name="message" type="textarea" label="Votre message" required wire:model.live.blur="message" :value="$message" class="sm:col-span-2" rows="7" maxlength="3000" hint="Décrivez votre besoin, votre contexte et vos délais. Entre 20 et 3 000 caractères." />

            {{-- Pot-de-miel : invisible pour les humains, doit rester vide. --}}
            <div class="absolute -left-[9999px] top-0 h-px w-px overflow-hidden" aria-hidden="true">
                <label for="site-web">Site web</label>
                <input id="site-web" type="text" name="website" tabindex="-1" autocomplete="off" wire:model="website">
            </div>

            @error('form')
                <p class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 sm:col-span-2" role="alert">{{ $message }}</p>
            @enderror

            <div class="flex flex-col gap-4 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                <button type="submit" class="btn-primary disabled:cursor-wait disabled:opacity-70" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Envoyer le message</span>
                    <span wire:loading wire:target="submit" class="inline-flex items-center gap-2"><span class="led led-on" aria-hidden="true"></span> Envoi en cours…</span>
                </button>
                <p class="text-xs leading-relaxed text-ardoise">Réponse au plus tard le jour ouvré suivant. Vos coordonnées servent uniquement à traiter votre demande.</p>
            </div>
        </form>
    @endif
</div>
