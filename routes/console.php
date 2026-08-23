<?php

use App\Services\LdapDirectoryService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('identity:ldap-test', function (LdapDirectoryService $directory) {
    $result = $directory->testConnection();
    $result['success'] ? $this->info($result['message']) : $this->error($result['message']);
    return $result['success'] ? self::SUCCESS : self::FAILURE;
})->purpose('Testa a conexão e o bind com o diretório LDAP/Active Directory');

Artisan::command('identity:sync', function (LdapDirectoryService $directory) {
    try {
        $result = $directory->syncAll();
        $this->info("Sincronização concluída: {$result['users']} usuários e {$result['groups']} grupos.");
        return self::SUCCESS;
    } catch (Throwable $e) {
        $this->error($e->getMessage());
        return self::FAILURE;
    }
})->purpose('Sincroniza usuários e grupos do LDAP/Active Directory com o CADCOLAB');
