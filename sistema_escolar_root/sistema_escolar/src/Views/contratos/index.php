<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Pedidos</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css">
    <link rel="stylesheet" href="/assets/css/contrato.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Gerenciar Pedidos</h1>
            </header>

            <main>
                <div class="toolbar-alunos">
                    <a href="/contrato/cadastrar" class="btn-primary" style="text-decoration:none;">+ Novo Pedido</a>
                </div>

                <div class="relatorio">
                    <h3 class="form-section-title">Pedidos Cadastrados</h3>

                    <table class="tabela-filtrada">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título do Pedido / Fornecedor</th>
                                <th>Divisão</th>
                                <th>Valor Total</th>
                                <th>Data de Registro</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contratos)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px; color: #666;">
                                        <div style="font-size: 1.2rem; margin-bottom: 10px;">📂</div>
                                        Nenhum pedido encontrado no sistema.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contratos as $c): ?>
                                    <tr>
                                        <td><span style="color: #999;">#</span><?php echo $c['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($c['titulo']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge-folhas"><?php echo $c['qtd_folhas']; ?> folha(s)</span>
                                        </td>
                                        <td style="color: #0066cc; font-weight: bold;">
                                            R$ <?php echo number_format($c['valor_total'], 2, ',', '.'); ?>
                                        </td>
                                        <td style="font-size: 0.9rem; color: #666;">
                                            <?php echo date('d/m/Y', strtotime($c['criado_em'])); ?>
                                            <small style="display:block; color: #aaa;"><?php echo date('H:i', strtotime($c['criado_em'])); ?></small>
                                        </td>
                                        <td style="text-align: center;">
                                            <div style="display: flex; gap: 5px; justify-content: center; align-items: center;">
                                                <a href="/contrato/ver/<?php echo $c['id']; ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                                    👁️ Ver Produtos
                                                </a>

                                                <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                                                    <a href="/contrato/excluir/<?php echo $c['id']; ?>"
                                                        class="btn-danger"
                                                        style="padding: 6px 12px; font-size: 0.85rem; text-decoration: none; background-color: #d32f2f; color: white; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px;"
                                                        onclick="return confirm('⚠️ ATENÇÃO: Deseja realmente excluir este contrato? Isso apagará todas as folhas e produtos vinculados!')">
                                                        🗑️ Apagar
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>

</html>