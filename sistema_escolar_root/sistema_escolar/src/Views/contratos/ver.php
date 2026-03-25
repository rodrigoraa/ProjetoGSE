<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Detalhes do Pedido #<?php echo (int)$contrato['id']; ?></title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css">
    <link rel="stylesheet" href="/assets/css/contrato.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Detalhes do Pedido</h1>
                <div style="color: var(--text-muted);">ID: #<?php echo (int)$contrato['id']; ?> | Registrado em: <?php echo date('d/m/Y', strtotime($contrato['criado_em'])); ?></div>
            </header>

            <main>
                <div class="toolbar-alunos">
                    <a href="/contrato" class="btn-secondary" style="text-decoration:none;">Voltar</a>
                    <a href="/contrato/editar/<?php echo (int)$contrato['id']; ?>" class="btn-warning" style="text-decoration:none; background-color: #ffc107; color: #000; padding: 10px 15px; border-radius: 5px; font-weight: bold;">Editar Geral</a>
                </div>

                <?php
                $total_produtos_geral = array_sum(array_column($produtos, 'valor_total'));
                $saldo_geral = $contrato['valor_total'] - $total_produtos_geral;
                ?>

                <div class="contrato-header">
                    <h2 style="margin-bottom: 5px;"><?php echo e($contrato['titulo']); ?></h2>
                    <div class="resumo-financeiro">
                        <div>
                            <p class="resumo-label">Valor total do Pedido</p>
                            <p class="resumo-valor" style="color: #0066cc;">R$ <?php echo number_format($contrato['valor_total'], 2, ',', '.'); ?></p>
                        </div>
                        <div>
                            <p class="resumo-label">Valor total dos itens</p>
                            <p class="resumo-valor" style="color: #333;">R$ <?php echo number_format($total_produtos_geral, 2, ',', '.'); ?></p>
                        </div>
                        <div style="text-align: right;">
                            <p class="resumo-label">Saldo Final</p>
                            <p class="resumo-valor" style="font-size: 1.5rem; color: <?php echo ($saldo_geral < 0) ? '#ff4c4c' : '#28a745'; ?>;">R$ <?php echo number_format($saldo_geral, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="tabs-container">
                    <div class="tabs-header" style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ($folhas as $f): ?>
                            <button class="tab-btn <?php echo ($f['numero_folha'] == ($aba_ativa ?? 1)) ? 'active' : ''; ?>" type="button" onclick="abrirAba(event, <?php echo (int)$f['numero_folha']; ?>)">
                                Folha <?php echo (int)$f['numero_folha']; ?>
                            </button>
                        <?php endforeach; ?>

                        <form action="/contrato/adicionar_folha/<?php echo (int)$contrato['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Deseja adicionar uma nova folha a este pedido?');">
                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                            <button type="submit" class="tab-btn">Nova Folha</button>
                        </form>
                    </div>

                    <?php foreach ($folhas as $f): ?>
                        <?php
                        $num_folha = $f['numero_folha'];
                        $produtos_desta_folha = array_filter($produtos, function ($p) use ($num_folha) {
                            return ($p['numero_folha'] ?: 1) == $num_folha;
                        });
                        $total_gasto_folha = array_sum(array_column($produtos_desta_folha, 'valor_total'));
                        $saldo_folha = $f['valor_folha'] - $total_gasto_folha;
                        ?>
                        <div id="aba-<?php echo (int)$num_folha; ?>" class="tab-content <?php echo ($num_folha == ($aba_ativa ?? 1)) ? 'active' : ''; ?>">
                            <div class="relatorio">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <div>
                                        <h3 class="form-section-title" style="margin: 0;">Itens da Folha <?php echo (int)$num_folha; ?></h3>
                                        <p style="margin: 5px 0 0 0; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
                                            <span>
                                                Valor da folha: <strong>R$ <?php echo number_format($f['valor_folha'], 2, ',', '.'); ?></strong>
                                                <button type="button" class="btn-warning" onclick="document.getElementById('edit-valor-<?php echo (int)$num_folha; ?>').style.display='inline-flex'" style="padding: 4px 8px; font-size: 0.8rem; background: #ffc107; color: #000; text-decoration: none; border-radius: 4px;">Editar</button>
                                            </span>

                                            <form id="edit-valor-<?php echo (int)$num_folha; ?>" action="/contrato/editar_valor_folha/<?php echo (int)$contrato['id']; ?>" method="POST" style="display: none; align-items: center; gap: 5px; margin:0;">
                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                <input type="hidden" name="numero_folha" value="<?php echo (int)$num_folha; ?>">
                                                <input type="number" name="novo_valor" step="0.01" class="sistema" style="width: 120px; margin: 0; padding: 4px;" value="<?php echo e($f['valor_folha']); ?>" required>
                                                <button type="submit" class="btn-primary" style="padding: 4px 8px; font-size: 0.8rem;">Salvar</button>
                                                <button type="button" class="btn-secondary" style="padding: 4px 8px; font-size: 0.8rem;" onclick="document.getElementById('edit-valor-<?php echo (int)$num_folha; ?>').style.display='none'">Cancelar</button>
                                            </form>

                                            | Saldo: <strong style="color: <?php echo ($saldo_folha < 0) ? '#ff4c4c' : '#28a745'; ?>">R$ <?php echo number_format($saldo_folha, 2, ',', '.'); ?></strong>
                                        </p>
                                    </div>
                                    <div style="display: flex; gap: 10px;">
                                        <button type="button" onclick="toggleFormProduto(<?php echo (int)$num_folha; ?>)" class="btn-primary" style="padding: 8px 15px;">+ Adicionar Produto</button>

                                        <?php if (count($folhas) > 1): ?>
                                            <form action="/contrato/excluir_folha/<?php echo (int)$contrato['id']; ?>/<?php echo (int)$num_folha; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja apagar a Folha <?php echo (int)$num_folha; ?> e todos os produtos cadastrados nela?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                <button type="submit" class="btn-danger" style="padding: 8px 15px; border:0; cursor:pointer;">Apagar Folha</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div id="form-produto-<?php echo (int)$num_folha; ?>" class="inline-form-container" style="display:none;">
                                    <h4 style="margin-top:0; color:#0066cc; margin-bottom: 15px;">Novo item para a Folha #<?php echo (int)$num_folha; ?></h4>
                                    <form action="/contrato/adicionar_produto_inline/<?php echo (int)$contrato['id']; ?>" method="POST" class="grid-form-inline" style="display: grid; grid-template-columns: 2fr 1fr 1fr 0.8fr 1fr auto; gap: 10px; align-items: end;">
                                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                        <input type="hidden" name="numero_folha" value="<?php echo (int)$num_folha; ?>">

                                        <div><label style="font-size:0.8rem; display:block;">Produto</label><input type="text" name="nome_produto" class="sistema" required style="margin:0;"></div>
                                        <div><label style="font-size:0.8rem; display:block;">Marca</label><input type="text" name="marca" class="sistema" style="margin:0;" placeholder="Ex: Chamex"></div>
                                        <div>
                                            <label style="font-size:0.8rem; display:block;">Unidade</label>
                                            <select name="unidade" class="sistema" style="margin:0;">
                                                <option value="KG">KG</option>
                                                <option value="L">L</option>
                                                <option value="UN" selected>UN</option>
                                                <option value="PCT">PCT</option>
                                            </select>
                                        </div>
                                        <div><label style="font-size:0.8rem; display:block;">Qtd</label><input type="number" name="quantidade" min="1" value="1" class="sistema" required style="margin:0;"></div>
                                        <div><label style="font-size:0.8rem; display:block;">Valor Unit.</label><input type="number" name="valor_unitario" step="0.01" class="sistema" required style="margin:0;"></div>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="submit" class="btn-primary" style="height: 38px;">Salvar</button>
                                            <button type="button" onclick="toggleFormProduto(<?php echo (int)$num_folha; ?>)" class="btn-secondary" style="height: 38px;">X</button>
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
                                                <td colspan="7" style="text-align: center; padding: 20px; color: #999;">Nenhum produto lancado nesta folha.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($produtos_desta_folha as $p): ?>
                                                <tr>
                                                    <td><strong><?php echo e($p['nome_produto']); ?></strong></td>
                                                    <td><?php echo e($p['marca'] ?: '-'); ?></td>
                                                    <td><span class="badge-unidade" style="background: #f0f0f0; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; border: 1px solid #ddd;"><?php echo e($p['unidade'] ?? 'UN'); ?></span></td>
                                                    <td><?php echo (int)$p['quantidade']; ?></td>
                                                    <td>R$ <?php echo number_format($p['valor_unitario'], 2, ',', '.'); ?></td>
                                                    <td style="color: #28a745; font-weight: bold;">R$ <?php echo number_format($p['valor_total'], 2, ',', '.'); ?></td>
                                                    <td style="text-align: center; white-space: nowrap;">
                                                        <a href="/contrato/editar_produto/<?php echo (int)$p['id']; ?>" class="btn-warning" style="padding: 4px 8px; font-size: 0.8rem; background: #ffc107; color: #000; text-decoration: none; border-radius: 4px;">Editar</a>
                                                        <form action="/contrato/excluir_produto/<?php echo (int)$p['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Excluir este item definitivamente?');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                            <button type="submit" class="btn-danger" style="padding: 4px 8px; font-size: 0.8rem; border:0; cursor:pointer;">Apagar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
