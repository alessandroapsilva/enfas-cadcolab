(() => {
    const sidebar = document.querySelector('.sidebar');
    const main = document.querySelector('.main-wrapper');
    const toggle = document.getElementById('sidebarToggle');
    if (!sidebar || !main || !toggle) return;

    const mobile = () => window.matchMedia('(max-width: 768px)').matches;
    const backdrop = document.createElement('div');
    backdrop.className = 'cadcolab-menu-backdrop';
    backdrop.setAttribute('aria-hidden', 'true');
    document.body.appendChild(backdrop);

    const apply = (open) => {
        if (mobile()) {
            sidebar.classList.toggle('mobile-open', open);
            sidebar.classList.remove('collapsed');
            main.classList.remove('expanded');
            backdrop.classList.toggle('visible', open);
        } else {
            sidebar.classList.toggle('collapsed', !open);
            main.classList.toggle('expanded', !open);
            localStorage.setItem('cadcolabV3MenuOpen', open ? '1' : '0');
        }
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    apply(mobile() ? false : localStorage.getItem('cadcolabV3MenuOpen') !== '0');
    toggle.addEventListener('click', () => apply(mobile() ? !sidebar.classList.contains('mobile-open') : sidebar.classList.contains('collapsed')));
    backdrop.addEventListener('click', () => apply(false));
    sidebar.querySelectorAll('a').forEach(link => link.addEventListener('click', () => { if (mobile()) apply(false); }));
    window.addEventListener('resize', () => apply(mobile() ? false : localStorage.getItem('cadcolabV3MenuOpen') !== '0'));

    document.querySelectorAll('form').forEach(form => form.addEventListener('submit', () => {
        if (form.dataset.submitting === 'true') return;
        form.dataset.submitting = 'true';
        document.body.classList.add('cadcolab-busy');
        window.setTimeout(() => { delete form.dataset.submitting; document.body.classList.remove('cadcolab-busy'); }, 12000);
    }));
    window.addEventListener('pageshow', () => {
        document.body.classList.remove('cadcolab-busy');
        document.querySelectorAll('form[data-submitting]').forEach(form => delete form.dataset.submitting);
    });
})();
