import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Horloge de Conakry (GMT) affichée dans la barre d'état.
Alpine.data('clock', () => ({
    time: '',
    init() {
        const tick = () => {
            this.time = new Intl.DateTimeFormat('fr-FR', {
                hour: '2-digit', minute: '2-digit', timeZone: 'Africa/Conakry', hour12: false,
            }).format(new Date());
        };
        tick();
        setInterval(tick, 15000);
    },
}));

// Apparition progressive des blocs « .reveal » au défilement.
const revealObserver = ('IntersectionObserver' in window)
    ? new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        }
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 })
    : null;

const observeReveals = () => {
    document.querySelectorAll('.reveal:not(.is-visible)').forEach((el) => {
        if (revealObserver) revealObserver.observe(el); else el.classList.add('is-visible');
    });
};

document.addEventListener('DOMContentLoaded', observeReveals);
document.addEventListener('livewire:navigated', observeReveals);

// Après une réponse Livewire (changement d'étape, envoi refusé), défile jusqu'à un élément en
// tenant compte du header collant, puis lui donne le focus (clavier, lecteurs d'écran).
// Payload : { id, invalid } — avec « invalid », on vise le premier champ en erreur à l'intérieur de #id.
const stickyOffset = () => (document.querySelector('body > header')?.offsetHeight ?? 104) + 16;

Livewire.on('scroll-to', ({ id, invalid = false }) => {
    requestAnimationFrame(() => {
        const root = document.getElementById(id);
        if (!root) return;
        const target = (invalid && root.querySelector('[aria-invalid="true"], [role="alert"]')) || root;
        const top = Math.max(0, window.scrollY + target.getBoundingClientRect().top - stickyOffset());
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.scrollTo({ top, behavior: reduceMotion ? 'auto' : 'smooth' });
        target.focus({ preventScroll: true });
    });
});

Livewire.start();
