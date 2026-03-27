<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Passivo</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/passivo.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>
        <div class="main-content-wrapper">
            <header>
                <h1>Editar Registro Passivo</h1>
            </header>
            <main>
                <?php if (!empty($mensagem)) echo $mensagem; ?>

                <section class="page-intro">
                    <h2>Atualize os dados do registro com segurança</h2>
                    <p>Edite apenas o que for necessário e, se mudar o nome ou a caixa, revise depois a numeração automática para manter o acervo consistente.</p>
                </section>

                <div class="relatorio form-card">
                    <form method="POST" class="sistema">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                        <div class="alert-box alert-warning form-note">
                            <strong>Atenção:</strong> Se você alterar o nome, a numeração automática da caixa poderá ser afetada na próxima vez que rodar a ferramenta "Enumerar".
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label>Nome Completo:</label>
                            <input type="text" name="nome" value="<?php echo htmlspecialchars($reg['nome_completo']); ?>" required style="font-size: 1.1rem;">
                        </div>

                        <div class="form-grid-3">
                            <div>
                                <label>Data Nasc:</label>
                                <input type="date" name="data_nascimento" value="<?php echo e($reg['data_nascimento']); ?>">
                            </div>
                            <div>
                                <label>Número:</label>
                                <input type="text" name="numero" value="<?php echo htmlspecialchars($reg['numero']); ?>">
                            </div>
                            <div class="field-strong">
                                <label class="field-highlight">Caixa:</label>
                                <input type="text" name="caixa" value="<?php echo htmlspecialchars($reg['caixa']); ?>" required>
                            </div>
                        </div>

                        <div class="form-row-actions">
                            <button type="submit" class="btn-primary">Salvar Alterações</button>
                            <a href="/passivo" class="cancelar">Cancelar</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
