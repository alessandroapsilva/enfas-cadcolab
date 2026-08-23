<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark"><head><meta charset="utf-8"/><title>ENFAS | Autoatendimento</title><meta name="viewport" content="width=device-width, initial-scale=1.0"/><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><style>:root { --primary: #00a2e8; --dark-bg: #1c1e29; --card-bg: #ffffff; --text-primary: #1e293b; } body { background: var(--dark-bg); font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin:0; } .portal-container { width: 900px; background: var(--card-bg); border-radius: 20px; display: flex; box-shadow: 0 20px 40px rgba(0,0,0,0.5); overflow: hidden; } .portal-brand { background: linear-gradient(135deg, #00a2e8 0%, #005b82 100%); width: 40%; padding: 40px; color: #fff; display: flex; flex-direction: column; justify-content: space-between; } .portal-form { width: 60%; padding: 50px; background: #fff; } .btn-login { background: var(--primary); color: #fff; border: none; padding: 14px; border-radius: 10px; width: 100%; font-weight: bold; } .form-control { border: 2px solid #e2e8f0; border-radius: 10px; }</style></head>
<body>
    @if(session('erro')) <script>Swal.fire('Atenção', '{{ session('erro') }}', 'error');</script> @endif
    <div class="portal-container">
        <div class="portal-brand"><div><h3 class="fw-bold">ENFAS</h3><p style="opacity:0.8;">Gestão de Identidades</p></div><div style="font-size:12px; opacity:0.6;">&copy; {{ date('Y') }}</div></div>
        <div class="portal-form">
            <h3 class="fw-bold mb-4">Login Corporativo</h3>
            <form method="POST" action="{{ url('/Conta/Login') }}">
                @csrf
                <div class="form-floating mb-3"><input type="text" name="usuario" class="form-control" required placeholder="ID COLAB"><label>ID COLAB (CPF ou Matrícula)</label></div>
                <div class="form-floating mb-3"><input type="password" name="senha" class="form-control" required placeholder="Senha"><label>Senha</label></div>
                <button name="login_colaborador" class="btn-login">Acessar Sistema</button>
            </form>
        </div>
    </div>
</body>
</html>
