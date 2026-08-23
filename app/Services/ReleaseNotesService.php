<?php

namespace App\Services;

class ReleaseNotesService
{
    public function all(): array
    {
        return [
            [
                'version' => '4.3.0',
                'date' => '23/08/2026',
                'title' => 'Badge Studio & People Experience',
                'type' => 'current',
                'summary' => 'Novo módulo visual para criação, edição e pré-visualização de crachás e modelos de identificação.',
                'items' => [
                    'Designer de Crachás & Modelos como módulo independente em Pessoas & Estrutura.',
                    'Editor visual com frente e verso, elementos arrastáveis e propriedades por elemento.',
                    'Campos dinâmicos de colaborador: nome, matrícula, cargo, unidade, e-mail, CPF, nascimento e foto.',
                    'Pré-visualização com dados reais quando disponíveis e dados seguros de demonstração como fallback.',
                    'Modelos por unidade, orientação vertical/horizontal, dimensões em milímetros e modelo padrão.',
                    'Modo de visualização para impressão e armazenamento do layout em JSON versionável.',
                    'Sidebar atualizado para CADCOLAB v4.3.0 e separação mais clara de módulos.',
                    'Base preparada para expansão futura de modelos de documentos, assinaturas e credenciais visuais.'
                ],
            ],
            [
                'version' => '4.2.0',
                'date' => '23/08/2026',
                'title' => 'Navigation Shell & Product Experience',
                'type' => 'feature',
                'summary' => 'Nova navegação modular sem recarregamento completo, sidebar reorganizado e changelog centralizado.',
                'items' => [
                    'Navegação assíncrona entre módulos usando fetch e History API.',
                    'Sidebar reorganizado por domínio funcional e com estado ativo persistente.',
                    'Breadcrumb e título atualizados dinamicamente sem refresh da página.',
                    'Loader de transição entre módulos e tratamento de erro com fallback seguro.',
                    'Changelog centralizado e versionado com histórico das evoluções da plataforma.',
                    'Identificação de versão do produto exibida no sidebar.'
                ],
            ],
            [
                'version' => '4.1.0',
                'date' => '23/08/2026',
                'title' => 'Governance Intelligence & Enterprise Reports',
                'type' => 'feature',
                'summary' => 'Insights locais de governança e relatórios operacionais avançados.',
                'items' => [
                    'Score de governança calculado com dados internos do CADCOLAB.',
                    'Recomendações priorizadas para falhas de integração, LDAP e comunicações.',
                    'Relatórios por período de 7, 30 e 90 dias.',
                    'Resumo operacional de auditoria, erros e comunicações.',
                    'Exportação CSV para análise e compliance.',
                    'Catálogo visual de módulos enterprise.'
                ],
            ],
            [
                'version' => '4.0.0',
                'date' => '23/08/2026',
                'title' => 'Modular Enterprise Architecture',
                'type' => 'major',
                'summary' => 'Separação das integrações e início da arquitetura modular da plataforma.',
                'items' => [
                    'Microsoft 365 extraído para serviço dedicado com operações independentes.',
                    'Google Workspace extraído para serviço dedicado.',
                    'CloudIntegrationService mantido como fachada de compatibilidade.',
                    'Diagnóstico Microsoft Graph por etapa com HTTP status e request-id.',
                    'Separação de perfil, telefone, estado da conta, senha e licenças M365.',
                    'Base preparada para módulos independentes sem quebrar controllers legados.'
                ],
            ],
            [
                'version' => '3.4.0',
                'date' => '23/08/2026',
                'title' => 'Premium UX & Modular Navigation',
                'type' => 'feature',
                'summary' => 'Reorganização visual do painel e refinamento da experiência administrativa.',
                'items' => [
                    'Navegação reorganizada em Pessoas, Identidade, Integrações, Governança, Administração e Suporte.',
                    'Breadcrumbs e hierarquia visual enterprise.',
                    'Dashboard com cards e indicadores mais consistentes.',
                    'Avatar de colaboradores normalizado e recortado proporcionalmente.',
                    'Configurações Mestres transformadas em hub de administração.'
                ],
            ],
            [
                'version' => '3.3.0',
                'date' => '23/08/2026',
                'title' => 'Communications & WhatsApp Foundation',
                'type' => 'feature',
                'summary' => 'Observabilidade de comunicações e base avançada para WhatsApp Cloud API.',
                'items' => [
                    'Histórico de e-mails enviados com destinatário, assunto, status e operador.',
                    'Registro de falhas de comunicação para diagnóstico.',
                    'WhatsAppService dedicado com templates e versão Graph configurável.',
                    'Webhook da Meta com validação de assinatura.',
                    'Conversas, mensagens recebidas/enviadas e status de entrega persistidos.'
                ],
            ],
            [
                'version' => '3.2.0',
                'date' => '23/08/2026',
                'title' => 'Identity Lifecycle & Policies',
                'type' => 'feature',
                'summary' => 'Fundação para lifecycle de identidade, onboarding, offboarding e políticas.',
                'items' => [
                    'IdentityLifecycleService para eventos do ciclo de vida.',
                    'IdentityPolicyService para regras por setor, cargo e grupo.',
                    'Eventos de provisionamento e trilha de execução.',
                    'Base para onboarding e offboarding orquestrados.'
                ],
            ],
            [
                'version' => '3.1.0',
                'date' => '23/08/2026',
                'title' => 'Identity Directory / LDAP',
                'type' => 'feature',
                'summary' => 'Autenticação corporativa e sincronização com LDAP/Active Directory.',
                'items' => [
                    'LDAP, LDAPS e StartTLS.',
                    'Configuração de diretório dentro das Configurações Mestres.',
                    'Senha de bind armazenada criptografada.',
                    'Sincronização de usuários, grupos e atributos do diretório.',
                    'Mapeamento de grupos LDAP para perfis Admin, TI e RH.',
                    'Login corporativo sem armazenar a senha do usuário LDAP.',
                    'Remoção do master account hardcoded e proteção do fallback local.'
                ],
            ],
            [
                'version' => '3.0.0',
                'date' => '15/05/2026',
                'title' => 'Enterprise Administration',
                'type' => 'major',
                'summary' => 'Consolidação dos recursos administrativos e de governança existentes.',
                'items' => [
                    'Identificação do operador responsável pelas alterações.',
                    'Protocolos avançados de reset e exclusão.',
                    'Tratamento seguro de dados nulos no cadastro.',
                    'Central de Relatórios Oficiais.',
                    'Templates de e-mail controláveis pelo painel.',
                    'Geração de credenciais corporativas e melhorias gerais de UX.'
                ],
            ],
            [
                'version' => '2.0.0',
                'date' => '10/05/2026',
                'title' => 'Cloud Integrations',
                'type' => 'major',
                'summary' => 'Primeira integração ampla com serviços de nuvem.',
                'items' => [
                    'Integração inicial com Microsoft Graph.',
                    'Provisionamento de usuários em serviços cloud.',
                    'Estrutura inicial de licenças e sincronização.'
                ],
            ],
            [
                'version' => '1.0.0',
                'date' => '2026',
                'title' => 'CADCOLAB Foundation',
                'type' => 'major',
                'summary' => 'Base funcional do CADCOLAB para cadastro e administração de colaboradores.',
                'items' => [
                    'Cadastro de colaboradores e estrutura organizacional.',
                    'Perfis administrativos e controles de acesso.',
                    'Auditoria e módulos administrativos iniciais.'
                ],
            ],
        ];
    }

    public function current(): array
    {
        return $this->all()[0];
    }
}
