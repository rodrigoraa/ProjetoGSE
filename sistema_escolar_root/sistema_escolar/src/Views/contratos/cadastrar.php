<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Novo Pedido</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css">
    <link rel="stylesheet" href="/assets/css/contrato.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Cadastrar Novo Pedido</h1>
            </header>

            <main>
                <form action="/contrato/cadastrar" method="POST" class="formulario-sistema">
                    <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                    <div class="form-group">
                        <label>Título do Pedido / Fornecedor</label>
                        <input type="text" name="titulo" class="sistema" required placeholder="Ex: Contrato de Papelaria 2026">
                    </div>

                    <div class="form-group">
                        <label>Valor Total do Pedido (R$)</label>
                        <input type="number" name="valor_total" id="valor_total" class="sistema" step="0.01" required oninput="calcularTotais()" placeholder="Ex: 50000.00">
                    </div>

                    <div class="form-group">
                        <label>Em quantas folhas este pedido será dividido?</label>
                        <input type="number" name="qtd_folhas" id="qtd_folhas" class="sistema" value="1" min="1" required oninput="calcularTotais()">
                    </div>

                    <h3 class="form-section-title" style="margin-top: 30px;">Produtos do Pedido</h3>

                    <div id="lista-produtos">
                        <div class="produto-linha">
                            <input type="text" name="produto_nome[]" class="sistema" placeholder="Nome do Produto" required style="margin:0; flex: 2;">
                            <input type="text" name="produto_marca[]" class="sistema" placeholder="Marca" style="margin:0; flex: 1;">

                            <select name="produto_unidade[]" class="sistema" style="margin:0; flex: 0.8;">
                                <option value="KG">Quilos (KG)</option>
                                <option value="L">Litros (L)</option>
                                <option value="UN">Unidade (UN)</option>
                            </select>

                            <input type="number" name="produto_qtd[]" class="sistema qtd-input" placeholder="Qtd" value="1" min="1" required oninput="calcularTotais()" style="margin:0; flex: 0.5;">
                            <input type="number" name="produto_valor[]" class="sistema valor-input" placeholder="Valor Unit (R$)" step="0.01" required oninput="calcularTotais()" style="margin:0; flex: 1;">
                            <button type="button" class="btn-remover" onclick="removerLinha(this)">X</button>
                        </div>
                    </div>

                    <button type="button" class="btn-secondary" onclick="adicionarProduto()" style="margin-bottom: 20px; margin-top: 10px;">+ Adicionar Outro Produto</button>
                    <div class="resumo-contrato">
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                <p><strong>Orçamento do Pedido:</strong> R$ <span id="lbl_total_contrato">0.00</span></p>
                                <p style="font-size: 0.9rem; color: #555;">Valor por Folha: R$ <span id="lbl_valor_folha">0.00</span></p>
                            </div>
                            <div style="text-align: right;">
                                <p><strong>Soma dos Produtos:</strong> R$ <span id="lbl_total_produtos">0.00</span></p>
                                <p><strong>Saldo (Orçamento - Produtos):</strong> R$ <span id="lbl_saldo" style="font-size: 1.2rem; font-weight: bold;">0.00</span></p>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn-primary">💾 Salvar Pedido</button>
                        <a href="/contrato" class="btn-secondary" style="text-decoration:none;">Cancelar</a>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script>
        function adicionarProduto() {
            const container = document.getElementById('lista-produtos');
            const novaLinha = document.createElement('div');
            novaLinha.className = 'produto-linha';

            novaLinha.innerHTML = `
                <input type="text" name="produto_nome[]" class="sistema" placeholder="Nome do Produto" required style="margin:0; flex: 2;">
                <input type="text" name="produto_marca[]" class="sistema" placeholder="Marca" style="margin:0; flex: 1;">
                <select name="produto_unidade[]" class="sistema" style="margin:0; flex: 0.8;">
                    <option value="KG">Quilos (KG)</option>
                    <option value="L">Litros (L)</option>
                    <option value="UN">Unidade (UN)</option>
                </select>
                <input type="number" name="produto_qtd[]" class="sistema qtd-input" placeholder="Qtd" value="1" min="1" required oninput="calcularTotais()" style="margin:0; flex: 0.5;">
                <input type="number" name="produto_valor[]" class="sistema valor-input" placeholder="Valor Unit (R$)" step="0.01" required oninput="calcularTotais()" style="margin:0; flex: 1;">
                <button type="button" class="btn-remover" onclick="removerLinha(this)">X</button>
            `;
            container.appendChild(novaLinha);
            calcularTotais();
        }

        function removerLinha(botao) {
            const linha = botao.parentElement;
            linha.remove();
            calcularTotais();
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

            document.getElementById('lbl_total_contrato').innerText = totalContrato.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('lbl_valor_folha').innerText = valorPorFolha.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('lbl_total_produtos').innerText = totalProdutos.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            let lblSaldo = document.getElementById('lbl_saldo');
            lblSaldo.innerText = saldo.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            if (saldo < 0) {
                lblSaldo.style.color = '#ff4c4c';
            } else {
                lblSaldo.style.color = '#28a745';
            }
        }
    </script>
</body>

</html>