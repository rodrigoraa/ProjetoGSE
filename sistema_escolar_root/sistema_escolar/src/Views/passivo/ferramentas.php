<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Ferramentas de Caixa</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/passivo.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/passivo.css'); ?>">
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

                <section class="page-intro">
                    <h2>Operações rápidas para manutenção das caixas</h2>
                    <p>Use estas ferramentas para organizar numeração de registros recém-importados ou gerar uma listagem simples para impressão e conferência física.</p>
                </section>

                <div class="tools-grid">
                    <div class="tool-card blue">
                        <h2>1. Enumerar</h2>
                        <p class="tool-copy">Gera números sequenciais automaticamente para alunos recém-importados nesta caixa, respeitando a ordem alfabética.</p>

                        <form method="POST" class="sistema">
                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                            <input type="hidden" name="acao" value="enumerar">
                            <label>Qual caixa processar?</label>

                            <div class="tool-form-row">
                                <input type="text" name="caixa" required placeholder="Ex: CX-10">
                                <button type="submit" class="btn-primary" onclick="return confirm('Isso vai alterar os números da caixa. Confirmar?')">Gerar</button>
                            </div>
                        </form>
                    </div>

                    <div class="tool-card green">
                        <h2>2. Baixar Lista (TXT)</h2>
                        <p class="tool-copy">Gera um arquivo de texto simples com a lista "Número - Nome" pronta para imprimir.</p>

                        <form method="POST" class="sistema">
                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                            <input type="hidden" name="acao" value="baixar_txt">
                            <label>Qual caixa baixar?</label>

                            <div class="tool-form-row">
                                <input type="text" name="caixa" required placeholder="Ex: CX-10">
                                <button type="submit" class="btn-primary" style="background-color: var(--success-color);">Download</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="page-footer-back">
                    <a href="/passivo" class="cancelar" style="margin-left: 0;">&larr; Voltar para a Busca</a>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
