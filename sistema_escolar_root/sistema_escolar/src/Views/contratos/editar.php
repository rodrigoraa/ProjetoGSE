<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Pedido</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css">
    <link rel="stylesheet" href="/assets/css/contrato.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Editar Pedido #<?php echo $contrato['id']; ?></h1>
            </header>

            <main>
                <div class="toolbar-alunos">
                    <a href="/contrato/ver/<?php echo $contrato['id']; ?>" class="btn-secondary" style="text-decoration:none;">⬅ Voltar</a>
                </div>

                <form action="/contrato/editar/<?php echo $contrato['id']; ?>" method="POST" class="form-aluno">
                    <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                    <h3 class="form-section-title">📝 Dados Principais</h3>

                    <div class="form-group">
                        <label>Título do Pedido / Fornecedor</label>
                        <input type="text" name="titulo" class="sistema" value="<?php echo htmlspecialchars($contrato['titulo']); ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Valor Total do Pedido (R$)</label>
                            <input type="number" name="valor_total" id="valor_total" class="sistema" step="0.01" value="<?php echo $contrato['valor_total']; ?>" required oninput="calcularTotais()">
                        </div>

                        <div class="form-group">
                            <label>Quantidade de Folhas</label>
                            <input type="number" name="qtd_folhas" id="qtd_folhas" class="sistema" min="1" value="<?php echo $contrato['qtd_folhas']; ?>" required oninput="calcularTotais()">
                        </div>
                    </div>

                    <h3 class="form-section-title" style="margin-top: 30px;">📦 Gerenciar Produtos</h3>

                    <div style="display: grid; grid-template-columns: 2fr 1.2fr 1fr 1fr 1fr auto; gap: 10px; padding: 0 10px; margin-bottom: 5px; color: #666; font-size: 0.85rem; font-weight: bold;">
                        <span>Produto</span>
                        <span>Marca</span>
                        <span>Unidade</span> <span>Qtd</span>
                        <span>Unitário</span>
                        <span></span>
                    </div>

                    <div id="produtos-container">
                        <?php foreach ($produtos as $p): ?>
                            <div class="produto-linha">
                                <input type="text" name="produto_nome[]" class="sistema" value="<?php echo htmlspecialchars($p['nome_produto']); ?>" required placeholder="Produto" style="margin:0;">

                                <input type="text" name="produto_marca[]" class="sistema" value="<?php echo htmlspecialchars($p['marca'] ?? ''); ?>" placeholder="Marca" style="margin:0;">

                                <select name="produto_unidade[]" class="sistema" style="margin:0;">
                                    <option value="KG" <?php echo ($p['unidade'] == 'KG') ? 'selected' : ''; ?>>KG</option>
                                    <option value="L" <?php echo ($p['unidade'] == 'L') ? 'selected' : ''; ?>>L</option>
                                    <option value="UN" <?php echo ($p['unidade'] == 'UN') ? 'selected' : ''; ?>>UN</option>
                                    <option value="PCT" <?php echo ($p['unidade'] == 'PCT') ? 'selected' : ''; ?>>PCT</option>
                                </select>

                                <input type="number" name="produto_qtd[]" class="sistema qtd-input" min="1" value="<?php echo $p['quantidade']; ?>" required oninput="calcularTotais()" style="margin:0;">
                                <input type="number" name="produto_valor[]" class="sistema valor-input" step="0.01" value="<?php echo $p['valor_unitario']; ?>" required oninput="calcularTotais()" style="margin:0;">
                                <button type="button" class="btn-remover" onclick="removerLinha(this)">X</button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="btn-secondary" onclick="adicionarProduto()" style="margin-bottom: 20px; margin-top: 10px;">+ Adicionar Novo Item</button>

                    <div class="resumo-contrato">
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                <p><strong>Orçamento do Pedido:</strong> R$ <span id="lbl_total_contrato">0.00</span></p>
                                <p style="font-size: 0.9rem; color: #555;">Valor por Folha: R$ <span id="lbl_valor_folha">0.00</span></p>
                            </div>
                            <div style="text-align: right;">
                                <p><strong>Soma dos Produtos:</strong> R$ <span id="lbl_total_produtos">0.00</span></p>
                                <p><strong>Saldo Restante:</strong> R$ <span id="lbl_saldo" style="font-size: 1.2rem; font-weight: bold;">0.00</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 30px;">
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem;">💾 Salvar Todas as Alterações</button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script>
        window.onload = calcularTotais;

        function adicionarProduto() {
            const container = document.getElementById('produtos-container');
            const novaLinha = document.createElement('div');
            novaLinha.className = 'produto-linha';
            novaLinha.innerHTML = `
                <input type="text" name="produto_nome[]" class="sistema" placeholder="Nome do Produto" required style="margin:0;">
                <input type="text" name="produto_marca[]" class="sistema" placeholder="Marca/Fabricante" style="margin:0;">
                <select name="produto_unidade[]" class="sistema" style="margin:0;">
                    <option value="KG">KG</option>
                    <option value="L">L</option>
                    <option value="UN" selected>UN</option>
                    <option value="PCT">PCT</option>
                </select>
                <input type="number" name="produto_qtd[]" class="sistema qtd-input" placeholder="Qtd" value="1" min="1" required oninput="calcularTotais()" style="margin:0;">
                <input type="number" name="produto_valor[]" class="sistema valor-input" placeholder="Valor Unit (R$)" step="0.01" required oninput="calcularTotais()" style="margin:0;">
                <button type="button" class="btn-remover" onclick="removerLinha(this)">X</button>
            `;
            container.appendChild(novaLinha);
            calcularTotais();
        }

        function removerLinha(botao) {
            if (confirm('Deseja remover este item da lista?')) {
                const linha = botao.parentElement;
                linha.remove();
                calcularTotais();
            }
        }

        function calcularTotais() {
            let totalContrato = parseFloat(document.getElementById('valor_total').value) || 0;
            let qtdFolhas = parseInt(document.getElementById('qtd_folhas').value) || 1;
            if (qtdFolhas < 1) qtdFolhas = 1;

            let totalProdutos = 0;
            const linhas = document.querySelectorAll('.produto-linha');

            linhas.forEach(linha => {
                const qtd = parseFloat(linha.querySelector('.qtd-input').value) || 0;
                const valor = parseFloat(linha.querySelector('.valor-input').value) || 0;
                totalProdutos += (qtd * valor);
            });

            let valorPorFolha = totalContrato / qtdFolhas;
            let saldo = totalContrato - totalProdutos;

            const formatar = (v) => v.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            document.getElementById('lbl_total_contrato').innerText = formatar(totalContrato);
            document.getElementById('lbl_valor_folha').innerText = formatar(valorPorFolha);
            document.getElementById('lbl_total_produtos').innerText = formatar(totalProdutos);

            let lblSaldo = document.getElementById('lbl_saldo');
            lblSaldo.innerText = formatar(saldo);

            if (saldo < 0) {
                lblSaldo.style.color = '#ff4c4c';
            } else if (saldo === 0) {
                lblSaldo.style.color = '#28a745';
            } else {
                lblSaldo.style.color = '#0066cc';
            }
        }
    </script>
</body>

</html>