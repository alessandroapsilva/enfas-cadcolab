(() => {
  const STORAGE_KEY = 'cadcolab.moduleShell.v1';
  const qs=(s,r=document)=>r.querySelector(s), qsa=(s,r=document)=>[...r.querySelectorAll(s)];
  let state={collapsed:false,open:null};
  try { state={...state,...JSON.parse(localStorage.getItem(STORAGE_KEY)||'{}')}; } catch(_) {}
  const save=()=>{try{localStorage.setItem(STORAGE_KEY,JSON.stringify(state));}catch(_){}};

  function init(){
    const app=qs('.app'), sidebar=qs('.sidebar'), backdrop=qs('.cms-backdrop');
    if(!app||!sidebar)return;

    const groups=qsa('.cms-nav-group');
    const activeGroup=groups.find(g=>qs('.nav-link.active',g));
    if(activeGroup) state.open=activeGroup.dataset.group||state.open;

    groups.forEach((group,index)=>{
      const trigger=qs('.cms-nav-trigger',group), body=qs('.cms-nav-body',group);
      if(!trigger||!body)return;
      const key=group.dataset.group||String(index);
      const shouldOpen=state.open===key || (!state.open&&index===0);
      group.classList.toggle('open',shouldOpen);
      trigger.setAttribute('aria-expanded',shouldOpen?'true':'false');
      trigger.addEventListener('click',()=>{
        const opening=!group.classList.contains('open');
        groups.forEach(other=>{
          if(other===group)return;
          other.classList.remove('open');
          qs('.cms-nav-trigger',other)?.setAttribute('aria-expanded','false');
        });
        group.classList.toggle('open',opening);
        trigger.setAttribute('aria-expanded',opening?'true':'false');
        state.open=opening?key:null; save();
      });
    });

    const applyCollapsed=()=>{
      app.classList.toggle('cms-collapsed',!!state.collapsed);
      const btn=qs('#cmsCollapse');
      btn?.setAttribute('aria-pressed',state.collapsed?'true':'false');
      btn?.setAttribute('title',state.collapsed?'Expandir menu':'Recolher menu');
      const icon=qs('i',btn); if(icon) icon.className=state.collapsed?'fa-solid fa-angles-right':'fa-solid fa-angles-left';
    };
    applyCollapsed();
    qs('#cmsCollapse')?.addEventListener('click',()=>{state.collapsed=!state.collapsed;save();applyCollapsed();});

    const openMobile=()=>{app.classList.add('cms-mobile-open');document.body.classList.add('cms-no-scroll');};
    const closeMobile=()=>{app.classList.remove('cms-mobile-open');document.body.classList.remove('cms-no-scroll');};
    qs('#cmsMobileMenu')?.addEventListener('click',openMobile);
    qs('#cmsCloseMobile')?.addEventListener('click',closeMobile);
    backdrop?.addEventListener('click',closeMobile);
    qsa('.sidebar .nav-link').forEach(a=>a.addEventListener('click',()=>{if(innerWidth<=980)closeMobile();}));
    addEventListener('keydown',e=>{if(e.key==='Escape')closeMobile();});

    const search=qs('#cmsSearch');
    if(search){
      search.addEventListener('input',()=>{
        const term=search.value.trim().toLocaleLowerCase('pt-BR');
        groups.forEach(group=>{
          let visible=0;
          qsa('.nav-link',group).forEach(link=>{
            const show=!term||link.textContent.toLocaleLowerCase('pt-BR').includes(term);
            link.hidden=!show; if(show)visible++;
          });
          group.hidden=!!term&&!visible;
          if(term&&visible){group.classList.add('open');qs('.cms-nav-trigger',group)?.setAttribute('aria-expanded','true');}
        });
      });
      document.addEventListener('keydown',e=>{
        if(e.key==='/'&&!/INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName||'')){e.preventDefault();search.focus();}
      });
    }
    document.documentElement.dataset.moduleShell='ready';
  }
  document.readyState==='loading'?document.addEventListener('DOMContentLoaded',init):init();
})();
