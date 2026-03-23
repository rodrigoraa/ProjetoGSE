<aside class="sidebar">
    <div class="sidebar-header">
        <h2>🎓 Gestor Escolar</h2>
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
            <span>📊</span> Painel Geral
        </a>
        <a href="/aluno" class="sidebar-link <?php echo is_active($uri, '/aluno'); ?>">
            <span>👥</span> Gestão de Alunos
        </a>
        <a href="/certidao" class="sidebar-link <?php echo is_active($uri, '/certidao'); ?>">
            <span>📜</span> Certidões
        </a>
        <a href="/contrato" class="sidebar-link <?php echo is_active($uri, '/contrato'); ?>">
            <span>📄</span> Contratos
        </a>
        <a href="/passivo" class="sidebar-link <?php echo is_active($uri, '/passivo'); ?>">
            <span>📦</span> Arquivo Passivo
        </a>
        <a href="/relatorio" class="sidebar-link">
            <span>📊</span> Relatórios
        </a>

        <a href="/etiqueta" class="sidebar-link">
            <span>🏷️</span> Etiquetas
        </a>

        <a href="https://meteo.eesjv.com.br" target="_blank" class="sidebar-link">
            <span>🌤️</span> Estação Meteorológica
        </a>

        <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
            <div style="border-top:1px solid rgba(255,255,255,0.1); margin:15px 0;"></div>
            <small style="padding-left:20px; color:rgba(255,255,255,0.5); text-transform:uppercase; font-size:0.75rem;">Admin</small>

            <a href="/usuario" class="sidebar-link <?php echo is_active($uri, '/usuario'); ?>">
                <span>⚙️</span> Usuários
            </a>
            <a href="/sistema/backups" class="sidebar-link <?php echo is_active($uri, '/backups'); ?>">
                <span>💾</span> Backups
            </a>
            <a href="/sistema/logs" class="sidebar-link <?php echo is_active($uri, '/logs'); ?>">
                <span>👁️</span> Logs
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div>Olá, <strong><?php echo $_SESSION['usuario_nome'] ?? 'User'; ?></strong></div>
        <a href="/login/sair" class="btn-sair">Sair do Sistema</a>
    </div>
</aside>