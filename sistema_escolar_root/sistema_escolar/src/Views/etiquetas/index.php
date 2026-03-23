<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modelos de Etiquetas - Gestor Escolar</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/etiquetas.css">
</head>

<body>
    <div class="layout-container">
        <?php
        $caminhoMenu = dirname(__DIR__) . '/partials/menu.php';
        if (!file_exists($caminhoMenu)) {
            $caminhoMenu = dirname(__DIR__) . '/menu.php';
        }

        if (file_exists($caminhoMenu)) {
            include $caminhoMenu;
        }
        ?>

        <div class="main-content-wrapper">
            <header>
                <h1>🏷️ Modelos de Etiquetas</h1>
            </header>

            <main>
                <div class="container-selecao">
                    <p style="color: #666; margin-bottom: 30px;">
                        Selecione um dos modelos abaixo para preencher os dados e gerar o arquivo de impressão.
                    </p>

                    <div class="grid-etiquetas">
                        <div class="card-etiqueta">
                            <div class="icon-wrapper">📁</div>
                            <h3>Pasta de Aluno</h3>
                            <p>Gera 2 fichas por folha A4 com dados do estudante, documentos e grade de histórico anual.</p>
                            <a href="/etiqueta/pasta" class="btn-abrir">Abrir Editor</a>
                        </div>

                        <div class="card-etiqueta">
                            <div class="icon-wrapper">📦</div>
                            <h3>Arquivo Passivo</h3>
                            <p>Etiqueta para identificação de caixas e livros. Importação automática via XML/TXT.</p>
                            <a href="/etiqueta/caixa" class="btn-abrir">Abrir Editor</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>