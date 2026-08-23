(() => {
  const install = () => {
    const root = document.querySelector('.ce-nav-root');
    if (!root || root.querySelector('[data-ce-module="badge-studio"]')) return;
    const groups = [...root.querySelectorAll('.ce-nav-group')];
    const people = groups.find(g => /Pessoas/i.test(g.querySelector('summary')?.textContent || '')) || groups[0];
    const items = people?.querySelector('.ce-nav-items');
    if (!items) return;
    const a = document.createElement('a');
    a.className = 'ce-nav-link' + (new URLSearchParams(location.search).get('p') === 'badge-studio' ? ' active' : '');
    a.dataset.ceModule = 'badge-studio';
    a.href = '/dashboard?p=badge-studio';
    a.innerHTML = '<span class="ce-nav-icon"><i class="fa-solid fa-id-card"></i></span><span>Crachás & Modelos</span>';
    items.appendChild(a);
    if (new URLSearchParams(location.search).get('p') === 'badge-studio') people.open = true;
  };
  const observer = new MutationObserver(install);
  observer.observe(document.documentElement, {childList:true,subtree:true});
  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', install) : install();
  setTimeout(() => observer.disconnect(), 5000);
})();
