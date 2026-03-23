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

                <div class="backup-controls">
                    <h2 class="backup-title">Criar Novo Backup</h2>

                    <?php if (!empty($mensagem)) echo $mensagem; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                        <input type="hidden" name="acao" value="criar">
                        <button type="submit" class="btn-primary" style="background-color: var(--success-color);">
                            🔄 Criar Backup Seguro Agora
                        </button>
                    </form>
                </div>

                <div class="relatorio">
                    <h3>Histórico Local (<?php echo count($lista); ?> arquivos)</h3>
                    <p style="font-size:0.9em; color:#666; margin-bottom:15px;">
                        💡 O sistema protege automaticamente o arquivo mais recente contra exclusão acidental.
                    </p>

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

                            <?php foreach ($lista as $index => $caminho):
                                $nome = basename($caminho);
                                $data = date('d/m/Y H:i:s', filemtime($caminho));
                                $tamanho = round(filesize($caminho) / 1024, 2) . ' KB';

                                $eh_o_ultimo = ($index === 0);
                                $classe_linha = $eh_o_ultimo ? 'class="backup-recent"' : '';
                            ?>
                                <tr <?php echo $classe_linha; ?>>
                                    <td>
                                        <?php echo $nome; ?>
                                        <?php if ($eh_o_ultimo): ?>
                                            <span class="badge-recent">MAIS RECENTE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $data; ?></td>
                                    <td><?php echo $tamanho; ?></td>
                                    <td style="text-align: center;">
                                        <a href="?baixar=<?php echo $nome; ?>" class="btn-download" title="Baixar">
                                            ⬇️ Baixar
                                        </a>

                                        <?php if ($eh_o_ultimo): ?>
                                            <span class="badge-protected" title="Protegido">
                                                🚫 Protegido
                                            </span>
                                        <?php else: ?>
                                            <a href="?apagar=<?php echo $nome; ?>" class="btn-delete"
                                                onclick="return confirm('Tem certeza absoluta que deseja apagar este backup?');"
                                                title="Excluir">
                                                🗑️ Apagar
                                            </a>
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