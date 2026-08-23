(() => {
  const VERSION='4.3.2';
  const q=(s,r=document)=>r.querySelector(s), qa=(s,r=document)=>[...r.querySelectorAll(s)];
  const page=()=>new URLSearchParams(location.search).get('p')||'dashboard';
  const esc=(v='')=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  function organizeSidebar(){
    const sidebar=q('.sidebar'), nav=q('.sidebar nav');
    if(!sidebar||!nav)return;
    sidebar.classList.add('ce-shell-sidebar');
    const footer=qa('.sidebar .mt-auto').at(-1);
    if(footer)footer.innerHTML=`<div class="ce-shell-version"><strong>CADCOLAB v${VERSION}</strong><span>Identity & People Suite</span><small>ENFAS • 2026</small></div>`;
    qa('.ce-nav-group').forEach(g=>g.open=!!g.querySelector('.ce-nav-link.active'));
    const root=q('.ce-nav-root');
    if(root&&!q('.ce-module-search')){
      const box=document.createElement('div');
      box.className='ce-module-search';
      box.innerHTML='<i class="fa-solid fa-magnifying-glass"></i><input type="search" placeholder="Buscar módulo…" autocomplete="off"><kbd>/</kbd>';
      root.before(box);
      const input=q('input',box);
      input?.addEventListener('input',()=>filterModules(input.value));
      document.addEventListener('keydown',e=>{if(e.key==='/'&&!/INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName||'')){e.preventDefault();input?.focus();}});
    }
  }

  function filterModules(value){
    const term=String(value||'').trim().toLocaleLowerCase('pt-BR');
    qa('.ce-nav-group').forEach(group=>{
      let visible=0;
      qa('.ce-nav-link',group).forEach(link=>{
        const show=!term||link.textContent.toLocaleLowerCase('pt-BR').includes(term);
        link.style.display=show?'':'none'; if(show)visible++;
      });
      group.style.display=visible?'':'none'; if(term&&visible)group.open=true;
    });
  }

  function setActive(){
    const current=page();
    qa('.ce-nav-link').forEach(link=>{
      try{const u=new URL(link.href,location.href);link.classList.toggle('active',u.searchParams.get('p')===current);}catch(_){ }
    });
    qa('.ce-nav-group').forEach(g=>{if(g.querySelector('.ce-nav-link.active'))g.open=true;});
  }

  function renderVersion(v){
    const items=(v.items||[]).map(i=>`<li><i class="fa-solid fa-check"></i><span>${esc(i)}</span></li>`).join('');
    return `<article class="ce-release-item ${esc(v.type||'feature')}"><div class="ce-release-rail"><span></span></div><div class="ce-release-card"><div class="ce-release-head"><div><span class="ce-release-version">v${esc(v.version)}</span><span class="ce-release-date">${esc(v.date)}</span></div>${v.type==='current'?'<span class="ce-release-badge">ATUAL</span>':''}</div><h3>${esc(v.title)}</h3><p>${esc(v.summary)}</p><ul>${items}</ul></div></article>`;
  }

  async function renderChangelog(){
    if(page()!=='changelog')return;
    const area=q('.content-area'); if(!area)return;
    try{
      const r=await fetch('/enterprise/changelog',{headers:{Accept:'application/json'},credentials:'same-origin'});
      if(!r.ok)throw new Error(`HTTP ${r.status}`);
      const d=await r.json(), versions=d.versions||[];
      area.innerHTML=`<section class="ce-release-hero"><div><span class="ce-release-kicker">RELEASE CENTER</span><h2>Histórico de evolução do CADCOLAB</h2><p>Versões, melhorias e correções da plataforma.</p></div><div class="ce-release-current"><small>VERSÃO ATUAL</small><strong>v${esc(d.current?.version||VERSION)}</strong><span>${esc(d.current?.date||'')}</span></div></section><div class="ce-release-timeline">${versions.map(renderVersion).join('')}</div>`;
    }catch(e){area.innerHTML=`<div class="alert alert-danger">Falha ao carregar as notas de versão: ${esc(e.message)}</div>`;}
  }

  function markReady(){
    document.documentElement.dataset.cadcolabVersion=VERSION;
    document.documentElement.dataset.cadcolabNavigation='safe';
    const t=(document.title||'CADCOLAB').replace(/\s*•\s*v?\d+(\.\d+)*/i,'');
    document.title=`${t} • v${VERSION}`;
  }

  document.readyState==='loading'?document.addEventListener('DOMContentLoaded',()=>{markReady();organizeSidebar();setActive();renderChangelog();}):(()=>{markReady();organizeSidebar();setActive();renderChangelog();})();
})();
