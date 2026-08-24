Warning: truncated output (original token count: 30413)
Total output lines: 1102

<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>CADCOLAB ENFAS IAM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* TEMA CORPORATE ENFAS (AZUL OFICIAL - SEM ROXO) */
        :root { --sidebar-bg: #111827; --sidebar-hover: #1f2937; --primary: #00a2e8; --bg-body: #F4F7FB; --bg-card: #ffffff; --text-primary: #1e293b; --text-muted: #64748b; --border-color: #e2e8f0; }
        [data-theme="dark"] { --sidebar-bg: #151720; --sidebar-hover: #1c1e29; --primary: #00a2e8; --bg-body: #1c1e29; --bg-card: #151720; --text-primary: #f8fafc; --text-muted: #94a3b8; --border-color: #2a2d3d; }
        
        body { background-color: var(--bg-body); color: var(--text-primary); font-family: 'Segoe UI',sans-serif; margin: 0; display: flex; min-height: 100vh; overflow-x: hidden; transition: background-color 0.3s;}
        
        .btn-primary { background-color: var(--primary) !important; border-color: var(--primary) !important; color: #fff !important; }
        .btn-primary:hover { filter: brightness(1.1); }
        .bg-primary { background-color: var(--primary) !important; }
        .text-primary { color: var(--primary) !important; }

        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: #f8fafc; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; border-right: 1px solid var(--border-color); overflow-y:auto; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);}
        .sidebar.collapsed { transform: translateX(-100%); }

        .sidebar-logo { padding: 25px 20px 10px; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-start; gap: 5px; }
        .sidebar-logo span.lg-main { color: #fff; font-weight: 900; font-size: 20px; letter-spacing: 1px;}
        .sidebar-logo span.lg-sub { color: var(--primary); font-weight: 900; font-size: 20px; letter-spacing: 1px;}
        .sidebar-logo .lg-mini { color: #8892b0; font-size: 9px; letter-spacing: 2px; }

        .nav-section { padding: 25px 20px 5px; font-size: 11px; color: #8892b0; font-weight: 700; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: 0.2s;}
        .nav-section:hover { color: #fff; }
        .nav-item { padding: 12px 20px; display: flex; align-items: center; gap: 12px; color: #cbd5e1; text-decoration: none; font-size: 14px; font-weight: 500; border-radius: 8px; margin: 4px 15px; transition: all 0.2s ease; overflow: hidden; white-space: nowrap;}
        .nav-item:hover { background-color: var(--sidebar-hover); color: #fff; transform: translateX(5px); }
        .nav-item.active { background-color: rgba(0, 162, 232, 0.1); color: var(--primary); border-left: 4px solid var(--primary); border-radius: 0 8px 8px 0; font-weight: 700;}

        .main-wrapper { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); max-width: calc(100% - 260px);}
        .main-wrapper.expanded { margin-left: 0; max-width: 100%; }
        
        .topbar { background: var(--bg-card); height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; border-bottom: 1px solid var(--border-color); }
        .content-area { padding: 30px 40px; flex: 1; }
        
        .table-card { background: var(--bg-card); border-radius: 12px; padding: 25px; border: 1px solid var(--border-color); margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .table { color: var(--text-primary); margin-bottom:0; width: 100%; }
        .table th { border-bottom: 2px solid var(--border-color); color: var(--text-muted); text-transform: uppercase; font-size: 11px; padding: 12px 10px; background: transparent; font-weight: 700; letter-spacing: 0.5px;}
        .table td { border-bottom: 1px solid var(--border-color); padding: 12px 10px; vertical-align: middle; background: transparent; color: var(--text-primary); font-size: 13px;}
        
        .conecta-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 25px 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; text-decoration: none; transition: 0.2s; height: 100%; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .conecta-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); border-color: var(--primary); }
        .conecta-icon { font-size: 35px; color: var(--primary); margin-bottom: 15px; background: rgba(0, 162, 232, 0.1); padding: 20px; border-radius: 12px;}

        /* LAYOUT DE CARDS NA TELA DE COLABORADORES */
        .user-card { background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-color); padding: 20px 20px 0 20px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: 0.2s; position: relative; border-left: 4px solid var(--primary);}
        .user-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.3); }
        .uc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed var(--border-color); }
        
        .uc-body { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 20px; }
        
        .uc-actions { display: flex; gap: 6px; justify-content: flex-end; align-items:center; background: rgba(0,0,0,0.2); padding: 10px 20px; border-radius: 0 0 12px 12px; border-top: 1px solid var(--border-color); margin: 0 -20px;}

        .btn-action-form { display: inline-block; margin: 0; padding: 0; }
        .btn-action { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-size: 13px; cursor: pointer; transition: all 0.2s ease; margin-left: 2px; padding: 0; color: var(--text-muted);}
        .btn-action:hover { transform: translateY(-2px); color: #fff !important; }
        
        .btn-a-history:hover { background: #64748b; border-color: #64748b; }
        .btn-a-sync:hover { background: var(--primary); border-color: var(--primary); }
        .btn-a-key:hover { background: #f59e0b; border-color: #f59e0b; }
        .btn-a-edit:hover { background: #10b981; border-color: #10b981; }
        .btn-a-del:hover { background: #ef4444; border-color: #ef4444; }
        .btn-a-badge:hover { background: var(--primary); border-color: var(--primary); }
        .btn-a-print:hover { background: #3b82f6; border-color: #3b82f6; }

        .modal-content { background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        .form-control, .form-select, textarea { background-color: var(--bg-body) !important; color: var(--text-primary) !important; border: 1px solid var(--border-color) !important; border-radius: 8px;}
        .form-control:focus, .form-select:focus, textarea:focus { background-color: var(--bg-card) !important; color: var(--text-primary) !important; border-color: var(--primary) !important; box-shadow: none !important;}
        
        .status-option { display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; border: 2px solid var(--border-color); border-radius: 10px; margin-bottom: 10px; cursor: pointer; background: var(--bg-body); transition: 0.2s; }
        .status-option:hover { border-color: var(--primary); }
        .status-dot { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #fff; margin-right: 15px;}
        
        .timeline { position: relative; padding-left: 30px; border-left: 2px solid var(--primary); margin-top:20px; text-align:left; }
        .timeline-item { position: relative; margin-bottom: 20px; }
        .timeline-item::before { content: ''; position: absolute; left: -36px; top: 0; width: 10px; height: 10px; border-radius: 50%; background: var(--primary); border: 2px solid var(--bg-body); }
        .timeline-date { font-size: 11px; color: var(--text-muted); margin-bottom: 3px; font-weight: bold; }
        .timeline-content { background: var(--bg-card); padding: 12px 15px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px; }
    </style>
    <link rel="stylesheet" href="/cadcolab-v3-plus.css?v=3.1.0">
    <link rel="stylesheet" href="/cadcolab-intelligence.css?v=3.1.0">
</head>
<body>

    @if(session('swal')) <script>Swal.fire({icon: 'success', title: 'Sucesso!', text: '{{ session('swal') }}', confirmButtonColor: '#00a2e8', background: 'var(--bg-card)', color: 'var(--text-primary)'});</script> @endif
    @if(session('swal_error')) <script>Swal.fire({icon: 'error', title: 'Atenção!', text: '{{ session('swal_error') }}', confirmButtonColor: '#ef4444', background: 'var(--bg-card)', color: 'var(--text-primary)'});</script> @endif
    @if(session('val_error')) <script>Swal.fire({icon: 'warning', title: 'Atenção, RH!', text: '{{ session('val_error') }}', confirmButtonColor: '#00a2e8', background: 'var(--bg-card)', color: 'var(--text-primary)'});</script> @endif

    <form id="formSendEmail" method="POST" action="/acao" style="display:none;">
        @csrf <input type="hidden" name="action" value="send_pwd_email">
        <input type="hidden" name="email_pessoal" id="fse_email">
        <input type="hidden" name="senha" id="fse_senha">
    </form>

    @if(session('senha_resetada'))
        <script>
            Swal.fire({
                title: '<i class="fa-solid fa-shield-check text-success fa-2x mb-3"></i><br>Senha Corporativa Resetada',
                html: `
                    <div id="printPwdArea" style="text-align:left; background:var(--bg-body); padding:20px; border-radius:8px; border:1px solid var(--border-color); margin-top:10px;">
                        <div style="text-align:center; margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                            <h2 style="color:var(--primary); margin:0; font-weight:900;">CADCOLAB ENFAS</h2>
                        </div>
                        <p style="margin:0 0 5px 0; font-size:13px; color:var(--text-muted);">Colaborador: <strong style="color:var(--text-primary);">{{ session('senha_resetada')['colab'] }}</strong></p>
                        <p style="margin:0 0 10px 0; font-size:12px; color:var(--text-muted);">Matrícula: <b>{{ session('senha_resetada')['matricula'] ?: 'N/A' }}</b> | Unidade: <b>{{ session('senha_resetada')['unidade'] }}</b></p>
                        <p style="margin:0 0 10px 0; font-size:13px; color:var(--text-muted);">Usuário Corporativo: <strong style="color:var(--primary);">{{ session('senha_resetada')['email'] }}</strong></p>
                        <p style="margin:0 0 15px 0; font-size:13px; color:var(--text-muted);">Status Sync (API): <strong style="color:#10b981;">{{ session('senha_resetada')['sync'] }}</strong></p>
                        
                        <div style="background:var(--bg-card); padding:15px; border-radius:6px; text-align:center; border:1px dashed var(--border-color);">
                            <span style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;">Nova Senha Temporária</span><br>
                            <b style="font-size:24px; color:#10b981; letter-spacing:2px; user-select:all;" id="copyPwd">{{ session('senha_resetada')['senha'] }}</b>
                        </div>
                        
                        <hr style="border-color:var(--border-color); margin:15px 0;">
                        <div style="font-size:11px; color:var(--text-muted); display:flex; justify-content:space-between; margin-bottom:5px;">
                            <span><i class="fa-solid fa-file-invoice"></i> Protocolo: <b>{{ session('senha_resetada')['protocolo'] }}</b></span>
                            <span><i class="fa-solid fa-headset"></i> Chamado: <b>{{ session('senha_resetada')['chamado'] }}</b></span>
                        </div>
                        <div style="font-size:11px; color:var(--text-muted); display:flex; justify-content:space-between; margin-bottom:5px;">
                            <span><i class="fa-solid fa-calendar-day"></i> Data: {{ session('senha_resetada')['data'] }}</span>
                            <span><i class="fa-solid fa-network-wired"></i> IP: {{ session('senha_resetada')['ip'] }}</span>
                        </div>
                        <div style="font-size:11px; color:var(--text-muted); text-align:center; margin-top:10px;">
                            <span>Operador Responsável: <b>{{ session('senha_resetada')['operador'] }}</b> ({{ session('senha_resetada')['forma'] }})</span>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:center; gap:10px; margin-top:20px;">
                        <button class="btn btn-outline-info btn-sm fw-bold" onclick="navigator.clipboard.writeText('{{ session('senha_resetada')['senha'] }}'); Swal.fire({toast:true, position:'top-end', showConfirmButton:false, timer:2000, icon:'success', title:'Copiado!'})"><i class="fa-solid fa-copy"></i> Copiar</button>
                        <button class="btn btn-outline-secondary btn-sm fw-bold" onclick="imprimirProtocolo()"><i class="fa-solid fa-print"></i> Protocolo</button>
                        <button class="btn btn-primary btn-sm fw-bold" onclick="document.getElementById('fse_email').value='{{ session('senha_resetada')['email_pessoal'] }}'; document.getElementById('fse_senha').value='{{ session('senha_resetada')['senha'] }}'; document.getElementById('formSendEmail').submit();"><i class="fa-solid fa-paper-plane"></i> Enviar P/ E-mail Pessoal</button>
                    </div>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:20px; line-height:1.4; text-align:left;">
                        <i class="fa-solid fa-triangle-exclamation text-warning"></i> <b>Segurança LGPD:</b> O sistema exigirá a troca no próximo login Microsoft/Google.
                    </div>
                `,
                background: 'var(--bg-card)', color: 'var(--text-primary)', showConfirmButton: false, width: 550
            });

            function imprimirProtocolo() {
                let conteudo = document.getElementById('printPwdArea').innerHTML;
                let janela = window.open('', '', 'width=800,height=600');
                janela.document.write('<html><head><title>Protocolo de Redefinição de Senha</title>');
                janela.document.write('<style>body { font-family: Arial, sans-serif; padding: 40px; color: #333; background: #fff; } #printPwdArea { border: 1px solid #ccc; padding: 30px; border-radius: 8px; } hr { border: 0; border-top: 1px solid #eee; margin: 20px 0; } </style>');
                janela.document.write('</head><body>');
                janela.document.write(conteudo);
                janela.document.write('<br><br><div style="text-align:center; margin-top:50px;">__________________________________________________<br><br>Assinatura do Solicitante</div>');
                janela.document.write('</body></html>');
                janela.document.close();
                janela.focus();
                setTimeout(() => { janela.print(); janela.close(); }, 500);
            }
        </script>
    @endif

    @if(session('exclusao_protocolo'))
        <script>
            Swal.fire({
                title: '<i class="fa-solid fa-trash-can text-danger fa-2x mb-3"></i><br>Exclusão Concluída',
                html: `
                    <div id="printDelArea" style="text-align:left; background:var(--bg-body); padding:20px; border-radius:8px; border:1px solid var(--border-color); margin-top:10px;">
                        <div style="text-align:center; margin-bottom:15px; border-bottom:1px solid var(--border-color); padding-bottom:10px;">
                            <h2 style="color:#ef4444; margin:0; font-weight:900;">TERMO DE EXCLUSÃO</h2>
                        </div>
                        <p style="margin:0 0 5px 0; font-size:13px; color:var(--text-muted);">Colaborador Apagado: <strong style="color:var(--text-primary);">{{ session('exclusao_protocolo')['colab'] }}</strong></p>
                        <p style="margin:0 0 10px 0; font-size:13px; color:var(--text-muted);">Motivo: <strong style="color:#ef4444;">{{ session('exclusao_protocolo')['motivo'] }}</strong></p>
                        
                        <hr style="border-color:var(--border-color); margin:15px 0;">
                        <div style="font-size:11px; color:var(--text-muted); display:flex; justify-content:space-between; margin-bottom:5px;">
                            <span><i class="fa-solid fa-file-invoice"></i> Protocolo: <b>{{ session('exclusao_protocolo')['protocolo'] }}</b></span>
                            <span><i class="fa-solid fa-headset"></i> Chamado: <b>{{ session('exclusao_protocolo')['chamado'] }}</b></span>
                        </div>
                        <div style="font-size:11px; color:var(--text-muted); display:flex; justify-content:space-between; margin-bottom:5px;">
                            <span><i class="fa-solid fa-calendar-day"></i> Data: {{ session('exclusao_protocolo')['data'] }}</span>
                            <span><i class="fa-solid fa-network-wired"></i> IP: {{ session('exclusao_protocolo')['ip'] }}</span>
                        </div>
                        <div style="font-size:11px; color:var(--text-muted); text-align:center; margin-top:10px;">
                            <span>Operador Responsável: <b>{{ session('exclusao_protocolo')['operador'] }}</b></span>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:center; gap:10px; margin-top:20px;">
                        <button class="btn btn-outline-secondary btn-sm fw-bold" onclick="imprimirProtocoloDel()"><i class="fa-solid fa-print"></i> Imprimir Protocolo</button>
                    </div>
                `,
                background: 'var(--bg-card)', color: 'var(--text-primary)', showConfirmButton: true, confirmButtonText: 'Fechar', confirmButtonColor: '#333', width: 550
            });

            function imprimirProtocoloDel() {
                let conteudo = document.getElementById('printDelArea').innerHTML;
                let janela = window.open('', '', 'width=800,height=600');
                janela.document.write('<html><head><title>Protocolo de Exclusão</title>');
                janela.document.write('<style>body { font-family: Arial, sans-serif; padding: 40px; color: #333; background: #fff; } #printDelArea { border: 1px solid #ccc; padding: 30px; border-radius: 8px; } hr { border: 0; border-top: 1px solid #eee; margin: 20px 0; } </style>');
                janela.document.write('</head><body>');
                janela.document.write(conteudo);
                janela.document.write('<br><br><div style="text-align:center; margin-top:50px;">__________________________________________________<br><br>Assinatura do Responsável (TI/RH)</div>');
                janela.document.write('</body></html>');
                janela.document.close();
                janela.focus();
                setTimeout(() => { janela.print(); janela.close(); }, 500);
            }
        </script>
    @endif

<div class="sidebar" id="cadcolabSidebar">
    <div class="text-center mb-4 mt-3">
        <a href="/dashboard" class="sidebar-logo">
            <div>
                <div><span class="lg-main">CADCOLAB</span><span class="lg-sub"> ENFAS</span></div>
                <div class="lg-mini">GESTÃO DE IDENTIDADES</div>
            </div>
        </a>
    </div>
    <nav class="text-start">
        <a href="/dashboard?p=dashboard" class="nav-item <?=($p=='dashboard'?'active':'')?>"><i class="fa-solid fa-house" style="width:20px;"></i> <span>Início</span></a>
        
        @if($isRH)
            <div class="nav-section mt-3" data-bs-toggle="collapse" data-bs-target="#menuRH"><span>Governança RH</span> <i class="fa-solid fa-chevron-down"></i></div>
            <div class="collapse <?=in_array($p, ['colaboradores','unidades','setores','cargos'])?'show':''?>" id="menuRH">
                <a href="/dashboard?p=colaboradores" class="nav-item <?=($p=='colaboradores'?'active':'')?>"><i class="fa-solid fa-users" style="width:20px;"></i> <span>Colaboradores</span></a>
                <a href="/dashboard?p=unidades" class="nav-item <?=($p=='unidades'?'active':'')?>"><i class="fa-solid fa-building" style="width:20px;"></i> <span>Unidades Corporativas</span></a>
                <a href="/dashboard?p=setores" class="nav-item <?=($p=='setores'?'active':'')?>"><i class="fa-solid fa-layer-group" style="width:20px;"></i> <span>Setores Organizacionais</span></a>
                <a href="/dashboard?p=cargos" class="nav-item <?=($p=='cargos'?'active':'')?>"><i class="fa-solid fa-briefcase" style="width:20px;"></i> <span>Cargos e Funções</span></a>
            </div>
        @endif
        @if($isAdmin)
            <div class="nav-section mt-3" data-bs-toggle="collapse" data-bs-target="#menuSeg"><span>Segurança e Automação</span> <i class="fa-solid fa-chevron-down"></i></div>
            <div class="collapse <?=in_array($p, ['grupos','robos'])?'show':''?>" id="menuSeg">
                <a href="/dashboard?p=grupos" class="nav-item <?=($p=='grupos'?'active':'')?>"><i class="fa-solid fa-user-lock" style="width:20px;"></i> <span>Perfis de Acesso (RBAC)</span></a>
                <a href="/dashboard?p=robos" class="nav-item <?=($p=='robos'?'active':'')?>"><i class="fa-solid fa-robot" style="width:20px;"></i> <span>Automação de Jornada</span></a>
            </div>
            
            <div class="nav-section mt-3" data-bs-toggle="collapse" data-bs-target="#menuRel"><span>Auditoria e Relatórios</span> <i class="fa-solid fa-chevron-down"></i></div>
            <div class="collapse <?=in_array($p, ['relatorios', 'auditoria'])?'show':''?>" id="menuRel">
                <a href="/dashboard?p=relatorios" class="nav-item <?=($p=='relatorios'?'active':'')?>"><i class="fa-solid fa-chart-pie" style="width:20px;"></i> <span>Central de Relatórios</span></a>
                <a href="/dashboard?p=auditoria" class="nav-item <?=($p=='auditoria'?'active':'')?>"><i class="fa-solid fa-file-shield" style="width:20px;"></i> <span>Logs ICP-Brasil</span></a>
            </div>

            <div class="nav-section mt-3" data-bs-toggle="collapse" data-bs-target="#menuTI"><span>TI & Sistemas Web</span> <i class="fa-solid fa-chevron-down"></i></div>
            <div class="collapse <?=in_array($p, ['sistemas','usuarios','configuracoes','erros', 'status'])?'show':''?>" id="menuTI">
                <a href="/dashboard?p=sistemas" class="nav-item <?=($p=='sistemas'?'active':'')?>"><i class="fa-solid fa-desktop" style="width:20px;"></i> <span>Aplicações Web SSO</span></a>
                <a href="/dashboard?p=usuarios" class="nav-item <?=($p=='usuarios'?'active':'')?>"><i class="fa-solid fa-user-shield" style="width:20px;"></i> <span>Administradores</span></a>
                <a href="/dashboard?p=configuracoes" class="nav-item <?=($p=='configuracoes'?'active':'')?>"><i class="fa-solid fa-sliders" style="width:20px;"></i> <span>Configurações Mestres</span></a>
                <a href="/dashboard?p=configuracoes#identity-directory-settings" class="nav-item"><i class="fa-solid fa-address-card" style="width:20px;"></i> <span>Identidade e Diretório (LDAP)</span></a>
                <a href="/dashboard?p=status" class="nav-item <?=($p=='status'?'active':'')?>"><i class="fa-solid fa-server" style="width:20px;"></i> <span>Status Cloud API</span></a>
                <a href="/dashboard?p=erros" class="nav-item <?=($p=='erros'?'active':'')?> text-danger"><i class="fa-solid fa-triangle-exclamation" style="width:20px;"></i> <span>Painel de Erros</span></a>
            </div>
            
            <div class="nav-section mt-3" data-bs-toggle="collapse" data-bs-target="#menuAjuda"><span>Ajuda e Suporte</span> <i class="fa-solid fa-chevron-down"></i></div>
            <div class="collapse <?=in_array($p, ['ajuda','changelog'])?'show':''?>" id="menuAjuda">
                <a href="/dashboard?p=ajuda" class="nav-item <?=($p=='ajuda'?'active':'')?> text-info"><i class="fa-solid fa-circle-question" style="width:20px;"></i> <span>Central de Ajuda</span></a>
                <a href="/dashboard?p=changelog" class="nav-item <?=($p=='changelog'?'active':'')?> text-success"><i class="fa-solid fa-code-branch" style="width:20px;"></i> <span>Notas de Versão</span></a>
            </div>
        @endif
    </nav>
    <div class="mt-auto p-3 text-center border-top" style="border-color: rgba(255,255,255,0.05) !important;">
        <div class="text-white text-xs" style="font-size:10px;">v3.0 Enterprise - 15 May 2026</div>
    </div>
</div>

<div class="main-wrapper">
    <div class="topbar">
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-link p-0 me-3 fs-4" style="color:var(--primary);" id="sidebarToggle" aria-label="Abrir ou recolher menu" aria-controls="cadcolabSidebar" aria-expanded="true"><i class="fa-solid fa-bars"></i></button>
            <h5 class="m-0 fw-bold" style="color:var(--text-primary); text-transform: capitalize;">{{ str_replace('_', ' ', $p) }}</h5>
        </div>
        <div class="d-flex align-items-center gap-3">
            <i class="fa-solid fa-moon theme-toggle" onclick="toggleTheme()" title="Modo Escuro"></i>
            <div class="d-flex align-items-center rounded-pill px-3 py-1 border" style="cursor:pointer; background:var(--bg-body); border-color:var(--border-color);" onclick="abrirModalSeguro('modalMeuPerfil')">
                <div class="text-end me-2" style="line-height:1.1;">
                    <span class="text-muted" style="font-size:10px; font-weight:bold; text-transform:uppercase;">{{ session('admin_perfil') }}</span><br>
                    <b style="color:var(--text-primary); font-size:13px;">{{ session('admin_nome') }}</b>
                </div>
                <i class="fa-solid fa-circle-user fa-2x" style="color:var(--primary);"></i>
            </div>
            <a href="/logout" class="btn btn-sm btn-danger px-4 rounded-pill fw-bold shadow-sm">Sair</a>
        </div>
    </div>
    <div class="content-area">

        <form id="actionForm" method="POST" action="/acao" style="display: none;">
            @csrf
            <input type="hidden" name="action" id="af_action">
            <input type="hidden" name="id" id="af_id">
            <input type="hidden" name="table" id="af_table">
        </form>

        <?php if($p == 'dashboard'): ?>
            <div style="background-color: #1c1e29; border: 1px solid var(--border-color); border-radius: 12px; padding: 35px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div>
                    <h3 class="fw-bold mb-2 text-white">Painel Administrativo de Identidades ENFAS</h3>
                    <p class="mb-0" style="color:#8892b0;">Governança de acessos automatizada com Microsoft 365 e Google Workspace integrados.</p>
                </div>
                <div class="text-center border-start border-light border-opacity-25 ps-4 ms-4">
                    <h2 class="text-white mb-0 fw-bold">{{ $dados['dias_implantacao'] ?? 0 }} Dias</h2>
                    <span style="color:var(--primary); font-size:12px; text-transform:uppercase; letter-spacing:1px; font-weight:bold;">De Governança Ativa</span>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3"><a href="/dashboard?p=colaboradores" class="conecta-card"><div class="conecta-icon"><i class="fa-solid fa-users"></i></div><h5 class="fw-bold mb-2" style="color:var(--text-primary);">Colaboradores (<?=$dados['tot']?>)</h5><p class="text-muted small mb-0">Gerencie o ciclo de vida e onboarding.</p></a></div>
                <div class="col-md-3"><a href="/dashboard?p=robos" class="conecta-card"><div class="conecta-icon"><i class="fa-solid fa-clock"></i></div><h5 class="fw-bold mb-2" style="color:var(--text-primary);">Automação de Jornada</h5><p class="text-muted small mb-0">Bloqueie acessos fora do horário.</p></a></div>
                <div class="col-md-3"><a href="/dashboard?p=grupos" class="conecta-card"><div class="conecta-icon"><i class="fa-solid fa-shield-halved"></i></div><h5 class="fw-bold mb-2" style="color:var(--text-primary);">Perfis de Acesso</h5><p class="text-muted small mb-0">Permissões a sistemas e Listas E-mail.</p></a></div>
                <div class="col-md-3"><a href="/dashboard?p=relatorios" class="conecta-card"><div class="conecta-icon"><i class="fa-solid fa-chart-column"></i></div><h5 class="fw-bold mb-2" style="color:var(--text-primary);">Auditoria e Relatórios</h5><p class="text-muted small mb-0">Logs de acesso com validade jurídica.</p></a></div>
            </div>
        
        <?php elseif($p == 'ajuda'): ?>
            <div class="table-card p-5">
                <h3 class="fw-bold text-primary mb-3"><i class="fa-solid fa-book-open-reader me-2"></i> Central de Ajuda CADCOLAB ENFAS</h3>
                <p class="text-muted">Bem-vindo à base de conhecimento oficial da plataforma de Governança de Identidades da ENFAS.</p>
                <hr class="border-secondary my-4">
                <h5 class="fw-bold text-info"><i class="fa-solid fa-cloud"></i> 1. Como funciona a Sincronização M365/Google?</h5>
                <p class="small text-muted mb-4">A sincronização é **em tempo real**. Sempre que um colaborador ativa a conta no Autoatendimento ou o RH "Salva a Ficha", o robô conecta nas APIs da Microsoft e Google, cria o e-mail oficial, define a senha aleatória, insere no Grupo Padrão e atribui a licença Business Basic (se selecionada).</p>
                <h5 class="fw-bold text-info"><i class="fa-solid fa-clock"></i> 2. Como a Catraca e a Jornada são bloqueadas?</h5>
                <p class="small text-muted mb-4">Os campos de "Início" e "Fim" ditam as regras. A base de dados do CadColab é espelhada com o sistema da catraca (Telemática/DIMEP). Fora do horário, a catraca é bloqueada e a sessão da Microsoft é revogada (Condicional Access).</p>
                <h5 class="fw-bold text-info"><i class="fa-solid fa-envelope-open-text"></i> 3. Como funciona a Injeção de Assinatura de E-mail?</h5>
                <p class="small text-muted mb-4">O sistema usa o template HTML configurado nas 'Configurações Mestres'. O robô troca as variáveis {NOME}, {CARGO}, {SETOR} pelos dados reais e injeta via Microsoft Graph API direto na caixa do usuário, impedindo adulteração.</p>
                <h5 class="fw-bold text-info"><i class="fa-solid fa-wand-magic-sparkles"></i> 4. O que é o Diagnóstico IA (Auto-Cura)?</h5>
                <p class="small text-muted mb-4">Em caso de erro HTTP 500, lentidão ou se um novo template HTML não carregar, vá em "Painel de Erros" e clique em "Diagnóstico IA e Auto-Cura". O sistema destrava os processos e limpa o cache do servidor automaticamente sem a necessidade de acessar o servidor Linux.</p>
            </div>
            
        <?php elseif($p == 'changelog'): ?>
            <div class="table-card p-5">
                <h3 class="fw-bold text-success mb-3"><i class="fa-solid fa-code-branch me-2"></i> Notas de Versão (Changelog)</h3>
                <div class="timeline mt-4">
                    <div class="timeline-item"><div class="timeline-date">15 de Maio de 2026 - v3.0 (Atual)</div><div class="timeline-content"><strong class="text-success d-block mb-1">Atualização Enterprise Final (UX, Validação e APIs)</strong><span class="text-muted small">- Funcionalidade "Atualizado por": Adicionado nome do operador na Ficha do Colaborador.<br>- Modal de Reset e Exclusão Avançados: Geração de Protocolo com Número do Chamado e Forma de Solicitação.<br>- JS Seguro: Fim de travamento de botões usando tags JSON ocultas e decodificação blindada.<br>- Banco Blindado (Fim do Erro 500): Conversão automática de campos em `NULL` silenciosamente antes de salvar.<br>- Nova Tela de Central de Relatórios Oficiais.<br>- E-mails Dinâmicos 100% controláveis pelo Painel de Configurações.<br>- Geração de Senha Aleatória Corporativa Segura (Enfas@ + 6 caracteres).</span></div></div>
                    <div class="timeline-item"><div class="timeline-date">10 de Maio de 2026 - v2.0</div><div class="timeline-content"><strong class="text-primary d-block mb-1">Integração Cloud Plena</strong><span class="text-muted small">- Microsoft Graph API integrada com sucesso.</span></div></div>
                </div>
            </div>

        <?php elseif($p == 'relatorios' && $isAdmin): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fa-solid fa-chart-bar text-primary me-2"></i> Central de Relatórios Oficiais</h4>
            </div>
            <div class="table-card">
                <p class="text-muted mb-4">Selecione o relatório desejado para auditoria e compliance trabalhista. Você pode exportar os dados em CSV (Excel) ou gerar um documento para impressão.</p>
                <div class="row g-4">
                    <div class="col-md-6"><div class="p-4 border rounded bg-body d-flex justify-content-between align-items-center"><div><h6 class="fw-bold text-primary mb-1"><i class="fa-solid fa-right-to-bracket me-2"></i> Login e Logout</h6><span class="small text-muted">Histórico de entradas e saídas no sistema.</span></div><div class="d-flex gap-2"><button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-csv"></i> CSV</button><button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-print"></i> Imprimir</button></div></div></div>
                    <div class="col-md-6"><div class="p-4 border rounded bg-body d-flex justify-content-between align-items-center"><div><h6 class="fw-bold text-primary mb-1"><i class="fa-solid fa-stopwatch me-2"></i> Tempo de Acesso</h6><span class="small text-muted">Duração total das sessões ativas (Jornada).</span></div><div class="d-flex gap-2"><button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-csv"></i> CSV</button><button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-print"></i> Imprimir</button></div></div></div>
                    <div class="col-md-6"><div class="p-4 border rounded bg-body d-flex justify-content-between align-items-center"><div><h6 class="fw-bold text-primary mb-1"><i class="fa-solid fa-users me-2"></i> Usuários</h6><span class="small text-muted">Relação completa de colaboradores IAM.</span></div><div class="d-flex gap-2"><button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-csv"></i> CSV</button><button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-print"></i> Imprimir</button></div></div></div>
                    <div class="col-md-6"><div class="p-4 border rounded bg-body d-flex justify-content-between align-items-center"><div><h6 class="fw-bold text-primary mb-1"><i class="fa-solid fa-calendar-alt me-2"></i> Turnos</h6><span class="small text-muted">Configurações de jornada e isenções.</span></div><div class="d-flex gap-2"><button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-csv"></i> CSV</button><button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-print"></i> Imprimir</button></div></div></div>
                    <div class="col-md-6"><div class="p-4 border rounded bg-body d-flex justify-content-between align-items-center"><div><h6 class="fw-bold text-primary mb-1"><i class="fa-solid fa-shield-halved me-2"></i> Permissões (RBAC)</h6><span class="small text-muted">Matriz de acessos a sistemas e grupos Cloud.</span></div><div class="d-flex gap-2"><button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-csv"></i> CSV</button><button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-print"></i> Imprimir</button></div></div></div>
                </div>
            </div>

        <?php elseif($p == 'auditoria' && $isTI): ?>
            <div class="table-card">
                <div class="alert alert-dark border-secondary mb-4 text-center">
                    <i class="fa-solid fa-certificate text-warning fa-2x mb-2"></i><br>
                    <b class="text-white">AUDITORIA PROTEGIDA ICP-BRASIL</b><br>
                    <span class="text-muted small">Eventos com hash de validade jurídica inalterável.</span>
                </div>
                <div class="table-responsive">
                    <table class="table small align-middle">
                        <thead><tr><th>Data / Hora (BRT)</th><th>Administrador</th><th>Ação Realizada</th><th>Hash de Controle</th></tr></thead>
                        <tbody>
                        @if(!empty($dados['lf'])) 
                            @foreach($dados['lf'] as $ra)
                            <tr><td style="white-space:nowrap;">{{ $ra['data_fmt'] }}</td><td><span class="badge bg-dark">{{ $ra['usuario_admin'] }}</span></td><td><b class="text-primary">{{ $ra['acao'] }}</b><br><span class="text-muted" style="font-size:11px;">{{ $ra['detalhes'] }}</span></td><td><span class="badge bg-secondary font-monospace">{{ $ra['codigo_controle'] }}</span></td></tr>
                            @endforeach 
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif($p == 'status' && $isTI): ?>
            <div class="d-flex justify-content-between align-items-center mb-4"><h4><i class="fa-solid fa-server text-primary me-2"></i> Status das Integrações Cloud</h4><button class="btn btn-primary" onclick="executarAcaoGlobal('reprocessar_erros', '', '', 'Forçar verificação de Sincronização nas Nuvens?')"><i class="fa-solid fa-rotate-right me-1"></i> Forçar Sincronização</button></div>
            <div class="row g-4">
                <div class="col-md-4"><div class="table-card h-100 d-flex align-items-center p-4" style="border-left: 4px solid #10b981;"><i class="fa-solid fa-database fa-3x text-primary me-4 opacity-75"></i><div><h5 class="fw-bold mb-1">Banco MySQL</h5><span class="badge bg-success-subtle text-success border border-success px-3 py-2 mt-2"><i class="fa-solid fa-circle-check"></i> Operacional</span></div></div></div>
                
                @php $m365_ok = $dados['api_status']['m365'] ?? false; $m365_msg = $dados['api_status']['m365_msg'] ?? 'Erro'; @endphp
                <div class="col-md-4"><div class="table-card h-100 d-flex align-items-center p-4" style="border-left: 4px solid {{ $m365_ok ? '#10b981' : '#ef4444' }};"><i class="fa-brands fa-microsoft fa-3x text-info me-4 opacity-75"></i><div><h5 class="fw-bold mb-1">Microsoft 365</h5><span class="badge bg-{{ $m365_ok ? 'success' : 'danger' }}-subtle text-{{ $m365_ok ? 'success' : 'danger' }} border border-{{ $m365_ok ? 'success' : 'danger' }} px-3 py-2 mt-2"><i class="fa-solid fa-{{ $m365_ok ? 'circle-check' : 'triangle-exclamation' }}"></i> {{ $m365_msg }}</span></div></div></div>
                
                @php $gw_ok = $dados['api_status']['google'] ?? false; $gw_msg = $dados['api_status']['google_msg'] ?? 'Erro'; @endphp
                <div class="col-md-4"><div class="table-card h-100 d-flex align-items-center p-4" style="border-left: 4px solid {{ $gw_ok ? '#10b981' : '#ef4444' }};"><i class="fa-brands fa-google fa-3x text-warning me-4 opacity-75"></i><div><h5 class="fw-bold mb-1">Google Workspace</h5><span class="badge bg-{{ $gw_ok ? 'success' : 'danger' }}-subtle text-{{ $gw_ok ? 'success' : 'danger' }} border border-{{ $gw_ok ? 'success' : 'danger' }} px-3 py-2 mt-2"><i class="fa-solid fa-{{ $gw_ok ? 'circle-check' : 'triangle-exclamation' }}"></i> {{ $gw_msg }}</span></div></div></div>

                @php $wa_ok = $dados['api_status']['whatsapp'] ?? false; $wa_msg = $dados['api_status']['whatsapp_msg'] ?? 'Erro'; @endphp
                <div class="col-md-4"><div class="table-card h-100 d-flex align-items-center p-4" style="border-left: 4px solid {{ $wa_ok ? '#10b981' : '#ef4444' }};"><i class="fa-brands fa-whatsapp fa-3x text-success me-4 opacity-75"></i><div><h5 class="fw-bold mb-1">WhatsApp API</h5><span class="badge bg-{{ $wa_ok ? 'success' : 'danger' }}-subtle text-{{ $wa_ok ? 'success' : 'danger' }} border border-{{ $wa_ok ? 'success' : 'danger' }} px-3 py-2 mt-2"><i class="fa-solid fa-{{ $wa_ok ? 'circle-check' : 'triangle-exclamation' }}"></i> {{ $wa_msg }}</span></div></div></div>

                @php $smtp_ok = $dados['api_status']['smtp'] ?? false; $smtp_msg = $dados['api_status']['smtp_msg'] ?? 'Erro'; @endphp
                <div class="col-md-4"><div class="table-card h-100 d-flex align-items-center p-4" style="border-left: 4px solid {{ $smtp_ok ? '#10b981' : '#ef4444' }};"><i class="fa-solid fa-envelope fa-3x text-primary me-4 opacity-75"></i><div><h5 class="fw-bold mb-1">Servidor SMTP</h5><span class="badge bg-{{ $smtp_ok ? 'success' : 'danger' }}-subtle text-{{ $smtp_ok ? 'success' : 'danger' }} border border-{{ $smtp_ok ? 'success' : 'danger' }} px-3 py-2 mt-2"><i class="fa-solid fa-{{ $smtp_ok ? 'circle-check' : 'triangle-exclamation' }}"></i> {{ $smtp_msg }}</span></div></div></div>
            </div>

        <?php elseif($p == 'robos' && $isTI): ?>
            <div class="d-flex justify-content-between align-items-center mb-4"><h4><i class="fa-solid fa-robot text-primary me-2"></i> Automação de Jornada e Robôs</h4></div>
            <div class="row g-4">
                <div class="col-md-6"><div class="table-card h-100" style="border-left:4px solid #10b981;"><h5 class="fw-bold mb-2">Robô de Jornada (Turnos)</h5><p class="small text-muted mb-3">Conecta as regras de horário (Início/Fim) configurados na Ficha com o banco SQL da catraca (Telemática).</p><span class="badge bg-success-subtle text-success border border-success px-3 py-2"><i class="fa-solid fa-circle-check"></i> Sincronização Ativa via SQL</span></div></div>
                <div class="col-md-6"><div class="table-card h-100" style="border-left:4px solid var(--primary);"><h5 class="fw-bold mb-2">Robô de Licenças Microsoft</h5><p class="small text-muted mb-3">Ao alterar os apps M365 (Teams, Outlook) na Ficha, faz uma chamada REST na API Graph para remover/adicionar a licença seletivamente.</p><span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2"><i class="fa-solid fa-rocket"></i> Gatilho Ativo (API REST)</span></div></div>
            </div>

        <?php elseif($p == 'erros' && $isTI): ?>
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="m-0 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> Logs de Erro e Diagnostics</h5>
                    <div>
                        <button type="button" class="btn btn-dark btn-sm me-2" onclick="executarAcaoGlobal('artisan_optimize', '', '', 'A IA irá limpar o cache do Laravel, compilar as views e reiniciar as rotas. Confirma a auto-cura?')"><i class="fa-solid fa-wand-magic-sparkles"></i> Diagnóstico IA e Auto-Cura</button>
                        <button type="button" class="btn btn-primary btn-sm me-2" onclick="executarAcaoGlobal('reprocessar_erros', '', '', 'O robô tentará reprocessar as falhas de sincronização na nuvem. Confirma?')"><i class="fa-solid fa-robot"></i> Reprocessar Falhas Cloud</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="executarAcaoGlobal('limpar_erros', '', '', 'Limpar painel de erros?')">Limpar Logs</button>
                    </div>
                </div>
                <div class="alert alert-info border-info mb-4">
                    <i class="fa-solid fa-robot fa-lg me-2"></i> <b>Soberano IA Ativo:</b> O monitoramento 24/7 está escaneando as APIs em busca de erros 400/500 e realizando correções automáticas de banco de dados. O sistema irá ignorar falhas de remoção de licenças inexistentes na Microsoft automaticamente.
                </div>
                <table class="table small">
                    <thead><tr><th>Data (BRT)</th><th>Módulo</th><th>Erro</th></tr></thead>
                    <tbody>@if(!empty($dados['ef'])) @foreach($dados['ef'] as $re) <tr><td style="white-space:nowrap;">{{ $re['data_fmt'] }}</td><td><b>{{ $re['modulo'] }}</b></td><td class="text-danger">{{ $re['mensagem'] }}</td></tr> @endforeach @endif</tbody>
                </table>
            </div>

        <?php elseif($p == 'colaboradores'): ?>
            <div class="d-flex justify-content-between mb-4">
                <form method="GET" class="d-flex gap-2" style="width:400px;"><input type="hidden" name="p" value="colaboradores"><input name="busca" class="form-control" placeholder="Pesquise nomes, CPFs ou Matrículas..." value="<?=htmlspecialchars($_GET['busca']??'')?>"><button type="submit" class="btn btn-primary fw-bold px-4">Buscar</button></form>
                <div>
                    <a href="/dashboard?p=colaboradores" class="btn btn-outline-secondary shadow-sm me-2 fw-bold" title="Recarregar Lista"><i class="fa-solid fa-rotate"></i></a>
                    <button class="btn btn-outline-success shadow-sm me-2 fw-bold" onclick="abrirModalSeguro('modalCSV')"><i class="fa-solid fa-file-csv me-1"></i> Importar CSV</button>
                    <button class="btn btn-primary shadow-sm fw-bold" onclick="novoColab()"><i class="fa-solid fa-plus me-1"></i> Novo Colaborador</button>
                </div>
            </div>
            
            <div class="row">
                @if(!empty($dados['colabs']) && count($dados['colabs']) > 0) 
                    @foreach($dados['colabs'] as $r) 
                        @php 
                            $id_exibicao = !empty($r['public_id']) ? $r['public_id'] : str_pad($r['id'], 6, '0', STR_PAD_LEFT); 
                            $hoje = date('Y-m-d'); $idade = '-'; 
                            if(!empty($r['data_nascimento']) && $r['data_nascimento'] != '0000-00-00') { $idade = (new DateTime($hoje))->diff(new DateTime($r['data_nascimento']))->y . ' anos'; } 
                            $nascimento_fmt = (!empty($r['data_nascimento']) && $r['data_nascimento'] != '0000-00-00') ? date('d/m/Y', strtotime($r['data_nascimento'])) : '-'; 
                            $bc = 'warning'; $ic='clock'; if($r['status'] == 'ativo'){ $bc = 'success'; $ic='check'; } elseif($r['status'] == 'bloqueado'){ $bc = 'danger'; $ic='ban'; } elseif($r['status'] == 'inativo'){ $bc = 'secondary'; $ic='bed'; } elseif(in_array($r['status'], ['desligado', 'demitido'])){ $bc = 'dark'; $ic='user-times'; }
                        @endphp
                        
                        <script type="application/json" id="json_colab_{{ $r['id'] }}">{!! json_encode($r) !!}</script>

                        <div class="col-md-12">
                            <div class="user-card" style="{{ empty($r['jornada_inicio']) || $r['jornada_inicio'] == '00:00:00' ? 'border-left-color: #ef4444;' : '' }}">
                                <div class="uc-header d-flex justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="uc-avatar d-flex align-items-center justify-content-center fw-bold fs-3 text-white shadow-sm" style="background:var(--bg-card); color:var(--text-primary) !important;">
                                            @if(!empty($r['foto_path'])) <img src="/{{ $r['foto_path'] }}" style="width:100%; height:100%; border-radius:50%; object-fit:cover;"> @else {{ strtoupper(substr($r['nome_completo'],0,2)) }} @endif
                                        </div>
                                        <div>
                                            <span class="badge bg-secondary font-monospace px-2">ID: {{ $id_exibicao }}</span>
                                            <span class="badge text-white font-monospace px-2 ms-1" style="background:var(--primary);">MAT: {{ $r['matricula']?:'N/A' }}</span>
                                            <h5 class="fw-bold mb-0 mt-1" style="color:var(--text-primary);">{{ $r['nome_completo'] }}</h5>
                                            <span class="text-muted font-monospace" style="font-size:11px;"><i class="fa-solid fa-id-card me-1"></i> {{ $r['cpf'] }} • Nasc: {{ $nascimento_fmt }} ({{ $idade }})</span>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-{{$bc}} fw-bold border-{{$bc}} px-4 py-2 rounded-pill shadow-sm text-uppercase" onclick="abrirModalStatus('{{ $r['id'] }}')" title="Alterar Status Rápido"><i class="fa-solid fa-{{$ic}} me-1"></i> {{ $r['status'] }}</button>
                                    </div>
                                </div>
                                
                                <div class="uc-body d-flex flex-wrap gap-4 mt-3 mb-3">
                                    <div class="flex-fill" style="min-width: 220px;">
                                        <span class="text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;"><i class="fa-solid fa-sitemap"></i> LOTAÇÃO E GESTÃO</span>
                                        <div class="mt-2 text-primary fw-bold" style="font-size: 13px;">{{ $r['un']?:'Sem Unidade' }} <span class="text-muted fw-normal">| {{ $r['seto']?:'Sem Setor' }}</span></div>
                                        <div class="text-muted mt-1" style="font-size: 12px;"><i class="fa-solid fa-briefcase"></i> {{ $r['crg']?:'Sem Cargo' }}</div>
                                        <div class="text-muted mt-1" style="font-size: 12px;"><i class="fa-solid fa-user-tie"></i> Gestor: <b style="color:var(--text-primary);">{{ $r['nome_gestor'] ?? 'Nenhum' }}</b></div>
                                    </div>
                                    
                                    <div class="flex-fill" style="min-width: 220px;">
                                        <span class="text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;"><i class="fa-solid fa-address-card"></i> DADOS PESSOAIS</span>
                                        <div class="mt-2 text-muted" style="font-size: 12px;">Admissão: <span style="color:var(--text-primary);" class="fw-bold">{{ $r['data_admissao_fmt'] ?? 'N/A' }}</span></div>
                                        <div class="text-muted mt-1" style="font-size: 12px;">Mãe: <span style="color:var(--text-primary);" class="fw-bold">{{ $r['nome_mae'] ?? 'N/A' }}</span></div>
                                        <div class="text-muted mt-1" style="font-size: 12px;">RG: <span class="text-info fw-bold">{{ $r['rg'] ?? 'N/A' }}</span></div>
                                    </div>

                                    <div class="flex-fill" style="min-width: 220px;">
                                        <span class="text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;"><i class="fa-solid fa-cloud"></i> CONTA CORPORATIVA</span>
                                        <div class="mt-2">
                                            @if($r['username_criado']) 
                                                <div class="text-info fw-bold" style="font-size: 13px;"><i class="fa-solid fa-envelope"></i> {{ $r['username_criado'] }}@enfas.com.br</div>
                                                <span class="badge border border-success text-success mt-1" style="background:var(--sidebar-bg);">CONECTADO</span>
                                            @else 
                                                <div class="text-warning fw-bold" style="font-size: 13px;"><i class="fa-solid fa-triangle-exclamation"></i> Conta Pendente</div>
                                                <span class="badge border border-warning text-warning mt-1" style="background:var(--sidebar-bg);">AGUARDANDO AUTOATEND.</span>
                                            @endif
                                            @if(empty($r['jornada_inicio']) || $r['jornada_inicio'] == '00:00:00') <span class="badge bg-danger text-white border mt-1"><i class="fa-solid fa-clock"></i> Sem Jornada Informada</span> @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex-fill" style="min-width: 200px;">
                                        <span class="text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;"><i class="fa-solid fa-shield-halved"></i> AUDITORIA DE CRIAÇÃO</span>
                                        <div class="mt-2 text-muted" style="font-size: 11px;">Criado: <span style="color:var(--text-primary);">{{ $r['criado_fmt'] ?? 'N/A' }}</span></div>
                                        <div class="text-muted mt-1" style="font-size: 11px;">Por: <span style="color:var(--text-primary);" class="text-uppercase">{{ $r['criado_por'] ?? 'Sistema' }}</span></div>
                                        <div class="text-muted mt-1" style="font-size: 11px;">Autoatend.: <span class="text-success">{{ $r['ativado_fmt'] ?? 'Pendente' }}</span></div>
                                    </div>
                                </div>

                                <div class="w-100 px-3 pb-2 mb-2 d-flex justify-content-between align-items-center" style="font-size: 10px; color: var(--text-muted); border-bottom: 1px solid var(--border-color);">
                                    <span>
                                    @if(!empty($r['atualizado_fmt']))
                                        <i class="fa-solid fa-clock-rotate-left"></i> Atualizado pela última vez em: <b>{{ $r['atualizado_fmt'] }}</b> por <b class="text-uppercase">{{ $r['atualizado_por'] ?? 'Sistema' }}</b>
                                    @else
                                        <i class="fa-solid fa-asterisk"></i> Apenas criação original.
                                    @endif
                                    </span>
                                </div>
                                
                                <div class="uc-actions">
                                    <button type="button" class="btn-action text-muted btn-a-history" onclick="abrirHistorico('{{ $r['id'] }}')" title="Timeline de Auditoria ICP-BR"><i class="fa-solid fa-clock-rotate-left"></i></button>
                                    
                                    @if($isTI && !empty($r['username_criado']))
                                        <button type="button" class="btn-action text-info btn-a-sync" onclick="executarAcaoGlobal('sync_assinatura', '{{ $r['id'] }}', 'pre_registros', 'Injetar assinatura corporativa M365 na caixa do usuário?')" title="Sincronizar Assinatura"><i class="fa-solid fa-pen-nib"></i></button>
                                        <button type="button" class="btn-action text-warning btn-a-key" onclick="confirmarResetSenha('{{ $r['id'] }}')" title="Resetar Senha Cloud"><i class="fa-solid fa-lock-open"></i></button>
                                    @endif
                                    
                                    <a href="/dashboard?p=cracha&id={{ $r['id'] }}" target="_blank" class="btn-action text-muted btn-a-badge" title="Gerar Crachá Oficial"><i class="fa-solid fa-id-badge"></i></a>
                                    <a href="/dashboard?p=ficha&id={{ $r['id'] }}" target="_blank" class="btn-action text-muted btn-a-print" title="Imprimir Ficha Completa"><i class="fa-solid fa-file-lines"></i></a>
                                    
                                    <button type="button" class="btn-action text-success btn-a-edit" onclick="editarColab('{{ $r['id'] }}')" title="Editar Ficha IAM"><i class="fa-solid fa-pen"></i></button>
                                    
                                    @if($isTI) 
                                        <button type="button" class="btn-action text-danger btn-a-del" onclick="confirmarExclusao('{{ $r['id'] }}')" title="Excluir Colaborador"><i class="fa-solid fa-trash"></i></button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else 
                    <div class="col-12 text-center py-5 text-muted">Nenhum colaborador encontrado com os filtros informados.</div> 
                @endif
            </div>
            
        <?php elseif(in_array($p, ['unidades','setores','cargos','perfis','sistemas', 'usuarios', 'grupos'])): $t = $p; if($p == 'sistemas') $t = 'sistemas_hc'; if($p == 'perfis' || $p == 'grupos') $t = 'perfis_acesso'; if($p == 'usuarios') $t = 'usuarios_admin'; $titulo = ucfirst($p); ?>
            <div class="d-flex justify-content-between mb-4"><h4><?=$titulo?></h4><button class="btn btn-primary fw-bold shadow-sm" onclick="<?=($p=='cargos'?'openCargo()':(($p=='perfis'||$p=='grupos')?'openPerfil()':"openGeneric('$t')"))?>"><i class="fa-solid fa-plus me-1"></i> Novo Registro</button></div>
            <div class="table-card table-responsive"><table class="table align-middle"><thead><tr><th>ID</th><th>Descrição</th><th class="text-end">Ações</th></tr></thead><tbody>
            @if(!empty($dados['regs'])) 
                @foreach($dados['regs'] as $r) 
                    <tr><td><span class="badge bg-secondary font-monospace text-white">#{{ $r['id'] }}</span></td><td><b>{{ $r['nome'] ?? $r['usuario'] }}</b> @if(isset($r['email'])) <div class='small mt-1' style='color:var(--text-muted);'><i class='fa-solid fa-envelope me-1'></i>{{ $r['email'] }} <span class='badge bg-primary-subtle text-primary border ms-2'>{{ $r['perfil'] ?? '' }}</span></div> @endif</td><td class="text-end"><div class="d-inline-flex gap-1 align-items-center justify-content-end">
                    <script type="application/json" id="data_reg_{{ $t }}_{{ $r['id'] }}">{!! json_encode($r) !!}</script>
                    <button class="btn-action btn-a-edit text-success" onclick="{{ $p=='cargos'?'editCargo('.$r['id'].')':(($p=='perfis'||$p=='grupos')?'editPerfil('.$r['id'].')':"editGeneric('".$t."', ".$r['id'].")") }}"><i class="fa-solid fa-pen"></i></button><button type="button" class="btn-action btn-a-del text-danger" onclick="executarAcaoGlobal('delete', '{{ $r['id'] }}', '{{ $t }}', 'Excluir definitivamente este registro?')"><i class="fa-solid fa-trash"></i></button></div></td></tr>
                @endforeach 
            @endif
            </tbody></table></div>
     …413 tokens truncated…></i>Microsoft 365 (Entra ID)</h6>@foreach(['m365_tenant'=>'Tenant ID','m365_client'=>'Client ID','m365_secret'=>'Secret (Client Secret)','m365_sku_basic'=>'SKU ID (Basic)'] as $k=>$l) <div class="mb-3"><label class="small fw-bold text-muted">{{ $l }}</label><input name="cfg[{{ $k }}]" value="{{ $cfg_global[$k] ?? '' }}" class="form-control"></div> @endforeach
                                <label class="small fw-bold text-muted">Grupo Padrão M365 (API Graph)</label><select name="cfg[m365_grupo_padrao]" id="cfg_m365_grupo_padrao" class="form-select mb-3" data-current="{{ $cfg_global['m365_grupo_padrao'] ?? '' }}"><option value="">Carregando API...</option></select></div></div>
                                <div class="col-md-6">
                                    <div class="p-4 border rounded mb-4" style="background:var(--bg-body);"><h6 class="fw-bold mb-3" style="color:#25D366;"><i class="fa-brands fa-whatsapp d-inline me-2" style="color:#25D366;"></i>WhatsApp API (Meta)</h6>@foreach(['wp_token'=>'Access Token Permanente','wp_phone_id'=>'Phone Number ID', 'wp_template'=>'Nome do Template de Disparo (OTP)'] as $k=>$l) <div class="mb-3"><label class="small fw-bold text-muted">{{ $l }}</label><input name="cfg[{{ $k }}]" value="{{ $cfg_global[$k] ?? '' }}" class="form-control"></div> @endforeach</div>
                                    <div class="p-4 border rounded" style="background:var(--bg-body);"><h6 class="fw-bold mb-3 text-danger"><i class="fa-solid fa-envelope d-inline me-2" style="color:#ea4335;"></i>Servidor SMTP Corporativo</h6><div class="row g-2">@foreach(['smtp_host'=>'Host SMTP (Ex: smtp.office365.com)','smtp_port'=>'Porta','smtp_user'=>'Usuário','smtp_pass'=>'Senha','mail_from'=>'E-mail do Remetente Oficial', 'mail_from_name'=>'Nome do Remetente Oficial (Ex: ENFAS IAM)'] as $k=>$l) <div class="{{ ($k=='smtp_host'||$k=='mail_from'||$k=='mail_from_name')?'col-md-12':'col-md-6' }} mb-2"><label class="small fw-bold text-muted">{{$l}}</label><input name="cfg[{{$k}}]" value="{{ $cfg_global[$k] ?? '' }}" class="form-control"></div> @endforeach</div></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="cfg_google">
                            <div class="row g-4"><div class="col-md-12"><div class="p-4 border rounded" style="background:var(--bg-body);"><h6 class="fw-bold mb-3 text-warning"><i class="fa-brands fa-google me-2"></i>Google Workspace (Directory API)</h6><p class="text-muted small mb-4">Para provisionar contas automáticas no Google, é necessária uma Service Account JSON com Domain-Wide Delegation ativa no G Suite.</p><div class="row g-3"><div class="col-md-12 mb-2"><label class="small fw-bold text-muted">Domínio Principal (ex: enfas.com.br)</label><input name="cfg[gw_domain]" value="{{ $cfg_global['gw_domain'] ?? '' }}" class="form-control"></div><div class="col-md-12 mb-2"><label class="small fw-bold text-muted">Service Account Key (Cole todo o JSON aqui)</label><textarea name="cfg[gw_json]" class="form-control font-monospace" rows="8">{{ $cfg_global['gw_json'] ?? '' }}</textarea></div></div></div></div></div>
                        </div>
                        <div class="tab-pane fade" id="cfg_iam">
                            <div class="row g-4">
                                <div class="col-md-6"><div class="p-4 border rounded h-100" style="background:var(--bg-body);"><h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-shield-halved d-inline me-2"></i>Políticas de Segurança e Senha</h6><label class="fw-bold text-primary mb-2">Dias para expiração da Senha (M365)</label><input type="number" name="cfg[dias_expiracao]" value="{{ $cfg_global['dias_expiracao'] ?? '90' }}" class="form-control mb-3"></div></div>
                                <div class="col-md-6"><div class="p-4 border rounded h-100" style="background:var(--bg-body);"><h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-users d-inline me-2"></i>Padrões de Cadastro (Robôs)</h6><label class="fw-bold text-primary mb-2">Jornada de Trabalho Padrão (Entrada - Saída)</label><div class="input-group mb-3"><input type="time" name="cfg[jornada_padrao_entrada]" value="{{ $cfg_global['jornada_padrao_entrada'] ?? '08:00' }}" class="form-control"><span class="input-group-text bg-dark border-secondary text-white">às</span><input type="time" name="cfg[jornada_padrao_saida]" value="{{ $cfg_global['jornada_padrao_saida'] ?? '18:00' }}" class="form-control"></div><label class="fw-bold text-primary mb-2">Grupo de Acesso (Perfil) Padrão</label>
                                <select name="cfg[perfil_padrao_id]" class="form-select mb-3"><option value="">Nenhum</option>@foreach(DB::table('perfis_acesso')->get() as $pp)<option value="{{$pp->id}}" {{ (isset($cfg_global['perfil_padrao_id']) && $cfg_global['perfil_padrao_id'] == $pp->id) ? 'selected' : '' }}>{{$pp->nome}}</option>@endforeach</select><label class="fw-bold text-primary mb-2">Aviso na Tela de Login do IAM</label><input type="text" name="cfg[aviso_login]" value="{{ $cfg_global['aviso_login'] ?? '' }}" class="form-control mb-3" placeholder="Mensagem de alerta..."></div></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="cfg_print">
                            <div class="row g-4">
                                <div class="col-md-6"><div class="p-4 border rounded h-100" style="background:var(--bg-body);"><label class="fw-bold text-primary mb-2">Assinatura de E-mail Institucional M365</label><p class="small text-muted mb-2">Variáveis: {NOME}, {CARGO}, {SETOR}, {TELEFONE}, {EMAIL}</p><textarea name="cfg[tpl_assinatura]" class="form-control font-monospace" rows="12" placeholder="HTML da Assinatura...">{{ $cfg_global['tpl_assinatura'] ?? '' }}</textarea><label class="fw-bold text-primary mb-2 mt-4">Termo de Uso e Diretrizes da Empresa (HTML)</label><textarea name="cfg[termo_uso_html]" class="form-control font-monospace" rows="8">{{ $cfg_global['termo_uso_html'] ?? '<p>O acesso aos sistemas corporativos é concedido exclusivamente para o desempenho de suas funções.</p>' }}</textarea></div></div>
                                <div class="col-md-6"><div class="p-4 border rounded h-100" style="background:var(--bg-body);"><label class="fw-bold text-primary mb-2">E-mail HTML de Recuperação (Link Seguro)</label><p class="small text-muted mb-2">Variáveis: {USUARIO}, {LINK}, {EMAIL_CORP}</p><textarea name="cfg[tpl_email]" class="form-control font-monospace" rows="8">{{ $cfg_global['tpl_email'] ?? "" }}</textarea><label class="fw-bold text-primary mb-2 mt-4">E-mail HTML de Envio da Nova Senha (Reset TI)</label><p class="small text-muted mb-2">Variáveis: {SENHA}</p><textarea name="cfg[tpl_email_senha]" class="form-control font-monospace" rows="8">{{ $cfg_global['tpl_email_senha'] ?? "" }}</textarea></div></div>
                                <div class="col-md-12"><div class="p-4 border rounded" style="background:var(--bg-body);"><label class="fw-bold text-primary mb-2"><i class="fa-solid fa-id-card me-1"></i> HTML do Crachá (Variáveis: {NOME}, {CARGO}, {SETOR}, {ID}, {FOTO})</label><textarea name="cfg[tpl_cracha]" class="form-control font-monospace" rows="6">{{ $cfg_global['tpl_cracha'] ?? '' }}</textarea></div></div>
                                <div class="col-md-12"><div class="p-4 border rounded" style="background:var(--bg-body);"><label class="fw-bold text-primary mb-2"><i class="fa-solid fa-file-lines me-1"></i> HTML da Ficha Cadastral</label><textarea name="cfg[tpl_ficha]" class="form-control font-monospace" rows="6">{{ $cfg_global['tpl_ficha'] ?? '' }}</textarea></div></div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-lg mt-4 w-100 fw-bold shadow">Salvar Todas as Configurações</button>
                </div>
            </form>
        @endif
    </div>
</div>

<div class="modal fade" id="modalEdicao" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" action="/acao" enctype="multipart/form-data" id="formFicha" onsubmit="return validarFicha(event);">
                @csrf 
                <input type="hidden" name="action" value="save_colab">
                <input type="hidden" name="id" id="e_i">
                
                <div class="modal-header bg-primary text-white">
                    <div>
                        <h4 class="fw-bold m-0" id="m_e_title">Adicionar Novo Colaborador (IAM)</h4>
                        <div id="info_atualizacao" style="display:none; font-size:11px; margin-top:5px; color:rgba(255,255,255,0.8);"></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="p-3" style="background:var(--bg-card); border-bottom: 1px solid var(--border-color);">
                    <ul class="nav nav-tabs border-0" role="tablist">
                        <li class="nav-item"><button class="nav-link active border-0 rounded bg-light text-primary fw-bold me-2 px-4 py-2" data-bs-toggle="tab" data-bs-target="#pessoal" type="button">1. Dados Pessoais</button></li>
                        <li class="nav-item"><button class="nav-link border-0 rounded bg-light text-primary fw-bold me-2 px-4 py-2" data-bs-toggle="tab" data-bs-target="#corp" type="button">2. Dados Corporativos</button></li>
                        <li class="nav-item"><button class="nav-link border-0 rounded bg-light text-primary fw-bold me-2 px-4 py-2" data-bs-toggle="tab" data-bs-target="#sist" type="button">3. Jornada e Robôs (IAM)</button></li>
                        <li class="nav-item"><button class="nav-link border-0 rounded bg-light text-primary fw-bold px-4 py-2" data-bs-toggle="tab" data-bs-target="#ferias" type="button">4. Férias e Anotações</button></li>
                    </ul>
                </div>
                <div class="modal-body tab-content p-4" style="background:var(--bg-body);">
                    <div class="tab-pane fade show active" id="pessoal">
                        <div class="row g-4">
                            <div class="col-md-2"><label class="fw-bold mb-1 text-muted small">ID Único</label>
                                <input type="text" name="public_id" id="e_pubid" class="form-control form-control-lg fw-bold font-monospace text-center bg-primary bg-opacity-10 text-primary border-0" readonly>
                                <div style="font-size:9px; color:#ef4444; margin-top:4px; text-align:center;">Identificação do colaborador em toda instituição, catracas e nuvem</div>
                            </div>
                            <div class="col-md-5"><label class="fw-bold mb-1 text-warning small">Nome Completo *</label><input type="text" name="nome" id="e_n" class="form-control form-control-lg"></div>
                            <div class="col-md-5"><label class="fw-bold mb-1 text-warning small">Nome da Mãe *</label><input type="text" name="nome_mae" id="e_mae" class="form-control form-control-lg"></div>
                            <div class="col-md-3"><label class="fw-bold mb-1 text-warning small">CPF *</label><input type="text" name="cpf" id="e_c" class="form-control form-control-lg"></div>
                            <div class="col-md-3"><label class="fw-bold mb-1 text-warning small">Data de Nascimento *</label><input type="date" name="nasc" id="e_d" class="form-control form-control-lg"></div>
                            <div class="col-md-3"><label class="fw-bold mb-1 text-muted small">RG</label><input type="text" name="rg" id="e_rg" class="form-control form-control-lg" placeholder="Documento Identidade"></div>
                            <div class="col-md-3"><label class="fw-bold mb-1 text-muted small">Celular / WhatsApp</label><input type="text" name="telefone" id="e_tel" class="form-control form-control-lg"></div>
                            <div class="col-md-6"><label class="fw-bold mb-1 text-muted small">E-mail Pessoal</label><input type="email" name="email_pessoal" id="e_mal" class="form-control form-control-lg"></div>
                            <div class="col-md-6"><label class="fw-bold mb-1 text-muted small">Foto de Perfil</label><input type="file" name="foto" accept="image/*" capture="user" class="form-control form-control-lg text-dark bg-white"></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="corp">
                        <div class="row g-4">
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Matrícula (Folha)</label><input type="text" name="matricula" id="e_m" class="form-control form-control-lg"></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Data Admissão</label><input type="date" name="data_admissao" id="e_dadm" class="form-control form-control-lg"></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Status Inicial</label><select name="status" id="e_s" class="form-select form-select-lg"><option value="ativo">Ativo</option><option value="pendente">Pendente</option><option value="inativo">Inativo</option><option value="bloqueado">Bloqueado</option><option value="desligado">Desligado</option><option value="demitido">Demitido</option></select></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Unidade Corporativa</label><select name="unidade" id="e_u" class="form-select form-select-lg"><option value="">Nenhuma</option>@foreach(DB::table('unidades')->get() as $u)<option value="{{$u->id}}">{{$u->nome}}</option>@endforeach</select></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Setor</label><select name="setor" id="e_t" class="form-select form-select-lg"><option value="">Nenhum</option>@foreach(DB::table('setores')->get() as $s)<option value="{{$s->id}}">{{$s->nome}}</option>@endforeach</select></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Cargo IAM (Define Perfil de Acesso)</label><select name="cargo" id="e_g" class="form-select form-select-lg"><option value="">Nenhum</option>@foreach(DB::table('cargos')->get() as $c)<option value="{{$c->id}}">{{$c->nome}}</option>@endforeach</select></div>
                            <div class="col-md-6"><label class="fw-bold mb-1 text-muted small">Gestor Imediato (Aprovações)</label><select name="gestor_id" id="e_gestor" class="form-select form-select-lg"><option value="">Nenhum</option>@foreach(DB::table('pre_registros')->where('status','ativo')->orderBy('nome_completo')->get() as $g)<option value="{{$g->id}}">{{$g->nome_completo}}</option>@endforeach</select></div>
                            <div class="col-md-6">
                                <label class="fw-bold mb-1 text-primary small"><i class="fa-solid fa-cloud me-1"></i>Conta Cloud Automática (Identidade)</label>
                                <div class="input-group"><input type="text" name="username_criado" id="e_usr_m365" class="form-control form-control-lg bg-dark text-white border-0" readonly placeholder="Gerado no Autoatendimento"><span class="input-group-text fw-bold border-0 bg-primary text-white">@enfas.com.br</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="sist">
                        <div class="alert alert-primary bg-primary bg-opacity-10 border-0 text-white small mb-4">
                            <i class="fa-solid fa-robot fa-2x float-start me-3"></i> <b>Robô de Provisionamento IAM Ativado.</b><br>As regras definidas abaixo serão aplicadas automaticamente na Microsoft (Office 365) e nas catracas físicas ao salvar. O Grupo Padrão M365 será adicionado automaticamente.
                        </div>
                        <div class="row g-4">
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Início Jornada (Catraca)</label><input type="time" name="jornada_inicio" id="e_jini" class="form-control form-control-lg" value="{{ $cfg_global['jornada_padrao_entrada'] ?? '08:00' }}"></div>
                            <div class="col-md-4"><label class="fw-bold mb-1 text-muted small">Fim Jornada (Catraca)</label><input type="time" name="jornada_fim" id="e_jfim" class="form-control form-control-lg" value="{{ $cfg_global['jornada_padrao_saida'] ?? '18:00' }}"></div>
                            <div class="col-md-4 d-flex align-items-center"><div class="form-check mt-4"><input type="checkbox" name="ignora_jornada" id="e_ignora" class="form-check-input" value="1" style="transform: scale(1.5); margin-right:10px;"><label class="form-check-label text-danger fw-bold">Diretoria / Isento de Ponto (VIP)</label></div></div>
                            <hr class="border-secondary my-4">
                            <div class="col-md-4"><label class="fw-bold mb-2 text-primary small">Nível de Licença Office 365</label><select name="m365_perfil" id="e_l" class="form-select form-select-lg"><option value="Sem Licença">Sem Licença</option><option value="Basic">Business Basic</option></select></div>
                            <div class="col-md-8"><label class="fw-bold mb-2 text-primary small">Controle Granular de Apps (Microsoft Graph)</label><br>
                                <div class="d-flex flex-wrap gap-4 p-3 border rounded bg-card mt-1">
                                    @foreach(['Outlook','Teams','OneDrive','SharePoint'] as $a) 
                                        <div class="form-check"><input type="checkbox" name="m365_apps[]" value="{{$a}}" id="app_{{$a}}" class="form-check-input c-app" style="transform: scale(1.3);"><label class="form-check-label fw-bold ms-2" for="app_{{$a}}"> {{$a}}</label></div> 
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12 mt-4"><label class="fw-bold mb-2 text-primary small">Sistemas Intranet de Exceção (Sobrescreve Perfil do Cargo)</label>
                                <div class="d-flex flex-wrap gap-3 p-3 border rounded bg-card mt-1">
                                @foreach(DB::table('sistemas_hc')->get() as $s) 
                                    <div class="form-check"><input type="checkbox" name="sistemas[]" value="{{$s->nome}}" id="sys_{{$s->id}}" class="form-check-input c-sys" style="transform: scale(1.3);"><label class="form-check-label fw-bold ms-2" for="sys_{{$s->id}}">{{$s->nome}}</label></div> 
                                @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="ferias">
                        <div class="row g-4">
                            <div class="col-md-6"><label class="fw-bold mb-1 text-muted small">Início do Afastamento/Férias</label><input type="date" name="ferias_inicio" id="e_fini" class="form-control form-control-lg"></div>
                            <div class="col-md-6"><label class="fw-bold mb-1 text-muted small">Retorno Previsto</label><input type="date" name="ferias_fim" id="e_ffim" class="form-control form-control-lg"></div>
                            <div class="col-12"><label class="fw-bold mb-1 text-muted small">Anotações Internas do RH (Invisível para o Colaborador)</label><textarea name="observacoes_rh" id="e_obs" class="form-control" rows="4"></textarea></div>
                            <div class="col-12"><label class="fw-bold mb-1 text-muted small">Campos Extras / Metadados Customizados</label><textarea name="dados_extras" id="e_ext" class="form-control" rows="3" placeholder="Ex: Tamanho Camisa: M, Restrição Alimentar: Sim"></textarea></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-4" style="border-top: 1px solid var(--border-color); background:var(--bg-card);"><button type="submit" id="btnSalvarFicha" class="btn btn-primary w-100 py-3 fw-bold fs-5 shadow-sm"><i class="fa-solid fa-cloud-arrow-up me-2"></i> Salvar Ficha (Sincronizar Nuvem e Catracas)</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPerfil" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content p-4"><form method="POST" action="/acao" onsubmit="this.querySelector('button').classList.add('btn-loading');">@csrf<input type="hidden" name="id" id="mp_id"><input type="hidden" name="table" value="perfis_acesso"><input type="hidden" name="action" value="save_generic"><h5 class="fw-bold mb-3 text-primary">Grupo de Acesso (RBAC)</h5><p class="small text-muted mb-3">Defina as permissões de Intranet e vincule grupos do Microsoft 365 e Google Workspace. Estes grupos serão aplicados nas contas Microsoft e Google (Listas de E-mail).</p><div class="mb-3"><label class="small text-muted fw-bold">Nome do Perfil/Grupo</label><input name="nome" id="mp_n" class="form-control" required placeholder="Ex: Analista Financeiro"></div><div class="row g-2 mb-3"><div class="col-6"><label class="small text-muted fw-bold">Grupos M365 (API Graph)</label><div id="mp_m365_container"><select name="grupos_m365[]" id="mp_m365" class="form-select form-select-sm" multiple style="height:100px;"><option value="" disabled>Carregando API Graph...</option></select></div></div><div class="col-6"><label class="small text-muted fw-bold">Grupos Google (API Directory)</label><div id="mp_gw_container"><select name="grupos_google[]" id="mp_gw" class="form-select form-select-sm" multiple style="height:100px;"><option value="" disabled>Carregando Directory API...</option></select></div></div></div><div class="mb-4"><label class="small text-muted fw-bold mb-2">Sistemas Intranet (SSO IAM Autorizados)</label><div style="max-height:150px; overflow-y:auto;">@foreach(DB::table('sistemas_hc')->get() as $ss) <div class="form-check mb-2 p-2 border rounded bg-body"><input class="form-check-input sys-chk ms-1" type="checkbox" name="sistemas[]" value="{{$ss->id}}" id="ps_{{$ss->id}}"><label class="form-check-label fw-bold ms-2" for="ps_{{$ss->id}}">{{$ss->nome}}</label></div> @endforeach</div></div><button class="btn btn-primary w-100 py-2 fw-bold">Salvar Grupo de Acesso</button></form></div></div></div>

<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title" id="statusTitle">Alterar Status</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="/acao" id="formStatus" onsubmit="this.querySelector('button').classList.add('btn-loading');">
                @csrf <input type="hidden" name="action" value="change_status"><input type="hidden" name="id" id="statusColabId">
                <div class="modal-body" style="padding: 20px; background:var(--bg-body);">
                    <label class="status-option"><div class="d-flex align-items-center"><div class="status-dot" style="background: #10b981;"><i class="fa-solid fa-check"></i></div><div class="status-text"><h5>Ativo</h5><p>Acesso liberado e Sincronizado.</p></div></div><input type="radio" name="status" value="ativo" required style="transform:scale(1.5);"></label>
                    <label class="status-option"><div class="d-flex align-items-center"><div class="status-dot" style="background: #f59e0b;"><i class="fa-solid fa-clock"></i></div><div class="status-text"><h5>Pendente</h5><p>Aguardando documentação.</p></div></div><input type="radio" name="status" value="pendente" style="transform:scale(1.5);"></label>
                    <label class="status-option"><div class="d-flex align-items-center"><div class="status-dot" style="background: #64748b;"><i class="fa-solid fa-bed"></i></div><div class="status-text"><h5>Inativo / Afastado</h5><p>Licença ou suspensão.</p></div></div><input type="radio" name="status" value="inativo" style="transform:scale(1.5);"></label>
                    <label class="status-option"><div class="d-flex align-items-center"><div class="status-dot" style="background: #ef4444;"><i class="fa-solid fa-ban"></i></div><div class="status-text"><h5>Bloqueado</h5><p>Bloqueio preventivo / Investigação.</p></div></div><input type="radio" name="status" value="bloqueado" style="transform:scale(1.5);"></label>
                    <label class="status-option"><div class="d-flex align-items-center"><div class="status-dot" style="background: #7f1d1d;"><i class="fa-solid fa-user-times"></i></div><div class="status-text"><h5>Desligado / Demitido</h5><p>Desligamento e revogação de licenças.</p></div></div><input type="radio" name="status" value="desligado" style="transform:scale(1.5);"></label>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-color); background:var(--bg-card);"><button type="button" class="btn btn-primary fw-bold w-100 py-3" onclick="confirmarStatus()"><i class="fa-solid fa-check-double me-2"></i> Confirmar Alteração na Rede</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHistorico" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title" id="histTitle">Auditoria do Colaborador</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="histBody" style="background:var(--bg-body);">
                <div class="text-center my-4"><i class="fa-solid fa-circle-notch fa-spin fa-3x text-primary"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMeuPerfil" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content p-4"><form method="POST" action="/acao">@csrf<input type="hidden" name="action" value="save_meu_perfil"><h5 class="fw-bold mb-4"><i class="fa-solid fa-user-gear text-primary me-2"></i> Meu Perfil</h5><div class="mb-3"><label class="fw-bold small text-muted">Nome</label><input name="nome" class="form-control" value="{{ session('admin_nome') }}" required></div><div class="mb-3"><label class="fw-bold small text-muted">E-mail</label><input type="email" name="email" class="form-control" value=""></div><div class="mb-3"><label class="fw-bold small text-muted">Usuário de Login</label><input name="usuario" class="form-control" value="{{ session('admin_usuario') }}" required></div><div class="mb-4"><label class="fw-bold small text-muted">Nova Senha (deixe em branco para manter)</label><input type="password" name="senha" class="form-control" placeholder="******"></div><button class="btn btn-primary w-100 py-2 fw-bold">Salvar Alterações</button></form></div></div></div>
<div class="modal fade" id="modalCSV" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content p-4"><form method="POST" action="/acao" enctype="multipart/form-data" onsubmit="this.querySelector('button').classList.add('btn-loading');">@csrf<input type="hidden" name="action" value="importar_csv"><h5 class="mb-4">Importação CSV</h5><input type="file" name="csv_file" accept=".csv" class="form-control mb-3" required><button class="btn btn-success w-100 fw-bold">Processar Importação</button></form></div></div></div>

<div class="modal fade" id="modalDynamic" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content p-4"><form method="POST" action="/acao" onsubmit="this.querySelector('button').classList.add('btn-loading');">@csrf<input type="hidden" name="table" id="md_table"><input type="hidden" name="id" id="md_id"><input type="hidden" name="action" value="save_generic"><div class="d-flex justify-content-between align-items-center mb-4"><h5 class="fw-bold m-0 text-primary" id="md_title">Novo Registro</h5><button type="button" class="btn-close d-none" data-bs-dismiss="modal"></button></div><div id="fields_container"></div><div id="admin_fields" class="d-none"><div class="mb-3"><label class="small text-muted fw-bold">E-mail</label><input name="email" id="g_e" class="form-control"></div><div class="mb-3"><label class="small text-muted fw-bold">Usuário</label><input name="usuario" id="g_u" class="form-control"></div><div class="mb-3"><label class="small text-muted fw-bold">Senha</label><input name="senha" type="password" class="form-control"></div><div class="mb-4"><label class="small text-muted fw-bold">Perfil Administrativo</label><select name="perfil" id="g_p" class="form-select"><option value="TI">TI</option><option value="Admin">Admin</option><option value="RH">RH</option></select></div><div class="mb-4"><label class="small text-muted fw-bold">Vincular a um Colaborador IAM</label><select name="pre_registro_id" id="g_colab" class="form-select"><option value="">Nenhum</option>@foreach(DB::table('pre_registros')->orderBy('nome_completo')->get() as $cc) <option value="{{$cc->id}}">{{$cc->nome_completo}}</option> @endforeach</select></div></div><button class="btn btn-primary w-100 py-2 mt-3 fw-bold">Salvar Registro</button></form></div></div></div>
<div class="modal fade" id="modalCargo" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content p-4"><form method="POST" action="/acao" onsubmit="this.querySelector('button').classList.add('btn-loading');">@csrf<input type="hidden" name="id" id="mc_id"><input type="hidden" name="table" value="cargos"><input type="hidden" name="action" value="save_generic"><h5 class="fw-bold mb-4 text-primary">Cadastro de Cargo (Função)</h5><p class="small text-muted mb-4">Ao atribuir um Perfil (Grupo de Acesso) a este cargo, todos os colaboradores desta função herdarão os acessos correspondentes no Autoatendimento.</p><div class="mb-3"><label class="small text-muted fw-bold">Nome do Cargo</label><input name="nome" id="mc_n" class="form-control" required></div><div class="mb-4"><label class="small text-muted fw-bold">Vincular Grupo de Acesso (Perfil IAM)</label><select name="perfil_id" id="mc_p" class="form-select" required><option value="">Selecione...</option>@foreach(DB::table('perfis_acesso')->get() as $pp) <option value="{{$pp->id}}">{{$pp->nome}}</option> @endforeach</select></div><button class="btn btn-primary w-100 py-2 fw-bold">Salvar Cargo</button></form></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/cadcolab-enterprise.js?v=3.1.0" defer></script>
<script src="/cadcolab-intelligence.js?v=3.1.0" defer></script>
<script src="/cadcolab-v3-plus.js?v=3.1.0" defer></script>
<script>
    function toggleTheme() { let html = document.documentElement; let currentTheme = html.getAttribute('data-theme'); let newTheme = currentTheme === 'light' ? 'dark' : 'light'; html.setAttribute('data-theme', newTheme); localStorage.setItem('enTheme', newTheme); }
    document.addEventListener('DOMContentLoaded', () => { 
        let savedTheme = localStorage.getItem('enTheme'); if(savedTheme) document.documentElement.setAttribute('data-theme', savedTheme); 
        
        let selGrp = document.getElementById('cfg_m365_grupo_padrao');
        if(selGrp) {
            fetch('/dashboard?ajax_cloud_groups=1').then(r => r.json()).then(data => {
                let currentVal = selGrp.getAttribute('data-current'); let html = '<option value="">Nenhum</option>';
                data.m365.forEach(g => { let isSel = (g.id == currentVal) ? 'selected' : ''; html += `<option value="${g.id}" ${isSel}>${g.nome}</option>`; });
                selGrp.innerHTML = html;
            });
        }
    });
    
    function abrirModalSeguro(id) { 
        var el = document.getElementById(id); 
        if(el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.show(); }
    }
    
    function abrirMeuPerfil() { abrirModalSeguro('modalMeuPerfil'); }

    function executarAcaoGlobal(btn) {
        let action = typeof btn === 'string' ? btn : btn.getAttribute('data-action');
        let msg = typeof btn === 'string' ? arguments[3] : btn.getAttribute('data-msg');
        let id = typeof btn === 'string' ? arguments[1] : (btn.getAttribute('data-id') || '');
        let table = typeof btn === 'string' ? arguments[2] : (btn.getAttribute('data-table') || '');
        
        // Para ações que não precisam de confirmação ou são especiais
        if(action === 'sync_assinatura') {
            Swal.fire({
                title: 'Confirmação', 
                text: msg, 
                icon: 'warning', 
                showCancelButton: true,
                confirmButtonColor: '#00a2e8', 
                cancelButtonText: 'Cancelar', 
                confirmButtonText: 'Sim, Executar',
                background: 'var(--bg-card)', 
                color: 'var(--text-primary)'
            }).then((res) => {
                if(res.isConfirmed) {
                    Swal.fire({
                        title: 'Processando...',
                        text: 'Aguarde enquanto sincronizamos a assinatura',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch('/acao/sync-assinatura', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({ id: id, action: action, table: table })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.sucesso) {
                            Swal.fire({
                                title: 'Sucesso!',
                                text: data.mensagem || 'Assinatura sincronizada!',
                                icon: 'success',
                                confirmButtonColor: '#00a2e8'
                            });
                        } else {
                            Swal.fire({
                                title: 'Atenção',
                                html: data.mensagem || 'Erro ao sincronizar assinatura',
                                icon: 'error',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Erro',
                            text: 'Erro na comunicação com o servidor',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    });
                }
            });
        } else {
            Swal.fire({
                title: 'Confirmação', text: msg, icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#00a2e8', cancelButtonText: 'Cancelar', confirmButtonText: 'Sim, Executar',
                background: 'var(--bg-card)', color: 'var(--text-primary)'
            }).then((res) => {
                if(res.isConfirmed) {
                    let form = document.getElementById('actionForm');
                    document.getElementById('af_action').value = action;
                    document.getElementById('af_id').value = id;
                    document.getElementById('af_table').value = table;
                    form.submit();
                }
            });
        }
    }

    // NOVA FUNÇÃO DE EXCLUSÃO (COM PROTOCOLO E AUDITORIA DE MOTIVO)
    function confirmarExclusao(id) {
        let colabData = JSON.parse(document.getElementById('json_colab_' + id).textContent);
        Swal.fire({
            title: 'ALERTA DE EXCLUSÃO', 
            html: `Você está prestes a apagar <b>${colabData.nome_completo}</b> do banco de dados.<br>
                   <span style="color:#ef4444; font-size:12px;"><b>ATENÇÃO:</b> A ação apagará também os arquivos e caixas de correio na Microsoft e Google de forma irreversível.</span><br><br>
                   <div style="text-align:left; margin-top:15px;">
                       <label style="font-size:12px; color:var(--text-muted);">Número do Chamado (Opcional)</label>
                       <input type="text" id="del_chamado" class="form-control mb-2" placeholder="Ex: INC-12345">
                       <label style="font-size:12px; color:var(--text-muted);">Motivo da Exclusão Definitiva *</label>
                       <select id="del_motivo" class="form-select">
                           <option value="Erro de Cadastro">Erro de Cadastro / Duplicidade</option>
                           <option value="Solicitação LGPD">Solicitação de Titular (LGPD)</option>
                           <option value="Ordem Judicial">Ordem Judicial</option>
                           <option value="Limpeza de Base">Limpeza de Base (Inativo Antigo)</option>
                       </select>
                   </div>
                   <br><span style="color:#ef4444; font-size:12px;"><b>Se for apenas um desligamento, feche e altere o status para Demitido.</b></span>`,
            icon: 'error', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#333', 
            confirmButtonText: '<i class="fa-solid fa-trash"></i> Apagar Definitivamente', cancelButtonText: 'Cancelar', 
            background: 'var(--bg-card)', color: 'var(--text-primary)',
            preConfirm: () => {
                return {
                    motivo: document.getElementById('del_motivo').value,
                    chamado: document.getElementById('del_chamado').value
                }
            }
        }).then((res) => {
            if(res.isConfirmed) {
                Swal.fire({
                    title: 'Segunda Confirmação', text: 'Esta ação não pode ser desfeita. Confirma a exclusão de todas as bases?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'CONFIRMAR EXCLUSÃO', background: 'var(--bg-card)', color: 'var(--text-primary)'
                }).then((res2) => {
                    if(res2.isConfirmed) {
                        let form = document.getElementById('actionForm');
                        document.getElementById('af_action').value = 'delete';
                        document.getElementById('af_id').value = id;
                        document.getElementById('af_table').value = 'pre_registros';
                        
                        let inChamado = document.createElement('input'); inChamado.type = 'hidden'; inChamado.name = 'chamado'; inChamado.value = res.value.chamado;
                        let inMotivo = document.createElement('input'); inMotivo.type = 'hidden'; inMotivo.name = 'motivo'; inMotivo.value = res.value.motivo;
                        form.appendChild(inChamado); form.appendChild(inMotivo);
                        
                        form.submit();
                    }
                });
            }
        });
    }

    function confirmarResetSenha(id) {
        let colabData = JSON.parse(document.getElementById('json_colab_' + id).textContent);
        Swal.fire({
            title: 'Políticas de Segurança (IAM)', 
            html: `A redefinição de senha para <b>${colabData.nome_completo}</b> exige registro de auditoria.<br><br>
                   <div style="text-align:left; margin-top:15px;">
                       <label style="font-size:12px; color:var(--text-muted);">Número do Chamado (Opcional)</label>
                       <input type="text" id="reset_chamado" class="form-control mb-2" placeholder="Ex: INC-12345">
                       <label style="font-size:12px; color:var(--text-muted);">Forma de Solicitação *</label>
                       <select id="reset_forma" class="form-select">
                           <option value="Ligação Telefônica">Ligação Telefônica (Confirmada)</option>
                           <option value="Chamado GLPI">Chamado GLPI / Sistema</option>
                           <option value="E-mail Gestor">E-mail do Gestor</option>
                           <option value="Presencial">Presencial (Balcão TI)</option>
                       </select>
                   </div>
                   <br><span style="color:#ef4444; font-size:12px;"><b>É expressamente proibida a mudança isolada sem registro.</b></span>`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#00a2e8', cancelButtonColor: '#333', 
            confirmButtonText: '<i class="fa-solid fa-lock-open"></i> Gerar e Sincronizar', cancelButtonText: 'Cancelar', 
            background: 'var(--bg-card)', color: 'var(--text-primary)',
            preConfirm: () => {
                return {
                    forma: document.getElementById('reset_forma').value,
                    chamado: document.getElementById('reset_chamado').value
                }
            }
        }).then((res) => {
            if(res.isConfirmed) {
                let form = document.getElementById('actionForm');
                document.getElementById('af_action').value = 'reset_senha';
                document.getElementById('af_id').value = id;
                document.getElementById('af_table').value = 'pre_registros';
                
                let inChamado = document.createElement('input'); inChamado.type = 'hidden'; inChamado.name = 'chamado'; inChamado.value = res.value.chamado;
                let inForma = document.createElement('input'); inForma.type = 'hidden'; inForma.name = 'forma_solicitacao'; inForma.value = res.value.forma;
                form.appendChild(inChamado); form.appendChild(inForma);
                
                form.submit();
            }
        });
    }

    let colabNomeAtual = '';
    function abrirModalStatus(id) { 
        let d = JSON.parse(document.getElementById('json_colab_' + id).textContent);
        document.getElementById('statusColabId').value = id; 
        colabNomeAtual = d.nome_completo;
        document.getElementById('statusTitle').innerHTML = 'Governança: <b class="text-white">' + colabNomeAtual + '</b>';
        document.querySelectorAll('input[name="status"]').forEach(r => { if(r.value === d.status) r.checked = true; }); 
        abrirModalSeguro('modalStatus'); 
    }
    
    function confirmarStatus() { 
        let status = document.querySelector('input[name="status"]:checked'); if(!status) return; 
        let statusName = status.parentElement.parentElement.querySelector('h5').innerText; 
        bootstrap.Modal.getInstance(document.getElementById('modalStatus')).hide(); 
        Swal.fire({ title: 'Atenção, RH!', html: `Deseja alterar a situação de<br><b>${colabNomeAtual}</b> para <b>${statusName}</b>?<br><br><span style="color:#ef4444; font-size:12px;">Isso atualizará o acesso na catraca e nas Nuvens instantaneamente.</span>`, icon: 'warning', showCancelButton: true, background: 'var(--bg-card)', color: 'var(--text-primary)', confirmButtonColor: '#00a2e8', confirmButtonText: 'Sim, Alterar', cancelButtonText: 'Cancelar' }).then((r) => { if(r.isConfirmed) document.getElementById('formStatus').submit(); }); 
    }

    function abrirHistorico(id) {
        let colabData = JSON.parse(document.getElementById('json_colab_' + id).textContent);
        document.getElementById('histTitle').innerHTML = '<i class="fa-solid fa-clock-rotate-left me-2"></i> Auditoria ICP-BR: ' + colabData.nome_completo;
        document.getElementById('histBody').innerHTML = '<div class="text-center my-5"><i class="fa-solid fa-circle-notch fa-spin fa-3x text-primary mb-3"></i><p>Extraindo logs criptografados (LGPD)...</p></div>';
        abrirModalSeguro('modalHistorico');
        fetch('/dashboard?ajax_history=1&id=' + id).then(r => r.json()).then(data => {
            let html = '<div class="timeline">';
            if(data.length === 0) html += '<p class="text-center text-muted mt-4">Nenhum evento registrado no momento.</p>';
            data.forEach(log => {
                let dt = log.data_hora ? log.data_fmt : 'Data Indisponível';
                html += `<div class="timeline-item"><div class="timeline-date"><i class="fa-regular fa-calendar me-1"></i> ${dt} • Operador: <span class="badge bg-secondary">${log.usuario_admin}</span></div><div class="timeline-content"><strong class="text-primary d-block mb-1">${log.acao}</strong><span class="text-muted small">${log.detalhes}</span><br><code class="mt-2 d-inline-block text-success" style="font-size:9px;">${log.codigo_controle}</code></div></div>`;
            });
            html += '</div>'; document.getElementById('histBody').innerHTML = html;
        }).catch(err => {
            document.getElementById('histBody').innerHTML = '<p class="text-center text-danger mt-4">Erro ao carregar auditoria. O formato da data pode estar corrompido em logs antigos.</p>';
        });
    }

    function validarFicha(event) {
        let nome = document.getElementById('e_n').value.trim(); let mae = document.getElementById('e_mae').value.trim();
        let cpf = document.getElementById('e_c').value.trim(); let nasc = document.getElementById('e_d').value.trim();
        if(!nome || !mae || !cpf || !nasc) {
            event.preventDefault(); // Impede o envio e não deixa a tela fechar!
            Swal.fire({ title: 'Atenção RH', text: 'Os campos laranjas (Nome, Mãe, CPF, Nascimento) são OBRIGATÓRIOS. Por favor, preencha-os.', icon: 'warning', confirmButtonColor: '#00a2e8', background: 'var(--bg-card)', color: 'var(--text-primary)'});
            return false;
        }
        let btn = document.getElementById('btnSalvarFicha'); btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando e Sincronizando...'; btn.classList.add('disabled');
        return true;
    }

    function sV(id, val) { let e = document.getElementById(id); if(e) e.value = (val !== null && val !== undefined) ? val : ''; }
    function sC(id, val) { let e = document.getElementById(id); if(e) e.checked = (val == 1 || val == true); }

    function novoColab() { 
        sV('e_i', ''); sV('e_n', ''); sV('e_mae', ''); sV('e_c', ''); sV('e_d', ''); sV('e_rg', ''); sV('e_tel', ''); sV('e_mal', '');
        sV('e_m', ''); sV('e_dadm', ''); sV('e_s', 'pendente'); sV('e_u', ''); sV('e_t', ''); sV('e_g', ''); sV('e_gestor', '');
        sV('e_usr_m365', ''); sV('e_l', 'Sem Licença'); sV('e_fini', ''); sV('e_ffim', ''); sV('e_obs', ''); sV('e_ext', '');
        sC('e_ignora', false); sV('e_jini', '08:00'); sV('e_jfim', '18:00');
        document.getElementById('e_pubid').value = Math.floor(100000 + Math.random() * 900000); 
        document.getElementById('m_e_title').innerText = 'Adicionar Novo Colaborador (IAM)'; 
        document.getElementById('info_atualizacao').style.display = 'none';
        document.querySelectorAll('.c-sys, .c-app').forEach(cb => cb.checked = false); 
        
        document.querySelectorAll('#modalEdicao .nav-link').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('#modalEdicao .tab-pane').forEach(p => { p.classList.remove('show', 'active'); });
        document.querySelector('#modalEdicao .nav-link[data-bs-target="#pessoal"]').classList.add('active');
        document.getElementById('pessoal').classList.add('show', 'active');
        abrirModalSeguro('modalEdicao'); 
    }
    
    function str_pad(n, width, z) { z = z || '0'; n = n + ''; return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n; }

    function editarColab(id) { 
        try {
            let d = JSON.parse(document.getElementById('json_colab_' + id).textContent);
            sV('e_i', d.id); sV('e_pubid', d.public_id ? d.public_id : str_pad(d.id, 6, '0'));
            sV('e_n', d.nome_completo); sV('e_mae', d.nome_mae); sV('e_c', d.cpf); sV('e_d', d.data_nascimento !== '0000-00-00' ? d.data_nascimento : ''); sV('e_rg', d.rg);
            sV('e_tel', d.telefone); sV('e_mal', d.e_mail_pessoal); sV('e_m', d.matricula); sV('e_dadm', d.data_admissao !== '0000-00-00' ? d.data_admissao : '');
            sV('e_s', d.status || 'pendente'); sV('e_u', d.unidade_id); sV('e_t', d.setor_id); sV('e_g', d.cargo_id); sV('e_gestor', d.gestor_id);
            sV('e_usr_m365', d.username_criado); sV('e_l', d.m365_perfil || 'Sem Licença');
            sV('e_jini', d.jornada_inicio || '08:00'); sV('e_jfim', d.jornada_fim || '18:00');
            sC('e_ignora', d.ignora_jornada);
            sV('e_fini', d.ferias_inicio !== '0000-00-00' ? d.ferias_inicio : ''); sV('e_ffim', d.ferias_fim !== '0000-00-00' ? d.ferias_fim : '');
            sV('e_obs', d.observacoes_rh); sV('e_ext', d.dados_extras);
            
            let sistStr = d.sistemas_liberados || ""; document.querySelectorAll('.c-sys').forEach(cb => { cb.checked = sistStr.includes(cb.value); }); 
            let appsStr = d.m365_apps || ""; document.querySelectorAll('.c-app').forEach(cb => { cb.checked = appsStr.includes(cb.value); });
            
            document.getElementById('m_e_title').innerText = 'Ficha do Colaborador: ' + d.nome_completo; 
            
            let infoUpdate = document.getElementById('info_atualizacao');
            if(d.atualizado_fmt) {
                infoUpdate.style.display = 'block';
                let op = d.atualizado_por ? d.atualizado_por.toUpperCase() : 'SISTEMA';
                infoUpdate.innerHTML = `<i class="fa-solid fa-clock-rotate-left"></i> Modificado em: <b>${d.atualizado_fmt}</b> por <b>${op}</b>`;
            } else {
                infoUpdate.style.display = 'none';
            }
            
            document.querySelectorAll('#modalEdicao .nav-link').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('#modalEdicao .tab-pane').forEach(p => { p.classList.remove('show', 'active'); });
            document.querySelector('#modalEdicao .nav-link[data-bs-target="#pessoal"]').classList.add('active');
            document.getElementById('pessoal').classList.add('show', 'active');
            abrirModalSeguro('modalEdicao'); 
        } catch(e) { Swal.fire('Erro Interno', 'Falha ao ler os dados do colaborador.', 'error'); }
    }

    function openPerfil() { 
        sV('mp_id', ''); sV('mp_n', ''); document.querySelectorAll('.sys-chk').forEach(c => c.checked = false); 
        document.getElementById('mp_m365_container').innerHTML = '<select class="form-select form-select-sm" disabled><option>Carregando API Graph...</option></select>';
        document.getElementById('mp_gw_container').innerHTML = '<select class="form-select form-select-sm" disabled><option>Carregando G Suite...</option></select>';
        abrirModalSeguro('modalPerfil'); 
        
        fetch('/dashboard?ajax_cloud_groups=1').then(r => r.json()).then(data => {
            let hm = ''; if(data.m365.length===0) hm='<option disabled>Nenhum grupo M365 retornado pela API.</option>'; else data.m365.forEach(g => { hm += `<option value="${g.id}">${g.nome}</option>`; }); 
            document.getElementById('mp_m365_container').innerHTML = '<select name="grupos_m365[]" class="form-select form-select-sm" multiple style="height:120px;">'+hm+'</select>';
            let hg = ''; if(data.google.length===0) hg='<option disabled>Nenhum grupo Google retornado pela API.</option>'; else data.google.forEach(g => { hg += `<option value="${g.id}">${g.nome}</option>`; }); 
            document.getElementById('mp_gw_container').innerHTML = '<select name="grupos_google[]" class="form-select form-select-sm" multiple style="height:120px;">'+hg+'</select>';
        });
    } 

    function editPerfil(id) { 
        let d = JSON.parse(document.getElementById('data_reg_perfis_acesso_' + id).textContent);
        sV('mp_id', d.id); sV('mp_n', d.nome); 
        let sysIds = d.sistemas_ids ? d.sistemas_ids.split(',') : []; document.querySelectorAll('.sys-chk').forEach(c => { c.checked = sysIds.includes(c.value); }); 
        
        document.getElementById('mp_m365_container').innerHTML = '<select class="form-select form-select-sm" disabled><option>Buscando na API Graph...</option></select>';
        document.getElementById('mp_gw_container').innerHTML = '<select class="form-select form-select-sm" disabled><option>Buscando no G Suite...</option></select>';
        abrirModalSeguro('modalPerfil'); 
        
        fetch('/dashboard?ajax_cloud_groups=1').then(r => r.json()).then(data => {
            let grpM = d.grupos_m365 ? d.grupos_m365.split(',') : []; let grpG = d.grupos_google ? d.grupos_google.split(',') : [];
            let hm = ''; if(data.m365.length===0) hm='<option disabled>Nenhum grupo M365.</option>'; else data.m365.forEach(g => { let sel = grpM.includes(g.id)?'selected':''; hm += `<option value="${g.id}" ${sel}>${g.nome}</option>`; }); 
            document.getElementById('mp_m365_container').innerHTML = '<select name="grupos_m365[]" class="form-select form-select-sm" multiple style="height:120px;">'+hm+'</select>';
            let hg = ''; if(data.google.length===0) hg='<option disabled>Nenhum grupo Google.</option>'; else data.google.forEach(g => { let sel = grpG.includes(g.id)?'selected':''; hg += `<option value="${g.id}" ${sel}>${g.nome}</option>`; }); 
            document.getElementById('mp_gw_container').innerHTML = '<select name="grupos_google[]" class="form-select form-select-sm" multiple style="height:120px;">'+hg+'</select>';
        });
    }

    function openGeneric(t){ 
        sV('md_table', t); sV('md_id', ''); document.getElementById('md_title').innerText = "Adicionar Novo"; let html = ""; 
        if(t == 'unidades'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Nome</label><input name="nome" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Endereço</label><input name="endereco" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Telefone</label><input name="telefone" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Template de Assinatura (HTML M365)</label><textarea name="template_assinatura" class="form-control font-monospace" rows="4" placeholder="Variáveis: {NOME}, {CARGO}, {SETOR}, {TELEFONE}, {EMAIL}"></textarea></div>'; } 
        else if(t == 'setores'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Nome</label><input name="nome" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Responsável</label><input name="responsavel" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Ramal</label><input name="ramal" class="form-control"></div>'; } 
        else if(t == 'sistemas_hc'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Aplicação</label><input name="nome" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Link (URL)</label><input name="link" class="form-control" required></div>'; } 
        else { html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Descrição</label><input name="nome" class="form-control" required></div>'; } 
        document.getElementById('fields_container').innerHTML = html; 
        
        let adm = document.getElementById('admin_fields'); 
        if(t == 'usuarios_admin'){ 
            adm.classList.remove('d-none'); 
            adm.querySelectorAll('input, select').forEach(el => el.disabled = false);
        } else { 
            adm.classList.add('d-none'); 
            adm.querySelectorAll('input, select').forEach(el => el.disabled = true);
        } 
        abrirModalSeguro('modalDynamic'); 
    }
    
    function editGeneric(t, id){ 
        let d = JSON.parse(document.getElementById('data_reg_' + t + '_' + id).textContent);
        sV('md_table', t); sV('md_id', d.id); document.getElementById('md_title').innerText = "Editar Parâmetro"; let html = ""; 
        if(t == 'unidades'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Nome</label><input name="nome" value="'+(d.nome||'')+'" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Endereço</label><input name="endereco" value="'+(d.endereco||'')+'" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Telefone</label><input name="telefone" value="'+(d.telefone||'')+'" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Template de Assinatura (HTML M365)</label><textarea name="template_assinatura" class="form-control font-monospace" rows="4" placeholder="Variáveis: {NOME}, {CARGO}, {SETOR}, {TELEFONE}, {EMAIL}">'+(d.template_assinatura?d.template_assinatura:'')+'</textarea></div>'; } 
        else if(t == 'setores'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Nome</label><input name="nome" value="'+(d.nome||'')+'" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Responsável</label><input name="responsavel" value="'+(d.responsavel||'')+'" class="form-control"></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Ramal</label><input name="ramal" value="'+(d.ramal||'')+'" class="form-control"></div>'; } 
        else if(t == 'sistemas_hc'){ html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Aplicação</label><input name="nome" value="'+(d.nome||'')+'" class="form-control" required></div><div class="mb-3"><label class="fw-bold mb-1 small text-muted">Link (URL)</label><input name="link" value="'+(d.link||'')+'" class="form-control" required></div>'; } 
        else { let v_nome = d.nome ? d.nome : (d.usuario||''); html = '<div class="mb-3"><label class="fw-bold mb-1 small text-muted">Descrição</label><input name="nome" value="'+v_nome+'" class="form-control" required></div>'; } 
        document.getElementById('fields_container').innerHTML = html; 
        
        let adm = document.getElementById('admin_fields'); 
        if(t == 'usuarios_admin'){ 
            adm.classList.remove('d-none'); sV('g_e', d.email); sV('g_u', d.usuario); sV('g_p', d.perfil); sV('g_colab', d.pre_registro_id); 
            adm.querySelectorAll('input, select').forEach(el => el.disabled = false);
        } else { 
            adm.classList.add('d-none'); 
            adm.querySelectorAll('input, select').forEach(el => el.disabled = true);
        } 
        abrirModalSeguro('modalDynamic'); 
    }
    
    function openCargo(){ sV('mc_id', ''); sV('mc_n', ''); sV('mc_p', ''); abrirModalSeguro('modalCargo'); } 
    function editCargo(id){ let d = JSON.parse(document.getElementById('data_reg_cargos_' + id).textContent); sV('mc_id', d.id); sV('mc_n', d.nome); sV('mc_p', d.perfil_id); abrirModalSeguro('modalCargo'); } 
</script>
</body>
</html>
