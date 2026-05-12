<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Detalhes do Pedido #<?php echo (int)$contrato['id']; ?></title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/alunos.css'); ?>">
    <link rel="stylesheet" href="/assets/css/contrato.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/contrato.css'); ?>">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Detalhes do Pedido</h1>
                <div class="contrato-meta">ID: #<?php echo (int)$contrato['id']; ?> | Registrado em: <?php echo date('d/m/Y', strtotime($contrato['criado_em'])); ?></div>
            </header>

            <main>
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <div class="toolbar-alunos">
                    <div class="toolbar-actions">
                        <a href="/contrato" class="btn-secondary">Voltar</a>
                        <a href="/contrato/editar/<?php echo (int)$contrato['id']; ?>" class="btn-warning">✏️ Editar Geral</a>
                        <a href="/contrato/imprimir/<?php echo (int)$contrato['id']; ?>" target="_blank" class="btn-primary" style="background-color: #4CAF50;">🖨️ Imprimir Pedido</a>
                    </div>
                </div>

                <?php
                $total_produtos_geral = array_sum(array_column($produtos, 'valor_total'));
                $saldo_geral = $contrato['valor_total'] - $total_produtos_geral;
                ?>

                <div class="contrato-header <?php echo !empty($contrato['faturado']) ? 'contrato-header-faturado' : ''; ?>">
                    <div class="contrato-title-row">
                        <div>
                            <h2 class="contrato-summary-title"><?php echo e($contrato['titulo']); ?></h2>
                            <span class="<?php echo !empty($contrato['faturado']) ? 'badge-faturado' : 'badge-nao-faturado'; ?>">
                                <?php echo !empty($contrato['faturado']) ? 'Faturado' : 'Nao faturado'; ?>
                            </span>
                        </div>

                        <form action="/contrato/salvar_faturamento/<?php echo (int)$contrato['id']; ?>" method="POST" class="contrato-status-form">
                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                            <label class="contrato-check-inline">
                                <input type="checkbox" name="faturado" value="1" <?php echo !empty($contrato['faturado']) ? 'checked' : ''; ?>>
                                <span>Pedido faturado</span>
                            </label>
                            <button type="submit" class="btn-secondary btn-sm">Salvar status</button>
                        </form>
                    </div>
                    <div class="resumo-financeiro">
                        <div>
                            <p class="resumo-label">Teto do Pedido</p>
                            <p class="resumo-valor money-primary">R$ <?php echo number_format($contrato['valor_total'], 2, ',', '.'); ?></p>
                        </div>
                        <div>
                            <p class="resumo-label">Total Gasto (Todas as notas)</p>
                            <p class="resumo-valor">R$ <?php echo number_format($total_produtos_geral, 2, ',', '.'); ?></p>
                        </div>
                        <div>
                            <p class="resumo-label">Saldo Final Restante</p>
                            <p class="resumo-valor summary-highlight" style="color: <?php echo ($saldo_geral < 0) ? '#ff4c4c' : '#28a745'; ?>;">R$ <?php echo number_format($saldo_geral, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="tabs-container">
                    <div class="tabs-header">
                        <?php foreach ($folhas as $f): ?>
                            <button class="tab-btn <?php echo ($f['numero_folha'] == ($aba_ativa ?? 1)) ? 'active' : ''; ?>" type="button" onclick="abrirAba(event, <?php echo (int)$f['numero_folha']; ?>)">
                                Nota <?php echo (int)$f['numero_folha']; ?>
                            </button>
                        <?php endforeach; ?>

                        <form action="/contrato/adicionar_folha/<?php echo (int)$contrato['id']; ?>" method="POST" onsubmit="return confirm('Deseja adicionar uma nova folha a este pedido?');">
                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                            <button type="submit" class="tab-btn">➕</button>
                        </form>
                    </div>

                    <?php foreach ($folhas as $f): ?>
                        <?php
                        $num_folha = $f['numero_folha'];
                        $produtos_desta_folha = array_filter($produtos, function ($p) use ($num_folha) {
                            return ($p['numero_folha'] ?: 1) == $num_folha;
                        });
                        ?>
                        <div id="aba-<?php echo (int)$num_folha; ?>" class="tab-content <?php echo ($num_folha == ($aba_ativa ?? 1)) ? 'active' : ''; ?>">
                            <div class="relatorio">
                                <div class="folha-header">
                                    <div>
                                        <h3 class="form-section-title contrato-summary-title">Itens da nota <?php echo (int)$num_folha; ?></h3>
                                        <div class="folha-meta">
                                            <span>
                                                Total acumulado desta nota: <strong style="font-size: 1.1em; color: #007bff;">R$ <?php echo number_format($f['valor_folha'], 2, ',', '.'); ?></strong>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="contrato-faturamento-card">
                                        <form action="/contrato/salvar_data_faturamento_folha/<?php echo (int)$contrato['id']; ?>/<?php echo (int)$num_folha; ?>" method="POST" class="contrato-faturamento-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                            <label for="data_faturamento_<?php echo (int)$num_folha; ?>" class="form-grid-label contrato-faturamento-label">Data de faturamento</label>
                                            <div class="contrato-faturamento-controls">
                                                <input type="date" id="data_faturamento_<?php echo (int)$num_folha; ?>" name="data_faturamento" class="sistema contrato-faturamento-input" value="<?php echo e($f['data_faturamento'] ?? ''); ?>">
                                                <button type="submit" class="btn-secondary">Salvar data</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="action-group" style="display: flex; gap: 10px; align-items: center;">
                                        <button type="button" onclick="toggleFormProduto(<?php echo (int)$num_folha; ?>)" class="btn-primary">+ Adicionar Produto</button>
                                        <a href="/contrato/imprimir/<?php echo (int)$contrato['id']; ?>?folha=<?php echo (int)$num_folha; ?>" target="_blank" class="btn-secondary">🖨️ Imprimir nota</a>

                                        <form action="/contrato/duplicar_folha/<?php echo (int)$contrato['id']; ?>/<?php echo (int)$num_folha; ?>" method="POST" style="margin: 0;" onsubmit="return confirm('Deseja realmente duplicar esta folha e todos os seus produtos?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                            <button type="submit" class="btn-secondary">📄 Duplicar nota</button>
                                        </form>

                                        <?php if (count($folhas) > 1): ?>
                                            <form action="/contrato/excluir_folha/<?php echo (int)$contrato['id']; ?>/<?php echo (int)$num_folha; ?>" method="POST" style="margin: 0;" onsubmit="return confirm('Tem certeza que deseja apagar a Folha <?php echo (int)$num_folha; ?> e todos os produtos cadastrados nela?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                <button type="submit" class="btn-danger">🗑️ Apagar nota</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div id="form-produto-<?php echo (int)$num_folha; ?>" class="inline-form-container" style="display:none;">
                                    <h4 class="contrato-summary-title money-primary">Novo item para a nota #<?php echo (int)$num_folha; ?></h4>
                                    <form action="/contrato/adicionar_produto_inline/<?php echo (int)$contrato['id']; ?>" method="POST" class="grid-form-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                        <input type="hidden" name="numero_folha" value="<?php echo (int)$num_folha; ?>">

                                        <div><label class="form-grid-label">Produto</label><input type="text" name="nome_produto" class="sistema" required style="margin:0;"></div>
                                        <div><label class="form-grid-label">Marca</label><input type="text" name="marca" class="sistema" style="margin:0;" placeholder="Ex: Chamex"></div>
                                        <div>
                                            <label class="form-grid-label">Unidade</label>
                                            <select name="unidade" class="sistema" style="margin:0;">
                                                <option value="KG">KG</option>
                                                <option value="L">L</option>
                                                <option value="UN" selected>UN</option>
                                                <option value="PCT">PCT</option>
                                            </select>
                                        </div>
                                        <div><label class="form-grid-label">Qtd</label><input type="number" name="quantidade" min="1" value="1" class="sistema" required style="margin:0;"></div>
                                        <div><label class="form-grid-label">Valor Unit.</label><input type="number" name="valor_unitario" step="0.01" class="sistema" required style="margin:0;"></div>
                                        <div class="inline-form-actions">
                                            <button type="submit" class="btn-primary">Salvar</button>
                                            <button type="button" onclick="toggleFormProduto(<?php echo (int)$num_folha; ?>)" class="btn-secondary btn-icon">X</button>
                                        </div>
                                    </form>
                                </div>

                                <table class="tabela-filtrada">
                                    <thead>
                                        <tr>
                                            <th>Descrição do Produto</th>
                                            <th>Marca</th>
                                            <th>Unid.</th>
                                            <th>Qtd</th>
                                            <th>Valor Unit.</th>
                                            <th>Subtotal</th>
                                            <th style="text-align: center;">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($produtos_desta_folha)): ?>
                                            <tr>
                                                <td colspan="7" class="muted-cell">Nenhum produto lancado nesta nota.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($produtos_desta_folha as $p): ?>
                                                <tr>
                                                    <td><strong><?php echo e($p['nome_produto']); ?></strong></td>
                                                    <td><?php echo e($p['marca'] ?: '-'); ?></td>
                                                    <td><span class="badge-unidade"><?php echo e($p['unidade'] ?? 'UN'); ?></span></td>
                                                    <td><?php echo (int)$p['quantidade']; ?></td>
                                                    <td>R$ <?php echo number_format($p['valor_unitario'], 2, ',', '.'); ?></td>
                                                    <td class="money-success">R$ <?php echo number_format($p['valor_total'], 2, ',', '.'); ?></td>
                                                    <td style="text-align: center; white-space: nowrap;">
                                                        <div class="table-actions">
                                                            <a href="/contrato/editar_produto/<?php echo (int)$p['id']; ?>" class="btn-warning btn-sm">✏️ Editar</a>
                                                            <form action="/contrato/excluir_produto/<?php echo (int)$p['id']; ?>" method="POST" onsubmit="return confirm('Excluir este item definitivamente?');">
                                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                                <button type="submit" class="btn-danger btn-sm">🗑️ Apagar</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                                <div class="contrato-observacao-card">
                                    <h4 class="contrato-summary-title">Observações da nota <?php echo (int)$num_folha; ?></h4>
                                    <form action="/contrato/salvar_observacao_folha/<?php echo (int)$contrato['id']; ?>/<?php echo (int)$num_folha; ?>" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                        <textarea name="observacao" class="sistema contrato-observacao-input" rows="3" placeholder="Escreva aqui informações adicionais desta nota..."><?php echo e($f['observacao'] ?? ''); ?></textarea>
                                        <div class="inline-form-actions contrato-observacao-actions">
                                            <button type="submit" class="btn-primary">Salvar observações</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        function abrirAba(event, numeroFolha) {
            document.querySelectorAll('.tab-content').forEach((el) => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach((el) => el.classList.remove('active'));
            document.getElementById('aba-' + numeroFolha).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        function toggleFormProduto(numeroFolha) {
            const form = document.getElementById('form-produto-' + numeroFolha);
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }
    </script>
</body>

</html>
