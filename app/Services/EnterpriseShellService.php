<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;

class EnterpriseShellService
{
    public function transform(string $html, string $page): string
    {
        if ($html === '' || !str_contains($html, '<body')) {
            return $html;
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);
        $sidebar = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' sidebar ')]")->item(0);
        if ($sidebar instanceof DOMElement) {
            $this->replaceChildren($dom, $sidebar, $this->sidebarHtml($page));
            $sidebar->setAttribute('class', trim($sidebar->getAttribute('class') . ' cadcolab-v4-sidebar'));
        }

        $topbar = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' topbar ')]")->item(0);
        if ($topbar instanceof DOMElement) {
            $this->replaceChildren($dom, $topbar, $this->topbarHtml($page));
            $topbar->setAttribute('class', trim($topbar->getAttribute('class') . ' cadcolab-v4-topbar'));
        }

        foreach ($xpath->query("//*[contains(normalize-space(string(.)), 'v3.0 Enterprise')]") as $node) {
            if ($node instanceof DOMElement && in_array(strtolower($node->tagName), ['small','span','div','footer'], true)) {
                $node->nodeValue = '';
            }
        }

        $body = $xpath->query('//body')->item(0);
        if ($body instanceof DOMElement) {
            $body->setAttribute('data-cadcolab-shell', 'v4.4.0');
        }

        $result = $dom->saveHTML();
        $result = preg_replace('/^<\?xml[^>]+>\s*/', '', $result) ?: $result;
        return $result;
    }

    private function replaceChildren(DOMDocument $dom, DOMElement $target, string $html): void
    {
        while ($target->firstChild) {
            $target->removeChild($target->firstChild);
        }

        $tmp = new DOMDocument('1.0', 'UTF-8');
        $tmp->loadHTML('<?xml encoding="UTF-8"><div id="cadcolab-fragment">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $wrapper = (new DOMXPath($tmp))->query('//*[@id="cadcolab-fragment"]')->item(0);
        if (!$wrapper) return;

        foreach (iterator_to_array($wrapper->childNodes) as $child) {
            $target->appendChild($dom->importNode($child, true));
        }
    }

    private function sidebarHtml(string $page): string
    {
        $groups = [
            'Visão Geral' => [
                ['dashboard', 'fa-house', 'Início'],
            ],
            'Pessoas & Estrutura' => [
                ['colaboradores', 'fa-users', 'Colaboradores'],
                ['unidades', 'fa-building', 'Unidades'],
                ['setores', 'fa-sitemap', 'Setores'],
                ['cargos', 'fa-briefcase', 'Cargos e Funções'],
                ['badge-studio', 'fa-id-card', 'Crachás & Modelos'],
            ],
            'Identidade & Acessos' => [
                ['identity-directory', 'fa-address-book', 'Identidade e Diretório'],
                ['perfis', 'fa-shield-halved', 'Perfis de Acesso'],
                ['sistemas_hc', 'fa-diagram-project', 'Aplicações SSO'],
                ['administradores', 'fa-user-shield', 'Administradores'],
            ],
            'Automação & Integrações' => [
                ['automacoes', 'fa-bolt', 'Automações e Jornada'],
                ['cloud_status', 'fa-cloud', 'Saúde das Integrações'],
                ['comunicacoes', 'fa-comments', 'Comunicações'],
            ],
            'Governança & Auditoria' => [
                ['relatorios', 'fa-chart-column', 'Relatórios'],
                ['auditoria', 'fa-file-shield', 'Trilha de Auditoria'],
                ['erros', 'fa-triangle-exclamation', 'Diagnósticos e Falhas'],
            ],
            'Administração' => [
                ['configuracoes', 'fa-sliders', 'Configurações Mestres'],
                ['changelog', 'fa-clock-rotate-left', 'Notas de Versão'],
            ],
        ];

        $out = '<div class="v4-brand"><a href="/dashboard?p=dashboard"><strong>CADCOLAB <span>ENFAS</span></strong><small>ENTERPRISE IDENTITY SUITE</small></a><button type="button" id="v4Collapse" title="Recolher menu"><i class="fa-solid fa-angles-left"></i></button></div>';
        $out .= '<div class="v4-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" id="v4ModuleSearch" placeholder="Buscar módulo…"><kbd>/</kbd></div>';
        $out .= '<nav class="v4-nav">';

        foreach ($groups as $title => $items) {
            $active = collect($items)->contains(fn ($item) => $item[0] === $page);
            $out .= '<details class="v4-group"' . ($active ? ' open' : '') . '><summary><span>' . e($title) . '</span><i class="fa-solid fa-chevron-down"></i></summary><div class="v4-items">';
            foreach ($items as [$key, $icon, $label]) {
                $cls = $key === $page ? ' active' : '';
                $out .= '<a class="v4-link' . $cls . '" data-page="' . e($key) . '" href="/dashboard?p=' . rawurlencode($key) . '" title="' . e($label) . '"><i class="fa-solid ' . e($icon) . '"></i><span>' . e($label) . '</span></a>';
            }
            $out .= '</div></details>';
        }

        $out .= '</nav><div class="v4-sidebar-foot"><strong>CADCOLAB v4.4.0</strong><span>Stable Enterprise Shell</span><small>ENFAS • 2026</small></div>';
        return $out;
    }

    private function topbarHtml(string $page): string
    {
        $titles = [
            'dashboard' => ['Visão Geral', 'Acompanhe pessoas, acessos e integrações em um só lugar.'],
            'colaboradores' => ['Colaboradores', 'Gestão completa do ciclo de vida das pessoas.'],
            'unidades' => ['Unidades', 'Estrutura organizacional e configurações por unidade.'],
            'setores' => ['Setores', 'Organização dos setores e responsabilidades.'],
            'cargos' => ['Cargos e Funções', 'Funções, responsabilidades e políticas de acesso.'],
            'identity-directory' => ['Identidade e Diretório', 'LDAP, Active Directory e sincronização corporativa.'],
            'perfis' => ['Perfis de Acesso', 'Governança de permissões e RBAC.'],
            'sistemas_hc' => ['Aplicações SSO', 'Aplicações corporativas e acesso centralizado.'],
            'administradores' => ['Administradores', 'Operadores e privilégios administrativos.'],
            'automacoes' => ['Automações e Jornada', 'Onboarding, movimentações e desligamentos.'],
            'cloud_status' => ['Saúde das Integrações', 'Microsoft 365, Google, LDAP e WhatsApp.'],
            'comunicacoes' => ['Comunicações', 'E-mails, WhatsApp e histórico de entregas.'],
            'relatorios' => ['Relatórios', 'Indicadores, exportações e visão de governança.'],
            'auditoria' => ['Trilha de Auditoria', 'Eventos, operadores e rastreabilidade.'],
            'erros' => ['Diagnósticos e Falhas', 'Erros operacionais e saúde técnica.'],
            'configuracoes' => ['Configurações Mestres', 'Parâmetros globais, integrações e segurança.'],
            'changelog' => ['Notas de Versão', 'Histórico completo de evolução do CADCOLAB.'],
        ];
        [$title, $subtitle] = $titles[$page] ?? ['CADCOLAB', 'Enterprise Identity Suite'];

        $user = e((string) (session('admin_nome') ?: session('admin_usuario') ?: 'Administrador'));
        return '<div class="v4-top-left"><button type="button" id="v4MobileMenu" aria-label="Abrir menu"><i class="fa-solid fa-bars"></i></button><div><h1>' . e($title) . '</h1><p>' . e($subtitle) . '</p></div></div><div class="v4-top-actions"><span class="v4-user"><i class="fa-regular fa-circle-user"></i>' . $user . '</span><a class="v4-logout" href="/logout"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Sair</span></a></div>';
    }
}
