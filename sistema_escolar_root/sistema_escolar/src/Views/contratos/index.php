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
                    <div class="toolbar-actions toolbar-actions--end">
                        <a href="/contrato/cadastrar" class="btn-primary">+ Novo Pedido</a>
                    </div>
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
                                    <td colspan="6" class="muted-cell">Nenhum pedido encontrado no sistema.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contratos as $c): ?>
                                    <tr>
                                        <td><span class="table-id">#</span><?php echo (int)$c['id']; ?></td>
                                        <td><strong><?php echo e($c['titulo']); ?></strong></td>
                                        <td><span class="badge-folhas"><?php echo (int)$c['qtd_folhas']; ?> folha(s)</span></td>
                                        <td class="money-primary">R$ <?php echo number_format($c['valor_total'], 2, ',', '.'); ?></td>
                                        <td class="table-date">
                                            <?php echo date('d/m/Y', strtotime($c['criado_em'])); ?>
                                            <small><?php echo date('H:i', strtotime($c['criado_em'])); ?></small>
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="table-actions">
                                                <a href="/contrato/ver/<?php echo (int)$c['id']; ?>" class="btn-secondary btn-sm">👁️ Ver detalhes</a>

                                                <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                                                    <form action="/contrato/excluir/<?php echo (int)$c['id']; ?>" method="POST" onsubmit="return confirm('Deseja realmente excluir este contrato? Isso apagará todas as folhas e produtos vinculados.');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                        <button type="submit" class="btn-danger btn-sm">🗑️ Apagar</button>
                                                    </form>
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
