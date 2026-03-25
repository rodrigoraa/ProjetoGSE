<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gestao de Backups</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/sistema.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Gestao de Backups</h1>
            </header>
            <main>
                <div class="backup-controls">
                    <h2 class="backup-title">Criar Novo Backup</h2>

                    <?php if (!empty($mensagem)) echo $mensagem; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                        <input type="hidden" name="acao" value="criar">
                        <button type="submit" class="btn-primary" style="background-color: var(--success-color);">Criar Backup Seguro Agora</button>
                    </form>
                </div>

                <div class="relatorio">
                    <h3>Historico Local (<?php echo count($lista); ?> arquivos)</h3>
                    <p style="font-size:0.9em; color:#666; margin-bottom:15px;">O sistema protege automaticamente o arquivo mais recente contra exclusao acidental.</p>

                    <table class="tabela-filtrada">
                        <thead>
                            <tr>
                                <th>Arquivo</th>
                                <th>Data/Hora</th>
                                <th>Tamanho</th>
                                <th style="text-align: center;">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lista)): ?>
                                <tr>
                                    <td colspan="4" style="padding: 20px; text-align: center;">Nenhum backup encontrado.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($lista as $index => $caminho): ?>
                                <?php
                                $nome = basename($caminho);
                                $data = date('d/m/Y H:i:s', filemtime($caminho));
                                $tamanho = round(filesize($caminho) / 1024, 2) . ' KB';
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
                                                <button type="submit" class="btn-delete" title="Excluir">Apagar</button>
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
