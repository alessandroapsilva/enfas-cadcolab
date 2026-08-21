<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateEmergencyAdmin extends Command
{
    protected $signature = 'cadcolab:admin
        {username : Identificador do administrador}
        {--name=Administrador de Emergência : Nome exibido}
        {--email= : E-mail corporativo}';

    protected $description = 'Cria ou redefine uma conta administrativa local de emergência';

    public function handle(): int
    {
        $password = $this->secret('Informe uma senha forte (mínimo de 14 caracteres)');
        $confirmation = $this->secret('Confirme a senha');

        if (! is_string($password) || strlen($password) < 14 || $password !== $confirmation) {
            $this->error('A senha deve ter ao menos 14 caracteres e a confirmação deve ser idêntica.');
            return self::FAILURE;
        }

        DB::table('usuarios_admin')->updateOrInsert(
            ['usuario' => $this->argument('username')],
            [
                'nome' => $this->option('name'),
                'email' => $this->option('email'),
                'senha' => Hash::make($password),
                'perfil' => 'TI',
                'auth_source' => 'local',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->info('Conta local de emergência configurada com segurança.');
        return self::SUCCESS;
    }
}
