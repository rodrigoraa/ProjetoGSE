<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Importar CSV</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/passivo.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/passivo.css'); ?>">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Importar CSV</h1>
            </header>

            <main>
                <?php if (!empty($mensagem)) echo $mensagem; ?>

                <section class="page-intro">
                    <h2>Substituição completa do arquivo passivo</h2>
                    <p>Esta rotina foi pensada para importações grandes. Antes de continuar, confira o formato do CSV e confirme que você realmente deseja sobrescrever os registros atuais.</p>
                </section>

                <div class="relatorio form-card">
                    <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">Instruções de Importação</h3>

                    <ul class="lista-instrucoes">
                        <li>O arquivo deve estar no formato <strong>.CSV</strong>.</li>
                        <li>O separador deve ser <strong>ponto e vírgula (;)</strong>.</li>
                        <li>As colunas obrigatórias, nesta ordem, são: <strong>Nome; Data; Número; Caixa</strong>.</li>
                        <li>A primeira linha, usada como cabeçalho, será ignorada.</li>
                    </ul>

                    <div class="alert-box alert-danger form-note">
                        <strong>Atenção crítica:</strong> esta ação apagará todo o arquivo passivo atual e substituirá os dados pelos registros do novo CSV. Faça um backup antes.
                    </div>

                    <form action="/passivo/importar" method="POST" enctype="multipart/form-data" class="sistema">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                        <div style="margin-bottom: 20px;">
                            <label>Selecione o arquivo CSV:</label>
                            <input type="file" name="arquivo_csv" accept=".csv" required style="background: #f8f9fa; padding: 10px;">
                        </div>

                        <div class="form-row-actions">
                            <button type="submit" class="btn-primary" onclick="return confirm('Tem certeza absoluta? Isso vai substituir todos os dados atuais.')">Iniciar Importação</button>
                            <a href="/passivo" class="cancelar">Cancelar</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
