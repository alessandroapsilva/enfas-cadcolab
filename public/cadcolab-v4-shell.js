(() => {
  const KEY='cadcolab.v4.shell';
  const q=(s,r=document)=>r.querySelector(s), qa=(s,r=document)=>[...r.querySelectorAll(s)];
  let state={collapsed:false,open:null};
  try{state={...state,...JSON.parse(localStorage.getItem(KEY)||'{}')}}catch(_){ }
  const save=()=>localStorage.setItem(KEY,JSON.stringify(state));

  function applyCollapsed(){
    document.body.classList.toggle('v4-sidebar-collapsed',!!state.collapsed);
    const btn=q('#v4Collapse i');
    if(btn){btn.className=state.collapsed?'fa-solid fa-angles-right':'fa-solid fa-angles-left';}
  }

  function bindGroups(){
    const groups=qa('.v4-group');
    const active=groups.find(g=>g.querySelector('.v4-link.active'));
    if(active){groups.forEach(g=>g.open=g===active);state.open=active.querySelector('summary span')?.textContent||null;save();}
    else if(state.open){groups.forEach(g=>g.open=(g.querySelector('summary span')?.textContent===state.open));}
    groups.forEach(g=>g.addEventListener('toggle',()=>{
      if(!g.open)return;
      groups.forEach(other=>{if(other!==g)other.open=false;});
      state.open=g.querySelector('summary span')?.textContent||null;save();
    }));
  }

  function bindSearch(){
    const input=q('#v4ModuleSearch'); if(!input)return;
    input.addEventListener('input',()=>{
      const term=input.value.trim().toLocaleLowerCase('pt-BR');
      qa('.v4-group').forEach(group=>{
        let visible=0;
        qa('.v4-link',group).forEach(link=>{
          const show=!term||link.textContent.toLocaleLowerCase('pt-BR').includes(term);
          link.hidden=!show;if(show)visible++;
        });
        group.hidden=!visible;
        if(term&&visible)group.open=true;
      });
    });
    document.addEventListener('keydown',e=>{
      if(e.key==='/'&&!/INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName||'')){e.preventDefault();input.focus();}
      if(e.key==='Escape'){document.body.classList.remove('v4-mobile-open');input.blur();}
    });
  }

  function bindControls(){
    q('#v4Collapse')?.addEventListener('click',()=>{state.collapsed=!state.collapsed;save();applyCollapsed();});
    q('#v4MobileMenu')?.addEventListener('click',()=>document.body.classList.toggle('v4-mobile-open'));
    let overlay=q('#v4Overlay');
    if(!overlay){overlay=document.createElement('div');overlay.id='v4Overlay';document.body.appendChild(overlay);}
    overlay.addEventListener('click',()=>document.body.classList.remove('v4-mobile-open'));
  }

  function recoverBusyState(){
    const clear=()=>{
      document.body.classList.remove('loading','busy','ce-shell-busy');
      qa('[aria-busy="true"]').forEach(x=>x.removeAttribute('aria-busy'));
      qa('button[disabled][data-cadcolab-auto-disabled]').forEach(x=>{x.disabled=false;x.removeAttribute('data-cadcolab-auto-disabled')});
    };
    clear();window.addEventListener('pageshow',clear);
    document.addEventListener('submit',e=>{
      const form=e.target;if(!(form instanceof HTMLFormElement)||form.dataset.allowDoubleSubmit==='1')return;
      const submitter=e.submitter;
      if(submitter instanceof HTMLButtonElement){submitter.dataset.cadcolabAutoDisabled='1';submitter.disabled=true;setTimeout(()=>{submitter.disabled=false;submitter.removeAttribute('data-cadcolab-auto-disabled')},15000);}
    },true);
  }

  function boot(){applyCollapsed();bindGroups();bindSearch();bindControls();recoverBusyState();document.documentElement.dataset.cadcolabVersion='4.4.0';}
  document.readyState==='loading'?document.addEventListener('DOMContentLoaded',boot):boot();
})();
