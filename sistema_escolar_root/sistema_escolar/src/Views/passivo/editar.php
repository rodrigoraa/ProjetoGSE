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
                <h1>✏️ Editar Registro Passivo</h1>
            </header>
            <main>
                <?php if (!empty($mensagem))
                    echo $mensagem; ?>

                <div class="relatorio" style="max-width: 800px; margin: 0 auto;">
                    <form method="POST" class="sistema">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                        <div
                            style="background-color: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #ffeeba; color: #856404; font-size: 0.9rem;">
                            ⚠️ <strong>Atenção:</strong> Se você alterar o nome, a numeração automática da caixa poderá
                            ser afetada na próxima vez que rodar a ferramenta "Enumerar".
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label>Nome Completo:</label>
                            <input type="text" name="nome"
                                value="<?php echo htmlspecialchars($reg['nome_completo']); ?>" required
                                style="font-size: 1.1rem;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                            <div>
                                <label>Data Nasc:</label>
                                <input type="date" name="data_nascimento"
                                    value="<?php echo $reg['data_nascimento']; ?>">
                            </div>
                            <div>
                                <label>Número:</label>
                                <input type="text" name="numero"
                                    value="<?php echo htmlspecialchars($reg['numero']); ?>">
                            </div>
                            <div>
                                <label style="color: var(--primary-color);">Caixa:</label>
                                <input type="text" name="caixa" value="<?php echo htmlspecialchars($reg['caixa']); ?>"
                                    required style="font-weight: bold;">
                            </div>
                        </div>

                        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
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
