(() => {
  const install = () => {
    const root = document.querySelector('.ce-nav-root');
    if (!root) return;

    const version = document.querySelector('.ce-shell-version strong');
    if (version) version.textContent = 'CADCOLAB v4.3.0';
    const edition = document.querySelector('.ce-shell-version span');
    if (edition) edition.textContent = 'Identity & People Experience';

    const groups = [...root.querySelectorAll('.ce-nav-group')];
    const people = groups.find(g => /Pessoas/i.test(g.querySelector('summary')?.textContent || '')) || groups[0];
    const items = people?.querySelector('.ce-nav-items');
    if (items && !root.querySelector('[data-ce-module="badge-studio"]')) {
      const a = document.createElement('a');
      a.className = 'ce-nav-link' + (new URLSearchParams(location.search).get('p') === 'badge-studio' ? ' active' : '');
      a.dataset.ceModule = 'badge-studio';
      a.href = '/dashboard?p=badge-studio';
      a.innerHTML = '<span class="ce-nav-icon"><i class="fa-solid fa-id-card"></i></span><span>Crachás & Modelos</span>';
      items.appendChild(a);
    }

    const automation = groups.find(g => /Automação & Integrações/i.test(g.querySelector('summary')?.textContent || ''));
    if (automation) {
      const label = automation.querySelector('summary span:nth-child(2)');
      if (label) label.textContent = 'Automação & Operações';
    }
    const governance = groups.find(g => /Governança & Auditoria/i.test(g.querySelector('summary')?.textContent || ''));
    if (governance) {
      const label = governance.querySelector('summary span:nth-child(2)');
      if (label) label.textContent = 'Governança & Compliance';
    }
    if (new URLSearchParams(location.search).get('p') === 'badge-studio' && people) people.open = true;
  };
  const observer = new MutationObserver(install);
  observer.observe(document.documentElement, {childList:true,subtree:true});
  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', install) : install();
  setTimeout(() => observer.disconnect(), 5000);
})();
