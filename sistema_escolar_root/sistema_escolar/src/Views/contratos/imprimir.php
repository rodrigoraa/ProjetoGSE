<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Imprimir Pedido #<?php echo (int)$contrato['id']; ?></title>
    <link rel="stylesheet" href="/assets/css/imprimir.css">
</head>

<body onload="window.print()">
    <div class="print-container">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir</button>

        <div class="header">
            <h1>Pedido: <?php echo htmlspecialchars($contrato['titulo']); ?></h1>
            <p>ID do Pedido: #<?php echo (int)$contrato['id']; ?> | Data: <?php echo date('d/m/Y', strtotime($contrato['criado_em'])); ?></p>
        </div>

        <?php
        $total_gasto = array_sum(array_column($produtos, 'valor_total'));
        $saldo = $contrato['valor_total'] - $total_gasto;
        ?>

        <div class="resumo">
            <div><strong>Valor total do pedido:</strong> R$ <?php echo number_format($contrato['valor_total'], 2, ',', '.'); ?></div>
            <div><strong>Total Acumulado:</strong> R$ <?php echo number_format($total_gasto, 2, ',', '.'); ?></div>
            <div style="color: <?php echo ($saldo < 0) ? '#dc3545' : '#28a745'; ?>;">
                <strong>Saldo Restante:</strong> R$ <?php echo number_format($saldo, 2, ',', '.'); ?>
            </div>
        </div>

        <?php foreach ($folhas as $f):
            $produtos_folha = array_filter($produtos, fn($p) => $p['numero_folha'] == $f['numero_folha']);
        ?>
            <div class="folha">
                <div class="folha-header">
                    <span>Nota <?php echo $f['numero_folha']; ?></span>
                    <span>Total da nota: R$ <?php echo number_format($f['valor_folha'], 2, ',', '.'); ?></span>
                </div>

                <?php if (count($produtos_folha) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Marca</th>
                                <th>Qtd</th>
                                <th>Unid.</th>
                                <th class="text-right">V. Unitário</th>
                                <th class="text-right">V. Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos_folha as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['nome_produto']); ?></td>
                                    <td><?php echo htmlspecialchars($p['marca']); ?></td>
                                    <td><?php echo $p['quantidade']; ?></td>
                                    <td><?php echo $p['unidade']; ?></td>
                                    <td class="text-right">R$ <?php echo number_format($p['valor_unitario'], 2, ',', '.'); ?></td>
                                    <td class="text-right"><strong>R$ <?php echo number_format($p['valor_total'], 2, ',', '.'); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="font-size: 14px; color: #777; font-style: italic;">Nenhum produto cadastrado nesta nota.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>