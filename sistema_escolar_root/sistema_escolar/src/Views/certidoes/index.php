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
    <link rel="stylesheet" href="/assets/css/certidoes.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/certidoes.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header class="cert-page-header">
                <h1><i class="fa-solid fa-table-cells-large"></i> Matriz de Certidões</h1>
            </header>

            <main id="certMainLayout">
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <section class="cert-overview">
                    <div>
                        <h2>Painel central das certidões</h2>
                        <p>Acompanhe rapidamente os documentos ativos, filtre por fornecedor e destaque o que precisa de renovação ou atenção imediata.</p>
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
                            <span>Tipos</span>
                        </div>
                    </div>
                </section>

                <div class="toolbar-matriz">
                    <div class="toolbar-stack">
                        <h3>Ações rápidas</h3>
                        <p>Cadastre, configure ou consulte o histórico sem sair do módulo.</p>
                    </div>

                    <div class="toolbar-actions">
                        <a href="/certidao/cadastrar" class="btn-novo"><i class="fa-solid fa-plus"></i> Nova Certidão</a>
                        <a href="/certidao/configurar" class="btn-arquivo"><i class="fa-solid fa-gear"></i> Tipos/Fornecedores</a>
                        <a href="/certidao/arquivadas" class="btn-arquivo"><i class="fa-solid fa-folder-open"></i> Arquivadas</a>
                    </div>

                    <div class="toolbar-filters">
                        <div class="legenda">
                            <button type="button" class="status-pill status-pill-filter is-active" data-status-value="todos">
                                <span class="dot"></span> Todas
                            </button>
                            <button type="button" class="status-pill status-pill-filter danger" data-status-value="status-vencida">
                                <span class="dot dot-vencida"></span> Vencida
                            </button>
                            <button type="button" class="status-pill status-pill-filter warning" data-status-value="status-avencer">
                                <span class="dot dot-avencer"></span> A vencer
                            </button>
                            <button type="button" class="status-pill status-pill-filter success" data-status-value="status-vigente">
                                <span class="dot dot-vigente"></span> Vigente
                            </button>
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

                        <button type="button" class="compact-toggle" id="toggleTelaCheia" aria-pressed="false">
                            <i class="fa-solid fa-expand"></i> Tela cheia
                        </button>

                        <button type="button" class="compact-toggle" id="togglePendencias" aria-pressed="false">
                            <i class="fa-solid fa-triangle-exclamation"></i> Somente pendências
                        </button>
                    </div>
                </div>

                <div class="matriz-meta">
                    <p>Visualizando <strong><?php echo (int)$totalCertidoes; ?></strong> registros distribuídos entre fornecedores e tipos ativos.</p>
                    <span class="count-pill" id="contadorVisivel">Todos os cartões visíveis</span>
                </div>

                <div class="fullscreen-status-bar" id="fullscreenStatusBar">
                    <button type="button" class="fullscreen-status-btn is-active" data-status-value="todos">Todas</button>
                    <button type="button" class="fullscreen-status-btn" data-status-value="status-vigente">Vigentes</button>
                    <button type="button" class="fullscreen-status-btn" data-status-value="status-avencer">A vencer</button>
                    <button type="button" class="fullscreen-status-btn" data-status-value="status-vencida">Vencidas</button>
                    <button type="button" class="fullscreen-exit-btn" id="fullscreenExitBtn">
                        <i class="fa-solid fa-compress"></i> Sair
                    </button>
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
                                <tr class="matriz-row" data-tipo="<?php echo e(mb_strtolower($tipo, 'UTF-8')); ?>">
                                    <td class="nome-tipo"><?php echo e($tipo); ?></td>

                                    <?php foreach ($lista_fornecedores as $fornecedor): ?>
                                        <?php $lista = $dados_organizados[$fornecedor][$tipo] ?? []; ?>
                                        <td class="td-fornecedor" data-fornecedor="<?php echo e($fornecedor); ?>" data-fornecedor-texto="<?php echo e(mb_strtolower($fornecedor, 'UTF-8')); ?>">
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
                                                            <a href="/certidao/cadastrar?f=<?php echo (int)$d['id_fornecedor']; ?>&t=<?php echo (int)$d['id_tipo_certidao']; ?>&renovar_id=<?php echo (int)$d['id']; ?>" class="action-btn text-success" title="Renovar certidão"><i class="fa-solid fa-rotate"></i></a>

                                                            <form action="/certidao/arquivar/<?php echo (int)$d['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Mover para os arquivos?');">
                                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                                <button type="submit" class="action-btn text-warning" title="Arquivar certidão" style="border:0; cursor:pointer;"><i class="fa-solid fa-box-archive"></i></button>
                                                            </form>

                                                            <a href="/certidao/visualizarPdf/<?php echo (int)$d['id']; ?>?origem=lista" class="action-btn text-info" title="Ver PDF"><i class="fa-solid fa-file-pdf"></i></a>

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
        let somentePendenciasAtivo = false;
        let statusFiltroAtual = 'todos';

        function atualizarContadorVisivel() {
            const cardsVisiveis = Array.from(document.querySelectorAll('.cert-card')).filter((card) => card.style.display !== 'none').length;
            const contador = document.getElementById('contadorVisivel');
            contador.textContent = cardsVisiveis + ' cartões visíveis';
        }

        function sincronizarBotoesStatus() {
            const filtroStatusAtual = statusFiltroAtual;
            statusPillBtns.forEach((btn) => {
                const valor = btn.getAttribute('data-status-value') || 'todos';
                btn.classList.toggle('is-active', valor === filtroStatusAtual);
            });
            fullscreenStatusBtns.forEach((btn) => {
                const valor = btn.getAttribute('data-status-value') || 'todos';
                btn.classList.toggle('is-active', valor === filtroStatusAtual);
            });
        }

        function aplicarFiltros() {
            const filtroStatus = statusFiltroAtual;
            const filtroFornecedor = document.getElementById('filtroFornecedor').value;

            document.querySelectorAll('.th-fornecedor').forEach((th) => {
                const bateFornecedor = filtroFornecedor === 'todos' || th.getAttribute('data-fornecedor') === filtroFornecedor;
                th.style.display = bateFornecedor ? '' : 'none';
            });

            document.querySelectorAll('.matriz-row').forEach((row) => {
                let linhaTemConteudoVisivel = false;

                row.querySelectorAll('.td-fornecedor').forEach((td) => {
                    const cards = Array.from(td.querySelectorAll('.cert-card'));

                    cards.forEach((cert) => {
                        const statusAtual = cert.getAttribute('data-status');
                        const bateStatus = filtroStatus === 'todos' || statusAtual === filtroStatus;
                        const batePendencia = !somentePendenciasAtivo || statusAtual === 'status-avencer' || statusAtual === 'status-vencida';
                        cert.style.display = bateStatus && batePendencia ? 'flex' : 'none';
                    });

                    const existeCardVisivel = cards.some((card) => card.style.display !== 'none');
                    const bateFornecedor = filtroFornecedor === 'todos' || td.getAttribute('data-fornecedor') === filtroFornecedor;
                    const exibirCelula = bateFornecedor;

                    td.style.display = exibirCelula ? '' : 'none';

                    if (exibirCelula && existeCardVisivel) {
                        linhaTemConteudoVisivel = true;
                    }
                });

                row.style.display = linhaTemConteudoVisivel ? '' : 'none';
            });

            sincronizarBotoesStatus();
            atualizarContadorVisivel();
        }

        const certMainLayout = document.getElementById('certMainLayout');
        const toggleTelaCheia = document.getElementById('toggleTelaCheia');
        const togglePendencias = document.getElementById('togglePendencias');
        const statusPillBtns = Array.from(document.querySelectorAll('.status-pill-filter'));
        const fullscreenStatusBtns = Array.from(document.querySelectorAll('.fullscreen-status-btn'));
        const fullscreenExitBtn = document.getElementById('fullscreenExitBtn');
        const preferenciaTelaCheia = localStorage.getItem('certidoes_tela_cheia');
        const preferenciaPendencias = localStorage.getItem('certidoes_somente_pendencias');

        function sincronizarTelaCheia() {
            const ativo = !!document.fullscreenElement;
            document.body.classList.toggle('cert-fullscreen', ativo);
            toggleTelaCheia.classList.toggle('is-active', ativo);
            toggleTelaCheia.setAttribute('aria-pressed', ativo ? 'true' : 'false');
            toggleTelaCheia.innerHTML = ativo
                ? '<i class="fa-solid fa-compress"></i> Sair da tela cheia'
                : '<i class="fa-solid fa-expand"></i> Tela cheia';

            localStorage.setItem('certidoes_tela_cheia', ativo ? '1' : '0');
        }

        toggleTelaCheia.addEventListener('click', async function() {
            try {
                if (!document.fullscreenElement) {
                    await document.documentElement.requestFullscreen();
                } else {
                    await document.exitFullscreen();
                }
            } catch (error) {
                console.error('Não foi possível alternar a tela cheia.', error);
            }
        });

        fullscreenExitBtn.addEventListener('click', async function() {
            if (!document.fullscreenElement) {
                return;
            }

            try {
                await document.exitFullscreen();
            } catch (error) {
                console.error('Não foi possível sair da tela cheia.', error);
            }
        });

        togglePendencias.addEventListener('click', function() {
            somentePendenciasAtivo = !somentePendenciasAtivo;
            togglePendencias.classList.toggle('is-active', somentePendenciasAtivo);
            togglePendencias.setAttribute('aria-pressed', somentePendenciasAtivo ? 'true' : 'false');
            localStorage.setItem('certidoes_somente_pendencias', somentePendenciasAtivo ? '1' : '0');
            aplicarFiltros();
        });

        statusPillBtns.forEach((btn) => {
            btn.addEventListener('click', function() {
                statusFiltroAtual = btn.getAttribute('data-status-value') || 'todos';
                aplicarFiltros();
            });
        });

        fullscreenStatusBtns.forEach((btn) => {
            btn.addEventListener('click', function() {
                statusFiltroAtual = btn.getAttribute('data-status-value') || 'todos';
                aplicarFiltros();
            });
        });

        document.addEventListener('fullscreenchange', sincronizarTelaCheia);

        if (preferenciaTelaCheia === '1') {
            sincronizarTelaCheia();
        }

        if (preferenciaPendencias === '1') {
            somentePendenciasAtivo = true;
            togglePendencias.classList.add('is-active');
            togglePendencias.setAttribute('aria-pressed', 'true');
        }

        const tabelaMatriz = document.querySelector('.tabela-matriz');
        if (tabelaMatriz) {
            tabelaMatriz.querySelectorAll('tbody tr').forEach((row) => {
                row.querySelectorAll('.td-fornecedor').forEach((cell, colIndex) => {
                    cell.addEventListener('mouseenter', function() {
                        row.classList.add('row-focus');
                        tabelaMatriz.querySelectorAll(`tbody tr td:nth-child(${colIndex + 2}), thead tr th:nth-child(${colIndex + 2})`).forEach((item) => {
                            item.classList.add('col-focus');
                        });
                    });

                    cell.addEventListener('mouseleave', function() {
                        row.classList.remove('row-focus');
                        tabelaMatriz.querySelectorAll('.col-focus').forEach((item) => item.classList.remove('col-focus'));
                    });
                });
            });
        }

        sincronizarTelaCheia();
        aplicarFiltros();
    </script>
</body>

</html>
