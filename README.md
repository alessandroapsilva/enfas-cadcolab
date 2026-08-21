# CADCOLAB ENFAS

Portal corporativo para gestão do ciclo de vida de colaboradores, acessos, integrações, crachás e rotinas administrativas da ENFAS.

## Base técnica

- PHP 8.3+
- Laravel 13
- autenticação corporativa por Active Directory, OpenLDAP ou Entra Domain Services
- integração com Microsoft 365 e Google Workspace
- trilha de auditoria administrativa
- perfis TI, RH, Gestor, Operador e Consulta

## Instalação

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm ci
npm run build
php artisan optimize
```

O diretório público do servidor web deve apontar exclusivamente para `public/`.

## Identidade corporativa

A configuração completa de LDAP/Active Directory está em [docs/LDAP.md](docs/LDAP.md). As senhas e chaves devem permanecer no `.env` da VPS e nunca devem ser versionadas.

Para criar uma conta local de contingência:

```bash
php artisan cadcolab:admin emergencia --name="Administrador de Emergência"
```

## Segurança

- use HTTPS e LDAPS/StartTLS em produção;
- mantenha `APP_DEBUG=false`;
- proteja o `.env`;
- execute filas com serviço dedicado;
- configure backups do banco e teste a restauração;
- revise regularmente os eventos de auditoria.

## Desenvolvimento

```bash
composer setup
composer test
```
