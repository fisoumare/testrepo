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

Livewire.start();
