<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Logs de Auditoria</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/sistema.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Logs de Auditoria</h1>
            </header>

            <main>
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <div class="info-box">
                    <strong>ℹ️ Informação:</strong> Aqui você vê as últimas 500 ações realizadas no sistema. Use a pesquisa para filtrar.
                </div>

                <div class="relatorio">

                    <div class="toolbar-logs">

                        <div class="search-group">
                            <label for="filtroPainel" style="font-weight: bold; display: block; margin-bottom: 5px;">Pesquisar nos Logs:</label>
                            <input type="search" id="filtroPainel" placeholder="Digite usuário, ação ou detalhe..." class="sistema">
                        </div>

                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja apagar permanentemente os logs com mais de 1 ano?')">
                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                            <input type="hidden" name="acao" value="limpar">
                            <button type="submit" class="btn-secondary">
                                🧹 Limpar Antigos (> 1 ano)
                            </button>
                        </form>
                    </div>

                    <table class="tabela-filtrada tabela-logs">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Usuário</th>
                                <th>Ação</th>
                                <th>Detalhes</th>
                            </tr>
                        </thead>
                        <tbody id="corpoLogs">
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="4" style="padding: 30px; text-align: center; color: #999;">Nenhum registro de log encontrado.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($logs as $log):
                                $data = date('d/m/Y H:i:s', strtotime($log['data_hora']));

                                $classeAcao = '';
                                $textoAcao = strtolower($log['acao']);

                                if (strpos($textoAcao, 'apagar') !== false || strpos($textoAcao, 'excluir') !== false) {
                                    $classeAcao = 'acao-apagar';
                                } elseif (strpos($textoAcao, 'cadastrar') !== false || strpos($textoAcao, 'criar') !== false) {
                                    $classeAcao = 'acao-cadastrar';
                                } elseif (strpos($textoAcao, 'editar') !== false || strpos($textoAcao, 'atualizar') !== false) {
                                    $classeAcao = 'acao-editar';
                                } elseif (strpos($textoAcao, 'login') !== false) {
                                    $classeAcao = 'acao-login';
                                }
                            ?>
                                <tr class="item-filtrado">
                                    <td class="col-data"><?php echo $data; ?></td>
                                    <td class="col-user filtro-user"><?php echo htmlspecialchars($log['nome_usuario']); ?></td>
                                    <td class="col-acao filtro-acao <?php echo $classeAcao; ?>"><?php echo htmlspecialchars($log['acao']); ?></td>
                                    <td class="col-detalhe filtro-detalhe"><?php echo htmlspecialchars($log['detalhes']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script>
        function removerAcentos(texto) {
            if (!texto) return "";
            return texto.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        }

        document.getElementById('filtroPainel').addEventListener('keyup', function() {
            let filtro = removerAcentos(this.value);
            let linhas = document.querySelectorAll('.item-filtrado');

            linhas.forEach(function(linha) {
                let user = removerAcentos(linha.querySelector('.filtro-user').textContent);
                let acao = removerAcentos(linha.querySelector('.filtro-acao').textContent);
                let detalhe = removerAcentos(linha.querySelector('.filtro-detalhe').textContent);

                if (user.includes(filtro) || acao.includes(filtro) || detalhe.includes(filtro)) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>
