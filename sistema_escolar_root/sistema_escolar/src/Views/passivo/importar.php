<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Importar CSV</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/passivo.css">
</head>

<body>

    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>📂 Importar CSV</h1>
            </header>

            <main>
                <?php if (!empty($mensagem))
                    echo $mensagem; ?>

                <div class="relatorio" style="max-width: 600px; margin: 0 auto;">
                    <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">Instruções de
                        Importação</h3>

                    <ul style="line-height: 1.8; color: #555; margin-bottom: 20px;">
                        <li>📄 O arquivo deve ser formato <strong>.CSV</strong>.</li>
                        <li>🔠 Separador deve ser <strong>ponto e vírgula (;)</strong>.</li>
                        <li>📊 Colunas obrigatórias na ordem: <strong>Nome; Data; Número; Caixa</strong>.</li>
                        <li>🚫 A primeira linha (cabeçalho) será ignorada.</li>
                    </ul>

                    <div
                        style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; border: 1px solid #f5c6cb; margin-bottom: 25px; font-size: 0.9rem;">
                        ⚠️ <strong>Atenção Crítica:</strong> Esta ação apagará TODO o arquivo passivo atual e
                        substituirá pelos dados do CSV novo. Faça um backup antes!
                    </div>

                    <form action="/passivo/importar" method="POST" enctype="multipart/form-data" class="sistema">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                        <div style="margin-bottom: 20px;">
                            <label>Selecione o arquivo CSV:</label>
                            <input type="file" name="arquivo_csv" accept=".csv" required
                                style="background: #f8f9fa; padding: 10px;">
                        </div>

                        <div
                            style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
                            <button type="submit" class="btn-primary"
                                onclick="return confirm('Tem certeza absoluta? Isso vai substituir todos os dados atuais.')">Iniciar
                                Importação</button>
                            <a href="/passivo" class="cancelar">Cancelar</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>