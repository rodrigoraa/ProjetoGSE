<?php
$totalContratos = count($contratos);
$valorAcumulado = 0.0;
$totalFolhas = 0;

foreach ($contratos as $contratoResumo) {
    $valorAcumulado += (float)($contratoResumo['valor_total'] ?? 0);
    $totalFolhas += (int)($contratoResumo['qtd_folhas'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Pedidos</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
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
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <section class="contrato-hero">
                    <div>
                        <h2>Visão geral dos pedidos cadastrados</h2>
                        <p>Use esta tela para acompanhar rapidamente os pedidos, a quantidade de notas e o valor total já registrado.</p>
                    </div>
                    <div class="contrato-hero-stats">
                        <div class="contrato-stat">
                            <strong><?php echo (int)$totalContratos; ?></strong>
                            <span>Pedidos cadastrados</span>
                        </div>
                        <div class="contrato-stat">
                            <strong><?php echo (int)$totalFolhas; ?></strong>
                            <span>Notas geradas</span>
                        </div>
                        <div class="contrato-stat">
                            <strong>R$ <?php echo number_format($valorAcumulado, 2, ',', '.'); ?></strong>
                            <span>Valor total</span>
                        </div>
                    </div>
                </section>

                <div class="contrato-toolbar">
                    <div class="contrato-toolbar-copy">
                        <h3>Lista principal</h3>
                        <p>Abra os detalhes para acompanhar notas, produtos e saldo de cada pedido.</p>
                    </div>

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
                                <th>Título do Pedido</th>
                                <th>Notas</th>
                                <th>Valor Total</th>
                                <th>Média por Nota</th>
                                <th>Data de Registro</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contratos)): ?>
                                <tr>
                                    <td colspan="7" class="muted-cell">Nenhum pedido encontrado no sistema.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contratos as $c): ?>
                                    <?php
                                    $qtdFolhas = max(1, (int)$c['qtd_folhas']);
                                    $mediaPorFolha = ((float)$c['valor_total']) / $qtdFolhas;
                                    ?>
                                    <tr>
                                        <td><span class="table-id">#</span><?php echo (int)$c['id']; ?></td>
                                        <td>
                                            <strong><?php echo e($c['titulo']); ?></strong>
                                            <small class="contrato-meta">Pedido criado para controle financeiro e distribuição em notas.</small>
                                        </td>
                                        <td>
                                            <span class="badge-folhas"><?php echo $qtdFolhas; ?> nota(s)</span>
                                        </td>
                                        <td class="money-primary">R$ <?php echo number_format($c['valor_total'], 2, ',', '.'); ?></td>
                                        <td><span class="badge-media">R$ <?php echo number_format($mediaPorFolha, 2, ',', '.'); ?></span></td>
                                        <td class="table-date">
                                            <?php echo date('d/m/Y', strtotime($c['criado_em'])); ?>
                                            <small><?php echo date('H:i', strtotime($c['criado_em'])); ?></small>
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="table-actions">
                                                <a href="/contrato/ver/<?php echo (int)$c['id']; ?>" class="btn-secondary btn-sm">Ver detalhes</a>

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
