document.addEventListener('DOMContentLoaded', () => {
    
    // 1. ESCANEIA A TABELA E APLICA AS PÍLULAS DE STATUS COLORIDAS
    document.querySelectorAll('td').forEach(td => {
        let txt = td.innerText.trim().toUpperCase();
        let html = '';
        if(txt === 'ATIVO') html = '<span class="badge-top badge-ativo"><i class="fa-solid fa-check-circle"></i> Ativo</span>';
        else if(txt === 'PENDENTE') html = '<span class="badge-top badge-pendente"><i class="fa-solid fa-clock"></i> Pendente</span>';
        else if(txt === 'BLOQUEADO') html = '<span class="badge-top badge-bloqueado"><i class="fa-solid fa-lock"></i> Bloqueado</span>';
        else if(txt === 'INATIVO') html = '<span class="badge-top badge-inativo"><i class="fa-solid fa-user-slash"></i> Inativo</span>';
        else if(txt === 'DESLIGADO' || txt === 'DEMITIDO') html = '<span class="badge-top badge-desligado"><i class="fa-solid fa-user-times"></i> ' + txt + '</span>';
        
        if(html !== '') { td.innerHTML = html; }
    });

    // 2. ALINHA OS BOTÕES DE AÇÕES
    document.querySelectorAll('td:last-child').forEach(td => {
        let btns = td.querySelectorAll('button, a');
        if(btns.length > 0) {
            let group = document.createElement('div');
            group.className = 'action-group';
            btns.forEach(btn => {
                btn.classList.add('btn-acao-top');
                if(btn.innerHTML.includes('trash') || btn.innerHTML.includes('excluir')) btn.classList.add('btn-delete');
                else if(btn.innerHTML.includes('pen') || btn.innerHTML.includes('editar')) btn.classList.add('btn-edit');
                else btn.classList.add('btn-key');
                group.appendChild(btn.cloneNode(true));
            });
            td.innerHTML = ''; td.appendChild(group);
        }
    });

    // 3. O GRANDE TRUQUE: TRANSFORMA O SELECT DE STATUS EM CARDS PREMIUM
    let selects = document.querySelectorAll('select[name="status"]');
    selects.forEach(select => {
        select.style.display = 'none'; // Esconde o select feio original
        
        let container = document.createElement('div');
        container.className = 'status-cards-container';
        
        const options = [
            {val: 'ativo', title: 'Ativo', desc: 'Acesso corporativo concedido. Identidade sincronizada com a Microsoft e Google Workspace da clínica.', icon: 'fa-check', bg: 'linear-gradient(135deg, #10b981, #047857)'},
            {val: 'pendente', title: 'Pendente', desc: 'Aguardando ação do colaborador no portal de autoatendimento ou validação da gestão.', icon: 'fa-clock', bg: 'linear-gradient(135deg, #f59e0b, #b45309)'},
            {val: 'bloqueado', title: 'Bloqueado', desc: 'Credenciais suspensas por medidas de segurança. O acesso aos sistemas foi interrompido.', icon: 'fa-lock', bg: 'linear-gradient(135deg, #8b5cf6, #5b21b6)'},
            {val: 'inativo', title: 'Inativo / Afastado', desc: 'Colaborador em licença ou afastamento temporário. Acessos pausados preventivamente.', icon: 'fa-user-slash', bg: 'linear-gradient(135deg, #6b7280, #374151)'},
            {val: 'desligado', title: 'Desligado / Demitido', desc: 'Vínculo empregatício encerrado. Revogação permanente e irrevogável de todos os acessos.', icon: 'fa-user-times', bg: 'linear-gradient(135deg, #ef4444, #b91c1c)'}
        ];

        let currentValue = select.value.toLowerCase();
        
        options.forEach(opt => {
            // Verifica se a option original existe no select antes de desenhar o card
            let exists = Array.from(select.options).some(o => o.value.toLowerCase() === opt.val || o.text.toLowerCase().includes(opt.title.toLowerCase().split('/')[0].trim()));
            if(!exists) return; // Só desenha o card se a opção existir no seu código original

            let card = document.createElement('div');
            card.className = 'status-card' + (currentValue.includes(opt.val) ? ' selected' : '');
            card.innerHTML = `
                <div class="status-icon" style="background: ${opt.bg};"><i class="fa-solid ${opt.icon}"></i></div>
                <div class="status-info"><h4>${opt.title}</h4><p>${opt.desc}</p></div>
                <div class="status-radio-indicator"></div>
            `;
            
            card.addEventListener('click', () => {
                container.querySelectorAll('.status-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                
                // Encontra a opção correta no select invisível e marca como selecionada
                Array.from(select.options).forEach(o => {
                    if(o.value.toLowerCase() === opt.val || o.text.toLowerCase().includes(opt.title.toLowerCase().split('/')[0].trim())) {
                        select.value = o.value;
                    }
                });
            });
            container.appendChild(card);
        });
        
        select.parentNode.insertBefore(container, select.nextSibling);
    });

    // 4. EMBELEZA O BOTÃO DE SALVAR/CONFIRMAR DOS MODAIS
    document.querySelectorAll('.modal form button[type="submit"]').forEach(btn => {
        let text = btn.innerText.trim();
        btn.className = 'btn-confirm-lux';
        btn.innerHTML = `<i class="fa-solid fa-check-double"></i> Confirmar Alteração e Salvar`;
    });
});
