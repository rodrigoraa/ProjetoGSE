<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Arquivo Passivo: Caixa e Livro - E.E São José</title>
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
        include $caminhoMenu;
        ?>

        <div class="main-content-wrapper">
            <div class="no-print-bar" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px; background: #333;">
                <button onclick="window.print()" class="btn-imprimir">🖨️ IMPRIMIR TUDO</button>
                <button onclick="document.getElementById('inputArquivo').click()" class="btn-limpar" style="background: #007bff;">📂 IMPORTAR XML / TXT</button>
                <input type="file" id="inputArquivo" style="display:none" accept=".xml,.txt" onchange="processarArquivo(this)">
                <button onclick="limparCampos()" class="btn-limpar">🧹 LIMPAR</button>
                <a href="/etiqueta" class="btn-voltar" style="color:white; text-decoration:none; font-weight: bold; padding: 10px 20px; background: #666; border-radius: 5px;">Sair</a>
            </div>

            <main>
                <div class="folha-ambiente-passivo">
                    <div class="container-duplo-passivo">

                        <h3 class="no-print" style="color: #333; text-align: center; margin-top: 20px;">MODELO: CAIXA</h3>
                        <div class="etiqueta-passivo-container" id="modelo-caixa">
                            <div class="bloco-conteudo-central">
                                <div class="header-passivo">
                                    <span class="escola-titulo" contenteditable="true">ESCOLA ESTADUAL SÃO JOSÉ</span>
                                    <div class="caixa-numero" contenteditable="true">CAIXA __</div>
                                </div>
                                <div class="corpo-passivo" id="lista-caixa" contenteditable="true"></div>
                            </div>
                        </div>

                        <hr style="width: 100%; border: 1px dashed #ccc; margin: 40px 0;" class="no-print">

                        <h3 class="no-print" style="color: #333; text-align: center;">MODELO: LIVRO</h3>
                        <div class="etiqueta-livro-container" id="modelo-livro">
                            <div class="bloco-conteudo-central">
                                <div class="header-passivo">
                                    <span class="escola-titulo" contenteditable="true">ESCOLA ESTADUAL SÃO JOSÉ</span>
                                    <div class="caixa-numero" contenteditable="true">CAIXA __</div>
                                </div>
                                <div class="corpo-passivo" id="lista-livro" contenteditable="true"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function processarArquivo(input) {
            const arquivo = input.files[0];
            if (!arquivo) return;

            const tituloSugerido = arquivo.name.replace('.xml', '').replace('.txt', '').replace(/_/g, ' ').toUpperCase();
            document.querySelectorAll('.caixa-numero').forEach(el => el.innerText = tituloSugerido);

            const leitor = new FileReader();
            leitor.onload = function(e) {
                const conteudo = e.target.result;
                const listaCaixa = document.getElementById('lista-caixa');
                const listaLivro = document.getElementById('lista-livro');

                listaCaixa.innerHTML = "";
                listaLivro.innerHTML = "";

                if (arquivo.name.endsWith('.xml')) {
                    const parser = new DOMParser();
                    const xmlDoc = parser.parseFromString(conteudo, "text/xml");
                    const tagsTexto = xmlDoc.getElementsByTagName("w:t");

                    for (let n = 0; n < tagsTexto.length; n++) {
                        let txt = tagsTexto[n].textContent.trim();
                        if (/^\d{4}/.test(txt)) {
                            adicionarLinhaSincronizada(txt.replace('-', '.'));
                        }
                    }
                } else {
                    conteudo.split(/\r?\n/).forEach(linha => {
                        if (linha.trim()) adicionarLinhaSincronizada(linha.replace('-', '.'));
                    });
                }
            };
            leitor.readAsText(arquivo);
        }

        function adicionarLinhaSincronizada(texto) {
            const divCaixa = document.createElement('div');
            divCaixa.textContent = texto;
            document.getElementById('lista-caixa').appendChild(divCaixa);

            const divLivro = document.createElement('div');
            divLivro.textContent = texto;
            document.getElementById('lista-livro').appendChild(divLivro);
        }

        function limparCampos() {
            if (confirm("Deseja apagar tudo?")) {
                document.querySelectorAll('.corpo-passivo').forEach(el => el.innerHTML = "");
                document.querySelectorAll('.caixa-numero').forEach(el => el.innerText = "CAIXA __");
            }
        }
    </script>
</body>

</html>