(() => {
  const qs=(s,r=document)=>r.querySelector(s), esc=(v='')=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const p=new URLSearchParams(location.search).get('p')||'dashboard';

  async function json(url){const r=await fetch(url,{headers:{Accept:'application/json'}});if(!r.ok)throw new Error('HTTP '+r.status);return r.json();}
  function severityClass(s){return ['high','medium','low','ok'].includes(s)?s:'low';}

  function addInsightsNav(){
    const root=qs('.ce-nav-root'); if(!root||qs('[data-ce-insights-nav]'))return;
    const a=document.createElement('a'); a.dataset.ceInsightsNav='1'; a.className='ce-nav-link'; a.href='/dashboard?p=dashboard#governance-insights';
    a.innerHTML='<span class="ce-nav-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></span><span>Insights & Recomendações</span>';
    const home=root.querySelector('.ce-nav-home'); home?.insertAdjacentElement('afterend',a);
  }

  async function dashboardIntelligence(){
    if(p!=='dashboard')return; const area=qs('.content-area'); if(!area||qs('#governance-insights'))return;
    const shell=document.createElement('section'); shell.id='governance-insights'; shell.className='ce-intelligence-shell';
    shell.innerHTML='<div class="ce-intel-head"><div><span class="ce-eyebrow">INSIGHTS DE GOVERNANÇA</span><h3>Recomendações inteligentes</h3><p>Análise local dos indicadores do CADCOLAB, sem envio de dados para terceiros.</p></div><div class="ce-score" id="ceGovScore"><strong>—</strong><span>Score</span></div></div><div class="ce-intel-grid" id="ceInsightGrid"><div class="ce-intel-loading"><i class="fa-solid fa-circle-notch fa-spin"></i> Analisando ambiente...</div></div><div class="ce-kpi-grid" id="ceEnterpriseKpis"></div>';
    const heading=qs('.ce-dashboard-heading'); if(heading) heading.insertAdjacentElement('afterend',shell); else area.prepend(shell);
    try{
      const [ins,sum]=await Promise.all([json('/enterprise/insights'),json('/enterprise/reports/summary')]);
      qs('#ceGovScore').innerHTML=`<strong>${Number(ins.score||0)}</strong><span>Score / 100</span>`;
      qs('#ceInsightGrid').innerHTML=(ins.items||[]).map(x=>`<article class="ce-insight ${severityClass(x.severity)}"><div class="ce-insight-icon"><i class="fa-solid ${x.severity==='high'?'fa-triangle-exclamation':x.severity==='medium'?'fa-circle-exclamation':x.severity==='ok'?'fa-circle-check':'fa-lightbulb'}"></i></div><div><div class="ce-insight-meta">${esc(x.module||'governança')} · ${esc(x.severity||'')}</div><h4>${esc(x.title)}</h4><p>${esc(x.message)}</p><div class="ce-insight-action"><i class="fa-solid fa-arrow-right"></i> ${esc(x.action)}</div></div></article>`).join('');
      const k=[['directory_users','Identidades no diretório','fa-address-book'],['directory_groups','Grupos sincronizados','fa-users-gear'],['audit_30d','Eventos auditados / 30d','fa-shield-check'],['emails_30d','E-mails / 30d','fa-envelope'],['whatsapp_30d','WhatsApp / 30d','fa-comment-dots'],['errors_7d','Falhas / 7d','fa-bug']];
      qs('#ceEnterpriseKpis').innerHTML=k.map(([key,label,icon])=>`<div class="ce-kpi"><i class="fa-solid ${icon}"></i><div><strong>${Number(sum[key]||0)}</strong><span>${label}</span></div></div>`).join('');
    }catch(e){qs('#ceInsightGrid').innerHTML='<div class="alert alert-warning mb-0">Insights temporariamente indisponíveis. Verifique as rotas enterprise e os logs do Laravel.</div>';}
  }

  function csvDownload(name,rows){
    if(!rows.length)return; const keys=Object.keys(rows[0]); const q=v=>'"'+String(v??'').replaceAll('"','""')+'"';
    const data=[keys.map(q).join(';'),...rows.map(r=>keys.map(k=>q(r[k])).join(';'))].join('\n');
    const blob=new Blob(['\ufeff'+data],{type:'text/csv;charset=utf-8'}),a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=name;a.click();URL.revokeObjectURL(a.href);
  }

  async function enterpriseReports(){
    if(p!=='relatorios')return; const area=qs('.content-area'); if(!area||qs('#enterprise-reports'))return;
    const panel=document.createElement('section'); panel.id='enterprise-reports'; panel.className='ce-intelligence-shell ce-reports';
    panel.innerHTML='<div class="ce-intel-head"><div><span class="ce-eyebrow">ANALYTICS & COMPLIANCE</span><h3>Relatórios Enterprise</h3><p>Visão consolidada de identidade, auditoria, comunicação e falhas operacionais.</p></div><div class="ce-period"><button data-days="7">7d</button><button data-days="30" class="active">30d</button><button data-days="90">90d</button></div></div><div class="ce-report-summary" id="ceReportSummary"></div><div class="ce-report-columns"><div class="ce-report-card"><div class="ce-report-title"><span>Falhas por módulo</span><button id="ceExportErrors"><i class="fa-solid fa-file-csv"></i> CSV</button></div><div id="ceErrorsTable"></div></div><div class="ce-report-card"><div class="ce-report-title"><span>Ações de auditoria</span><button id="ceExportAudit"><i class="fa-solid fa-file-csv"></i> CSV</button></div><div id="ceAuditTable"></div></div><div class="ce-report-card"><div class="ce-report-title"><span>Comunicações por status</span><button id="ceExportEmails"><i class="fa-solid fa-file-csv"></i> CSV</button></div><div id="ceEmailsTable"></div></div></div>';
    area.prepend(panel); let current={};
    async function load(days){
      try{const [sum,op]=await Promise.all([json('/enterprise/reports/summary'),json('/enterprise/reports/operational?days='+days)]);current=op;
        const cards=[['directory_users','Identidades'],['audit_30d','Auditorias 30d'],['emails_30d','E-mails 30d'],['email_failures_30d','Falhas de e-mail'],['errors_7d','Erros 7d']];
        qs('#ceReportSummary').innerHTML=cards.map(([k,l])=>`<div><strong>${Number(sum[k]||0)}</strong><span>${l}</span></div>`).join('');
        const table=(rows,a,b)=>`<table class="ce-mini-table"><thead><tr><th>${a}</th><th>Total</th></tr></thead><tbody>${rows.length?rows.map(r=>`<tr><td>${esc(r[a.toLowerCase()]??r.modulo??r.acao??r.status)}</td><td>${Number(r.total||0)}</td></tr>`).join(''):'<tr><td colspan="2">Sem dados no período.</td></tr>'}</tbody></table>`;
        qs('#ceErrorsTable').innerHTML=table(op.errors_by_module||[],'Módulo'); qs('#ceAuditTable').innerHTML=table(op.audit_by_action||[],'Ação'); qs('#ceEmailsTable').innerHTML=table(op.emails_by_status||[],'Status');
      }catch(e){qs('#ceReportSummary').innerHTML='<div class="alert alert-warning">Não foi possível carregar os relatórios enterprise.</div>';}
    }
    panel.querySelectorAll('[data-days]').forEach(b=>b.addEventListener('click',()=>{panel.querySelectorAll('[data-days]').forEach(x=>x.classList.remove('active'));b.classList.add('active');load(b.dataset.days);}));
    qs('#ceExportErrors').onclick=()=>csvDownload('cadcolab-erros.csv',current.errors_by_module||[]); qs('#ceExportAudit').onclick=()=>csvDownload('cadcolab-auditoria.csv',current.audit_by_action||[]); qs('#ceExportEmails').onclick=()=>csvDownload('cadcolab-comunicacoes.csv',current.emails_by_status||[]); load(30);
  }

  function moduleCatalog(){
    if(p!=='configuracoes')return; const area=qs('.content-area'); if(!area||qs('#ce-module-catalog'))return;
    const panel=document.createElement('section');panel.id='ce-module-catalog';panel.className='ce-intelligence-shell';
    const mods=[['Identidade & LDAP','Diretório corporativo, autenticação e grupos','fa-address-card'],['Microsoft 365','Contas, perfis, telefone, estado e licenças','fa-brands fa-microsoft'],['Google Workspace','Provisionamento e ciclo de vida de contas','fa-brands fa-google'],['SSO & Aplicações','Catálogo e governança de aplicações','fa-window-restore'],['Comunicações','E-mail auditado e WhatsApp Cloud API','fa-comments'],['Automação','Jornada, onboarding e offboarding','fa-gears'],['Governança','Políticas, auditoria e conformidade','fa-shield-halved'],['Relatórios & Insights','Analytics, score e recomendações','fa-chart-line']];
    panel.innerHTML='<div class="ce-intel-head"><div><span class="ce-eyebrow">ARQUITETURA MODULAR</span><h3>Módulos da Plataforma</h3><p>Cada domínio evolui de forma independente, preservando compatibilidade com o CADCOLAB atual.</p></div></div><div class="ce-module-grid">'+mods.map(m=>`<div class="ce-module-tile"><i class="${m[2].startsWith('fa-brands')?m[2]:'fa-solid '+m[2]}"></i><div><strong>${esc(m[0])}</strong><span>${esc(m[1])}</span></div></div>`).join('')+'</div>';
    area.appendChild(panel);
  }

  document.addEventListener('DOMContentLoaded',()=>{setTimeout(addInsightsNav,50);dashboardIntelligence();enterpriseReports();moduleCatalog();});
})();
