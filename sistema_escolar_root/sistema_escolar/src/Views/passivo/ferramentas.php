<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Ferramentas de Caixa</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/passivo.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Ferramentas de Caixa</h1>
            </header>

            <main>
                <?php if (!empty($mensagem)) echo $mensagem; ?>

                <div class="tools-grid">
                    <div class="tool-card blue">
                        <h2>1. Enumerar</h2>
                        <p style="margin-bottom: 20px; color: #666;">Gera numeros sequenciais automaticamente para alunos recem-importados nesta caixa, respeitando a ordem alfabetica.</p>

                        <form method="POST" class="sistema">
                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                            <input type="hidden" name="acao" value="enumerar">
                            <label>Qual caixa processar?</label>

                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="caixa" required placeholder="Ex: CX-10" style="margin-bottom: 0; flex:1;">
                                <button type="submit" class="btn-primary" onclick="return confirm('Isso vai alterar os numeros da caixa. Confirmar?')">Gerar</button>
                            </div>
                        </form>
                    </div>

                    <div class="tool-card green">
                        <h2>2. Baixar Lista (TXT)</h2>
                        <p style="margin-bottom: 20px; color: #666;">Gera um arquivo de texto simples com a lista "Numero - Nome" pronta para imprimir.</p>

                        <form method="POST" class="sistema">
                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                            <input type="hidden" name="acao" value="baixar_txt">
                            <label>Qual caixa baixar?</label>

                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="caixa" required placeholder="Ex: CX-10" style="margin-bottom: 0; flex:1;">
                                <button type="submit" class="btn-primary" style="background-color: var(--success-color);">Download</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <a href="/passivo" class="cancelar" style="margin-left: 0;">&larr; Voltar para a Busca</a>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
