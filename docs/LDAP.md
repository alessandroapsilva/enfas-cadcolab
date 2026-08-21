# Autenticação corporativa LDAP

O CADCOLAB suporta Active Directory, OpenLDAP e Microsoft Entra Domain Services por LDAP. Em produção, use LDAPS (porta 636) ou StartTLS e mantenha as credenciais exclusivamente no arquivo `.env` da VPS.

## Requisitos

- Extensão PHP LDAP instalada (`php-ldap`);
- DNS e rota da aplicação até pelo menos dois controladores de domínio;
- certificado da autoridade certificadora interna instalado no servidor;
- conta de serviço somente leitura para pesquisa no diretório;
- grupos dedicados para acesso e perfis do CADCOLAB.

## Configuração recomendada para Active Directory

```dotenv
LDAP_ENABLED=true
LDAP_HOSTS=ad01.enfas.local,ad02.enfas.local
LDAP_PORT=636
LDAP_USE_SSL=true
LDAP_USE_TLS=false
LDAP_BASE_DN="DC=enfas,DC=local"
LDAP_BIND_DN="CN=svc-cadcolab,OU=Contas de Servico,DC=enfas,DC=local"
LDAP_BIND_PASSWORD="definir-somente-na-vps"
LDAP_ALLOWED_GROUPS="CN=CADCOLAB-USUARIOS,OU=Grupos,DC=enfas,DC=local"
LDAP_GROUPS_TI="CN=CADCOLAB-TI,OU=Grupos,DC=enfas,DC=local"
LDAP_GROUPS_RH="CN=CADCOLAB-RH,OU=Grupos,DC=enfas,DC=local"
LDAP_GROUPS_GESTOR="CN=CADCOLAB-GESTORES,OU=Grupos,DC=enfas,DC=local"
LDAP_GROUPS_OPERADOR="CN=CADCOLAB-OPERADORES,OU=Grupos,DC=enfas,DC=local"
LDAP_GROUPS_CONSULTA="CN=CADCOLAB-CONSULTA,OU=Grupos,DC=enfas,DC=local"
LDAP_DEFAULT_ROLE=Consulta
LOCAL_AUTH_ENABLED=true
LOCAL_AUTH_FALLBACK=false
```

Separe múltiplos hosts por vírgula e múltiplos grupos por ponto e vírgula.

## Implantação

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

Crie uma conta local de contingência sem colocar senha no código:

```bash
php artisan cadcolab:admin emergencia --name="Administrador de Emergência"
```

Mantenha `LOCAL_AUTH_FALLBACK=false`. Assim, uma indisponibilidade do LDAP não permite tentativa automática com credenciais locais. A conta de contingência pode ser utilizada quando o LDAP estiver desabilitado deliberadamente durante um procedimento operacional autorizado.

## Segurança operacional

- Não publique o arquivo `.env`;
- rotacione imediatamente a antiga senha administrativa fixa;
- use conta LDAP de serviço sem privilégios de escrita;
- restrinja a porta LDAP no firewall aos controladores autorizados;
- monitore os eventos `LOGIN_SUCESSO` e `LOGIN_NEGADO`;
- teste primeiro em homologação.
