<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Passivo</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/passivo.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Novo Registro (Passivo)</h1>
            </header>

            <main>
                <?php if (!empty($mensagem))
                    echo $mensagem; ?>

                <div class="relatorio" style="max-width: 800px; margin: 0 auto;">
                    <form action="/passivo/cadastrar" method="POST" class="sistema">

                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                        <div
                            style="background: #e8f4ff; color: #004a91; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 0.9rem; border: 1px solid #b8daff;">
                            ℹ️ <strong>Dica:</strong> Use esta tela para adicionar um aluno avulso. Para muitos alunos,
                            use a Importação CSV.
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label>Nome Completo do Ex-Aluno:</label>
                            <input type="text" name="nome" value="<?php echo htmlspecialchars($d['nome'] ?? ''); ?>"
                                required placeholder="Nome completo sem abreviações" style="font-size: 1.1rem;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                            <div>
                                <label>Data de Nascimento:</label>
                                <input type="date" name="data_nascimento"
                                    value="<?php echo htmlspecialchars($d['data_nascimento'] ?? ''); ?>">
                            </div>

                            <div>
                                <label>Número (Pasta):</label>
                                <input type="text" name="numero"
                                    value="<?php echo htmlspecialchars($d['numero'] ?? ''); ?>"
                                    placeholder="Ex: 123/2015">
                            </div>

                            <div>
                                <label style="color: var(--primary-color);">Caixa (Localização):</label>
                                <input type="text" name="caixa"
                                    value="<?php echo htmlspecialchars($d['caixa'] ?? ''); ?>" required
                                    placeholder="Ex: CX-05" style="font-weight: bold;">
                            </div>
                        </div>

                        <div
                            style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; display: flex; align-items: center;">
                            <button type="submit" class="btn-primary">Salvar Registro</button>
                            <a href="/passivo" class="cancelar">Cancelar</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>