<?php
$totalCertidoes = 0;
foreach ($dados_organizados as $grupoFornecedor) {
    foreach ($grupoFornecedor as $grupoTipo) {
        $totalCertidoes += count($grupoTipo);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Painel de Certidões</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/certidoes.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header class="cert-page-header">
                <h1><i class="fa-solid fa-table-cells-large"></i> Matriz de Certidões</h1>
            </header>

            <main class="cert-layout-compact" id="certMainLayout">
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <section class="cert-overview">
                    <div>
                        <h2>Painel central das certidões</h2>
                        <p>Acompanhe rapidamente os documentos ativos a partir de <?php echo (int)$ano_atual; ?>, filtre por fornecedor e destaque o que precisa de renovação ou atenção imediata.</p>
                    </div>
                    <div class="cert-overview-stats">
                        <div class="cert-stat">
                            <strong><?php echo (int)$totalCertidoes; ?></strong>
                            <span>Certidões</span>
                        </div>
                        <div class="cert-stat">
                            <strong><?php echo count($lista_fornecedores); ?></strong>
                            <span>Fornecedores</span>
                        </div>
                        <div class="cert-stat">
                            <strong><?php echo count($tipos_certidoes); ?></strong>
                            <span>Tipo de Certidões</span>
                        </div>
                    </div>
                </section>

                <div class="toolbar-matriz">
                    <div class="toolbar-stack">
                        <h3>Ações rápidas</h3>
                        <p>Cadastre, configure ou consulte o arquivo histórico sem sair do módulo.</p>
                    </div>

                    <div class="toolbar-actions">
                        <a href="/certidao/cadastrar" class="btn-novo"><i class="fa-solid fa-plus"></i> Nova Certidão</a>
                        <a href="/certidao/configurar" class="btn-arquivo"><i class="fa-solid fa-gear"></i> Tipos/Fornecedores</a>
                        <a href="/certidao/arquivadas" class="btn-arquivo"><i class="fa-solid fa-folder-open"></i> Arquivadas</a>
                    </div>

                    <div class="toolbar-filters">
                        <div class="legenda">
                            <span class="status-pill danger"><span class="dot dot-vencida"></span> Vencida</span>
                            <span class="status-pill warning"><span class="dot dot-avencer"></span> A vencer</span>
                            <span class="status-pill success"><span class="dot dot-vigente"></span> Vigente</span>
                        </div>

                        <div class="filtro-box">
                            <label for="filtroFornecedor"><i class="fa-solid fa-truck-fast"></i></label>
                            <select id="filtroFornecedor" onchange="aplicarFiltros()">
                                <option value="todos">Todos os Fornecedores</option>
                                <?php foreach ($lista_fornecedores as $f): ?>
                                    <option value="<?php echo e($f); ?>"><?php echo e($f); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filtro-box">
                            <label for="filtroStatus"><i class="fa-solid fa-filter"></i></label>
                            <select id="filtroStatus" onchange="aplicarFiltros()">
                                <option value="todos">Mostrar Todas</option>
                                <option value="status-vigente">Apenas Vigentes</option>
                                <option value="status-avencer">Apenas A Vencer</option>
                                <option value="status-vencida">Apenas Vencidas</option>
                            </select>
                        </div>

                        <button type="button" class="compact-toggle is-active" id="toggleCompacto" aria-pressed="true">
                            <i class="fa-solid fa-compress"></i> Modo compacto
                        </button>
                    </div>
                </div>

                <div class="matriz-meta">
                    <p>Visualizando <strong><?php echo (int)$totalCertidoes; ?></strong> registros distribuídos entre fornecedores e tipos ativos.</p>
                    <span class="count-pill" id="contadorVisivel">Todos os cartões visíveis</span>
                </div>

                <div class="matriz-container">
                    <table class="tabela-matriz">
                        <thead>
                            <tr>
                                <th class="th-tipo">TIPO / FORNECEDOR</th>
                                <?php foreach ($lista_fornecedores as $fornecedor): ?>
                                    <th class="th-fornecedor" data-fornecedor="<?php echo e($fornecedor); ?>">
                                        <span><?php echo e($fornecedor); ?></span>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tipos_certidoes)): ?>
                                <tr>
                                    <td colspan="<?php echo count($lista_fornecedores) + 1; ?>" class="empty-state">Nenhuma certidão vigente para este ano.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($tipos_certidoes as $tipo): ?>
                                <tr>
                                    <td class="nome-tipo"><?php echo e($tipo); ?></td>

                                    <?php foreach ($lista_fornecedores as $fornecedor): ?>
                                        <?php $lista = $dados_organizados[$fornecedor][$tipo] ?? []; ?>
                                        <td class="td-fornecedor" data-fornecedor="<?php echo e($fornecedor); ?>">
                                            <?php if (empty($lista)): ?>
                                                <span class="cell-empty">-</span>
                                            <?php else: ?>
                                                <?php foreach ($lista as $d): ?>
                                                    <?php
                                                    $venc = new DateTime($d['data_vencimento']);
                                                    $emiss = new DateTime($d['data_emissao']);
                                                    $hoje = new DateTime();
                                                    $dias = (int)$hoje->diff($venc)->format('%r%a');
                                                    $bg = 'status-vigente';
                                                    $st = $dias . ' dias';

                                                    if ($dias < 0) {
                                                        $bg = 'status-vencida';
                                                        $st = 'Há ' . abs($dias) . ' dias';
                                                    } elseif ($dias <= 15) {
                                                        $bg = 'status-avencer';
                                                        $st = 'Em ' . $dias . ' dias';
                                                    }
                                                    ?>
                                                    <div class="cert-card <?php echo e($bg); ?>" data-status="<?php echo e($bg); ?>">
                                                        <div class="cert-header">
                                                            <span class="cert-badge"><?php echo e($st); ?></span>
                                                        </div>

                                                        <div class="cert-body">
                                                            <div class="cert-date">
                                                                <span class="date-lbl">Emissão</span>
                                                                <span class="date-val"><?php echo $emiss->format('d/m/y'); ?></span>
                                                            </div>
                                                            <div class="cert-date text-right">
                                                                <span class="date-lbl">Validade</span>
                                                                <span class="date-val"><?php echo $venc->format('d/m/y'); ?></span>
                                                            </div>
                                                        </div>

                                                        <div class="cert-actions">
                                                            <a href="/certidao/cadastrar?f=<?php echo (int)$d['id_fornecedor']; ?>&t=<?php echo (int)$d['id_tipo_certidao']; ?>&renovar_id=<?php echo (int)$d['id']; ?>" class="action-btn text-success" title="Renovar Certidão"><i class="fa-solid fa-rotate"></i></a>

                                                            <form action="/certidao/arquivar/<?php echo (int)$d['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Mover para os arquivos?');">
                                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                                <button type="submit" class="action-btn text-warning" title="Arquivar Certidão" style="border:0; cursor:pointer;"><i class="fa-solid fa-box-archive"></i></button>
                                                            </form>

                                                            <?php if (!empty($d['arquivo_pdf'])): ?>
                                                                <a href="/uploads/certidoes/<?php echo rawurlencode($d['arquivo_pdf']); ?>" target="_blank" class="action-btn text-info" title="Ver PDF"><i class="fa-solid fa-file-pdf"></i></a>
                                                            <?php else: ?>
                                                                <span class="action-btn disabled" title="Sem PDF"><i class="fa-solid fa-file-pdf"></i></span>
                                                            <?php endif; ?>

                                                            <a href="/certidao/editar/<?php echo (int)$d['id']; ?>" class="action-btn text-primary" title="Editar informações"><i class="fa-solid fa-pen"></i></a>

                                                            <form action="/certidao/excluir/<?php echo (int)$d['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Apagar permanentemente?');">
                                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                                <input type="hidden" name="origem" value="lista">
                                                                <button type="submit" class="action-btn text-danger" title="Apagar" style="border:0; cursor:pointer;"><i class="fa-solid fa-trash-can"></i></button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script>
        function atualizarContadorVisivel() {
            const cardsVisiveis = Array.from(document.querySelectorAll('.cert-card')).filter((card) => card.style.display !== 'none').length;
            const contador = document.getElementById('contadorVisivel');
            contador.textContent = cardsVisiveis + ' cartões visíveis';
        }

        function aplicarFiltros() {
            const filtroStatus = document.getElementById('filtroStatus').value;
            const filtroFornecedor = document.getElementById('filtroFornecedor').value;

            document.querySelectorAll('.th-fornecedor').forEach((th) => {
                th.style.display = filtroFornecedor === 'todos' || th.getAttribute('data-fornecedor') === filtroFornecedor ? '' : 'none';
            });

            document.querySelectorAll('.td-fornecedor').forEach((td) => {
                td.style.display = filtroFornecedor === 'todos' || td.getAttribute('data-fornecedor') === filtroFornecedor ? '' : 'none';
            });

            document.querySelectorAll('.cert-card').forEach((cert) => {
                cert.style.display = filtroStatus === 'todos' || cert.getAttribute('data-status') === filtroStatus ? 'flex' : 'none';
            });

            atualizarContadorVisivel();
        }

        const certMainLayout = document.getElementById('certMainLayout');
        const toggleCompacto = document.getElementById('toggleCompacto');
        const preferenciaCompacta = localStorage.getItem('certidoes_modo_compacto');

        function aplicarModoCompacto(ativo) {
            certMainLayout.classList.toggle('cert-layout-compact', ativo);
            toggleCompacto.classList.toggle('is-active', ativo);
            toggleCompacto.setAttribute('aria-pressed', ativo ? 'true' : 'false');
            toggleCompacto.innerHTML = ativo
                ? '<i class="fa-solid fa-compress"></i> Modo compacto'
                : '<i class="fa-solid fa-expand"></i> Modo confortável';

            localStorage.setItem('certidoes_modo_compacto', ativo ? '1' : '0');
        }

        toggleCompacto.addEventListener('click', function() {
            aplicarModoCompacto(!certMainLayout.classList.contains('cert-layout-compact'));
        });

        if (preferenciaCompacta === '0') {
            aplicarModoCompacto(false);
        } else {
            aplicarModoCompacto(true);
        }

        atualizarContadorVisivel();
    </script>
</body>

</html>
