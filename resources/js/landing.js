// Entrypoint da landing (Blade em resources/views/landing.blade.php)
// Mantém estilos globais e pode conter JS leve específico da página.
import '../css/app.css';

// Exemplo: suaviza âncoras internas (se necessário para navegadores antigos)
document.addEventListener('click', (ev) => {
    const target = ev.target.closest('a[href^="#"]');
    if (!target) return;
    const id = target.getAttribute('href').slice(1);
    const el = document.getElementById(id);
    if (el) {
        ev.preventDefault();
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});

import '../css/app.css';
