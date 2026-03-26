<script>
    (() => {
        const faviconHref = '/assets/image/logo_escola.png';
        let favicon = document.querySelector('link[rel="icon"]');

        if (!favicon) {
            favicon = document.createElement('link');
            favicon.rel = 'icon';
            favicon.type = 'image/png';
            document.head.appendChild(favicon);
        }

        favicon.href = faviconHref;
    })();
</script>

<aside class="sidebar">
    <div class="sidebar-header">
        <h2>GSE</h2>
    </div>

    <nav>
        <?php
        $uri = $_SERVER['REQUEST_URI'];
        function is_active($uri, $rota)
        {
            return (strpos($uri, $rota) !== false) ? 'active' : '';
        }
        ?>

        <a href="/painel" class="sidebar-link <?php echo is_active($uri, '/painel'); ?>">
            <span class="sidebar-icon">🏠</span>
            <span>Painel Geral</span>
        </a>
        <a href="/aluno" class="sidebar-link <?php echo is_active($uri, '/aluno'); ?>">
            <span class="sidebar-icon">🎓</span>
            <span>Gestão de Alunos</span>
        </a>
        <a href="/certidao" class="sidebar-link <?php echo is_active($uri, '/certidao'); ?>">
            <span class="sidebar-icon">📄</span>
            <span>Certidões</span>
        </a>
        <a href="/contrato" class="sidebar-link <?php echo is_active($uri, '/contrato'); ?>">
            <span class="sidebar-icon">📝</span>
            <span>Contratos</span>
        </a>
        <a href="/passivo" class="sidebar-link <?php echo is_active($uri, '/passivo'); ?>">
            <span class="sidebar-icon">📦</span>
            <span>Arquivo Passivo</span>
        </a>
        <a href="/relatorio" class="sidebar-link">
            <span class="sidebar-icon">📊</span>
            <span>Relatórios</span>
        </a>
        <a href="/etiqueta" class="sidebar-link">
            <span class="sidebar-icon">🏷️</span>
            <span>Etiquetas</span>
        </a>
        <a href="https://meteo.eesjv.com.br" target="_blank" rel="noopener noreferrer" class="sidebar-link">
            <span class="sidebar-icon">🌦️</span>
            <span>Estação Meteorológica</span>
        </a>

        <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
            <div class="sidebar-divider"></div>
            <small class="sidebar-section-label">Admin</small>

            <a href="/usuario" class="sidebar-link <?php echo is_active($uri, '/usuario'); ?>">
                <span class="sidebar-icon">👥</span>
                <span>Usuários</span>
            </a>
            <a href="/sistema/backups" class="sidebar-link <?php echo is_active($uri, '/backups'); ?>">
                <span class="sidebar-icon">💾</span>
                <span>Backups</span>
            </a>
            <a href="/sistema/logs" class="sidebar-link <?php echo is_active($uri, '/logs'); ?>">
                <span class="sidebar-icon">📜</span>
                <span>Logs</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div>Olá, <strong><?php echo e($_SESSION['usuario_nome'] ?? 'User'); ?></strong></div>
        <a href="/login/sair" class="btn-sair">Sair do Sistema</a>
    </div>
</aside>
