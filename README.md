# CADCOLAB ENFAS

Plataforma corporativa de identidade, acessos, colaboradores e integrações da ENFAS.

## Arquitetura de branches

- `main`: linha estável de produção.
- `develop`: integração das funcionalidades aprovadas.
- `feature/identity-directory`: desenvolvimento do módulo corporativo de identidade e diretório.

## Identity & Directory

O módulo `Identity & Directory` transforma LDAP/Active Directory na fonte central de identidade do CADCOLAB.

Principais recursos desta implementação:

- autenticação de usuários diretamente no LDAP/Active Directory;
- suporte a LDAP, LDAPS e StartTLS;
- a senha do diretório nunca é armazenada no banco do CADCOLAB;
- sincronização de usuários e grupos;
- identificação de contas habilitadas e bloqueadas pelo `userAccountControl`;
- leitura de nome, login, UPN, e-mail, matrícula, departamento, cargo, telefones, gestor e grupos;
- mapeamento de grupos do AD para perfis `Admin`, `TI`, `RH` e `Operador`;
- trilha de auditoria de autenticação, teste de conexão e sincronização;
- painel administrativo em `/identity-directory`;
- comandos CLI para teste e sincronização.

### Requisitos do servidor

PHP 8.3+ com a extensão LDAP instalada. Em Ubuntu/Debian, instale o pacote correspondente à versão do PHP em uso, por exemplo:

```bash
sudo apt update
sudo apt install php8.3-ldap
sudo systemctl restart php8.3-fpm
```

Se o servidor utilizar outra versão do PHP, ajuste o nome do pacote e do serviço.

Para LDAPS em produção, o servidor PHP deve confiar na cadeia de certificados da autoridade certificadora que emitiu o certificado do controlador de domínio. Não desabilite validação TLS em produção.

### Configuração

Copie as variáveis de `Identity & Directory` presentes em `.env.example` para o `.env` real e configure:

```dotenv
IDENTITY_DIRECTORY_ENABLED=true
IDENTITY_LOCAL_FALLBACK=true
IDENTITY_SYNC_ON_LOGIN=true

LDAP_HOST=ad.enfas.local
LDAP_PORT=636
LDAP_SSL=true
LDAP_START_TLS=false
LDAP_BASE_DN="DC=enfas,DC=local"
LDAP_USERS_DN="OU=Usuarios,DC=enfas,DC=local"
LDAP_GROUPS_DN="OU=Grupos,DC=enfas,DC=local"
LDAP_BIND_DN="CN=svc-cadcolab,OU=Servicos,DC=enfas,DC=local"
LDAP_BIND_PASSWORD="trocar-no-servidor"
LDAP_ACCOUNT_SUFFIX="@enfas.local"
```

A conta `LDAP_BIND_DN` deve ser uma conta técnica dedicada e de menor privilégio, com permissão apenas para leitura dos objetos necessários.

### Perfis por grupos

```dotenv
LDAP_ADMIN_GROUPS="CADCOLAB-ADMIN"
LDAP_TI_GROUPS="CADCOLAB-TI"
LDAP_RH_GROUPS="CADCOLAB-RH"
LDAP_DEFAULT_PROFILE=Operador
```

O CADCOLAB lê `memberOf` do usuário e atribui o perfil correspondente ao CN do grupo.

### Implantação da migration

```bash
php artisan migrate --force
php artisan optimize:clear
```

### Teste do diretório

```bash
php artisan identity:ldap-test
```

### Sincronização manual

```bash
php artisan identity:sync
```

Também é possível usar o painel `Identity & Directory` para testar a conexão e iniciar uma sincronização.

## Segurança

Não coloque senhas, tokens, chaves privadas ou credenciais LDAP no Git. O `.env.example` contém somente nomes e exemplos de configuração.

O projeto não deve possuir senha administrativa padrão. Para instalação inicial sem LDAP, uma conta de bootstrap pode ser criada exclusivamente por variável de ambiente durante a primeira migration; remova a variável assim que o acesso inicial estiver configurado.

## Stack

- Laravel 13
- PHP 8.3+
- MySQL
- LDAP / Active Directory
- Microsoft Graph / Microsoft 365
- Google Workspace Admin APIs
- Meta WhatsApp Cloud API

## Próximos módulos da suíte

A camada de identidade será a base para provisionamento e desprovisionamento, onboarding/offboarding, políticas de acesso, inventário de contas/licenças, Google Workspace, Microsoft 365 e automações de WhatsApp.
