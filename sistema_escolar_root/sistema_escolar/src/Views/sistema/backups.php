<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gestão de Backups</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/sistema.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Gestão de Backups</h1>
            </header>
            <main>
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <div class="backup-controls">
                    <h2 class="backup-title">Criar Novo Backup</h2>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                        <input type="hidden" name="acao" value="criar">
                        <button type="submit" class="btn-primary" style="background-color: var(--success-color);">Criar Backup Seguro Agora</button>
                    </form>
                </div>

                <div class="relatorio">
                    <h3>Histórico Local (<?php echo count($lista); ?> arquivos)</h3>
                    <p style="font-size:0.9em; color:#666; margin-bottom:8px;">O sistema protege automaticamente o arquivo mais recente contra exclusão acidental.</p>
                    <p class="backup-path-info">Pasta monitorada: <?php echo e($pasta_backups ?? 'database/backups/'); ?></p>

                    <table class="tabela-filtrada">
                        <thead>
                            <tr>
                                <th>Arquivo</th>
                                <th>Data/Hora</th>
                                <th>Tamanho</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lista)): ?>
                                <tr>
                                    <td colspan="4" style="padding: 20px; text-align: center;">Nenhum backup encontrado.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($lista as $index => $backup): ?>
                                <?php
                                $caminhoFallback = is_array($backup) ? ($backup['caminho'] ?? '') : (string)$backup;
                                $nome = is_array($backup) ? ($backup['nome'] ?? basename($caminhoFallback)) : basename($caminhoFallback);
                                $timestamp = is_array($backup) ? (int)($backup['timestamp'] ?? 0) : (int)filemtime($caminhoFallback);
                                $data = $timestamp > 0 ? date('d/m/Y H:i:s', $timestamp) : 'Data indisponível';
                                $tamanhoBytes = is_array($backup) ? (int)($backup['tamanho'] ?? 0) : (int)filesize($caminhoFallback);
                                $tamanho = round($tamanhoBytes / 1024, 2) . ' KB';
                                $eh_o_ultimo = ($index === 0);
                                $classe_linha = $eh_o_ultimo ? 'class="backup-recent"' : '';
                                ?>
                                <tr <?php echo $classe_linha; ?>>
                                    <td>
                                        <?php echo e($nome); ?>
                                        <?php if ($eh_o_ultimo): ?>
                                            <span class="badge-recent">MAIS RECENTE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($data); ?></td>
                                    <td><?php echo e($tamanho); ?></td>
                                    <td style="text-align: center;">
                                        <a href="?baixar=<?php echo urlencode($nome); ?>" class="btn-download" title="Baixar">Baixar</a>

                                        <?php if ($eh_o_ultimo): ?>
                                            <span class="badge-protected" title="Protegido">Protegido</span>
                                        <?php else: ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza absoluta que deseja apagar este backup?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                <input type="hidden" name="acao" value="apagar">
                                                <input type="hidden" name="arquivo" value="<?php echo e($nome); ?>">
                                                <button type="submit" class="btn-delete" title="🗑️ Apagar">🗑️ Apagar</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
