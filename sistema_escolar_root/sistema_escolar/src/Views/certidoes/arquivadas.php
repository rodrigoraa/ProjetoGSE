<?php $totalArquivadas = count($certidoes); ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Arquivo de Certidões</title>
    <link rel="stylesheet" href="/assets/css/painel.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/certidoes.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header class="cert-page-header">
                <h1><i class="fa-solid fa-folder-open"></i> Certidões Arquivadas</h1>
            </header>

            <main>
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <section class="cert-overview">
                    <div>
                        <h2>Histórico completo das certidões movimentadas</h2>
                        <p>Consulte documentos arquivados por ano, recupere itens para a lista principal e mantenha o histórico limpo sem perder rastreabilidade.</p>
                    </div>
                    <div class="cert-overview-stats">
                        <div class="cert-stat">
                            <strong><?php echo (int)$totalArquivadas; ?></strong>
                            <span>registros no filtro</span>
                        </div>
                        <div class="cert-stat">
                            <strong><?php echo $ano_filtro === 'todos' ? 'Todos' : e($ano_filtro); ?></strong>
                            <span>ano selecionado</span>
                        </div>
                        <div class="cert-stat">
                            <strong><?php echo count($anos_disponiveis); ?></strong>
                            <span>anos disponíveis</span>
                        </div>
                    </div>
                </section>

                <div class="toolbar-arquivo">
                    <div class="toolbar-stack">
                        <h3>Filtro do histórico</h3>
                        <p>Selecione um ano específico ou veja todo o acervo arquivado.</p>
                    </div>

                    <form action="/certidao/arquivadas" method="GET" class="form-filtro-ano">
                        <div class="filtro-box">
                            <label for="ano"><i class="fa-regular fa-calendar-days"></i> Ano do Histórico:</label>
                            <select name="ano" id="ano" onchange="this.form.submit()">
                                <option value="todos" <?php echo ($ano_filtro === 'todos') ? 'selected' : ''; ?>>Todos os Anos</option>
                                <?php foreach ($anos_disponiveis as $ano): ?>
                                    <option value="<?php echo e($ano); ?>" <?php echo ($ano == $ano_filtro) ? 'selected' : ''; ?>><?php echo e($ano); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <a href="/certidao" class="btn-arquivo"><i class="fa-solid fa-arrow-left"></i> Voltar para Vigentes</a>
                </div>

                <div class="matriz-meta">
                    <p>Exibindo <strong><?php echo (int)$totalArquivadas; ?></strong> certidões no histórico atual.</p>
                    <span class="count-pill"><?php echo $ano_filtro === 'todos' ? 'Visão completa do arquivo' : 'Filtro: ' . e($ano_filtro); ?></span>
                </div>

                <div class="arquivo-shell">
                    <div class="arquivo-table-wrap">
                        <table class="tabela-arquivo">
                            <thead>
                                <tr>
                                    <th>Fornecedor</th>
                                    <th>Tipo de Certidão</th>
                                    <th>Emissão</th>
                                    <th>Vencimento</th>
                                    <th style="text-align:center;">Documento</th>
                                    <th style="text-align:right;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($certidoes)): ?>
                                    <tr>
                                        <td colspan="6" class="empty-state arquivo-empty">Nenhuma certidão arquivada encontrada para o filtro selecionado.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($certidoes as $reg): ?>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--primary-color);"><?php echo e($reg['fornecedor']); ?></td>
                                        <td style="font-weight: 600; color: #334155;"><?php echo e($reg['tipo_certidao']); ?></td>
                                        <td><span class="data-badge"><?php echo date('d/m/Y', strtotime($reg['data_emissao'])); ?></span></td>
                                        <td><span class="data-badge"><?php echo date('d/m/Y', strtotime($reg['data_vencimento'])); ?></span></td>
                                        <td style="text-align:center;">
                                            <a href="/certidao/visualizarPdf/<?php echo (int)$reg['id']; ?>?origem=arquivo&ano=<?php echo urlencode((string)$ano_filtro); ?>" class="pdf-btn" title="Baixar/Ver PDF"><i class="fa-solid fa-file-pdf"></i> Ver PDF</a>
                                        </td>
                                        <td style="text-align: right;">
                                            <div class="cert-actions" style="border: none; padding: 0; margin: 0; justify-content: flex-end; gap: 8px;">
                                                <form action="/certidao/desarquivar/<?php echo (int)$reg['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Deseja desarquivar esta certidão e enviá-la para a tela inicial?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                    <button type="submit" class="action-btn text-success" title="Desarquivar" style="border:0; cursor:pointer;"><i class="fa-solid fa-rotate-left"></i></button>
                                                </form>

                                                <a href="/certidao/editar/<?php echo (int)$reg['id']; ?>" class="action-btn text-primary" title="Editar informações"><i class="fa-solid fa-pen"></i></a>

                                                <?php if ($_SESSION['usuario_tipo'] == 'admin'): ?>
                                                    <form action="/certidao/excluir/<?php echo (int)$reg['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Deseja excluir esta certidão permanentemente do histórico?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                        <input type="hidden" name="origem" value="arquivo">
                                                        <input type="hidden" name="ano" value="<?php echo e($ano_filtro); ?>">
                                                        <button type="submit" class="action-btn text-danger" title="Apagar definitivamente" style="border:0; cursor:pointer;"><i class="fa-solid fa-trash-can"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
