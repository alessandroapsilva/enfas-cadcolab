<?php

return [
    'version' => '5.0.0-beta.1',
    'groups' => [
        'overview' => [
            'label' => 'Visão Geral',
            'icon' => 'fa-house',
            'modules' => [
                'dashboard' => ['label' => 'Início', 'icon' => 'fa-house', 'roles' => ['admin','ti','rh','gestor','operador','consulta']],
            ],
        ],
        'people' => [
            'label' => 'Pessoas & Estrutura',
            'icon' => 'fa-users',
            'modules' => [
                'colaboradores' => ['label' => 'Colaboradores', 'icon' => 'fa-users', 'roles' => ['admin','ti','rh','gestor','operador','consulta']],
                'unidades' => ['label' => 'Unidades', 'icon' => 'fa-building', 'roles' => ['admin','rh']],
                'setores' => ['label' => 'Setores', 'icon' => 'fa-sitemap', 'roles' => ['admin','rh']],
                'cargos' => ['label' => 'Cargos e Funções', 'icon' => 'fa-briefcase', 'roles' => ['admin','rh']],
                'badge-studio' => ['label' => 'Crachás & Modelos', 'icon' => 'fa-id-card', 'roles' => ['admin','ti','rh']],
            ],
        ],
        'identity' => [
            'label' => 'Identidade & Acessos',
            'icon' => 'fa-shield-halved',
            'modules' => [
                'identity-directory' => ['label' => 'Diretório LDAP / AD', 'icon' => 'fa-address-book', 'roles' => ['admin','ti']],
                'grupos' => ['label' => 'Perfis de Acesso (RBAC)', 'icon' => 'fa-user-lock', 'roles' => ['admin','ti']],
                'sistemas' => ['label' => 'Aplicações SSO', 'icon' => 'fa-diagram-project', 'roles' => ['admin','ti']],
                'usuarios' => ['label' => 'Administradores', 'icon' => 'fa-user-shield', 'roles' => ['admin']],
            ],
        ],
        'cloud' => [
            'label' => 'Cloud & Provisionamento',
            'icon' => 'fa-cloud',
            'modules' => [
                'microsoft365' => ['label' => 'Microsoft 365', 'icon' => 'fa-microsoft', 'roles' => ['admin','ti'], 'virtual' => true],
                'google-workspace' => ['label' => 'Google Workspace', 'icon' => 'fa-google', 'roles' => ['admin','ti'], 'virtual' => true],
                'status' => ['label' => 'Saúde das Integrações', 'icon' => 'fa-heart-pulse', 'roles' => ['admin','ti']],
            ],
        ],
        'automation' => [
            'label' => 'Automação & Lifecycle',
            'icon' => 'fa-bolt',
            'modules' => [
                'robos' => ['label' => 'Jornada & Automações', 'icon' => 'fa-robot', 'roles' => ['admin','ti']],
                'lifecycle' => ['label' => 'Onboarding & Offboarding', 'icon' => 'fa-arrows-rotate', 'roles' => ['admin','ti','rh'], 'virtual' => true],
            ],
        ],
        'communications' => [
            'label' => 'Comunicações',
            'icon' => 'fa-comments',
            'modules' => [
                'comunicacoes' => ['label' => 'Central de Comunicações', 'icon' => 'fa-comments', 'roles' => ['admin','ti','rh']],
                'email-logs' => ['label' => 'Histórico de E-mails', 'icon' => 'fa-envelope-circle-check', 'roles' => ['admin','ti'], 'virtual' => true],
                'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'fa-whatsapp', 'roles' => ['admin','ti'], 'virtual' => true],
            ],
        ],
        'governance' => [
            'label' => 'Governança & Inteligência',
            'icon' => 'fa-chart-line',
            'modules' => [
                'relatorios' => ['label' => 'Relatórios Enterprise', 'icon' => 'fa-chart-column', 'roles' => ['admin','ti','rh','gestor']],
                'auditoria' => ['label' => 'Trilha de Auditoria', 'icon' => 'fa-file-shield', 'roles' => ['admin','ti']],
                'insights' => ['label' => 'Insights & Recomendações', 'icon' => 'fa-wand-magic-sparkles', 'roles' => ['admin','ti'], 'virtual' => true],
                'erros' => ['label' => 'Diagnósticos & Falhas', 'icon' => 'fa-triangle-exclamation', 'roles' => ['admin','ti']],
            ],
        ],
        'admin' => [
            'label' => 'Administração',
            'icon' => 'fa-gears',
            'modules' => [
                'configuracoes' => ['label' => 'Configurações Mestres', 'icon' => 'fa-sliders', 'roles' => ['admin','ti']],
                'ajuda' => ['label' => 'Central de Ajuda', 'icon' => 'fa-circle-question', 'roles' => ['admin','ti','rh','gestor','operador','consulta']],
                'changelog' => ['label' => 'Notas de Versão', 'icon' => 'fa-clock-rotate-left', 'roles' => ['admin','ti','rh','gestor','operador','consulta']],
            ],
        ],
    ],
];
