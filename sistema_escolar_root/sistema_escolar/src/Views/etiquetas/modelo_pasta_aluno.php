<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Ficha de Aluno - E.E São José</title>
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
                <button onclick="window.print()" class="btn-imprimir">🖨️ IMPRIMIR AS 2 FICHAS</button>
                <button onclick="limparTudo()" class="btn-limpar">🧹 LIMPAR TUDO</button>
                <a href="/etiqueta" class="btn-voltar" style="color:white; text-decoration:none; font-weight: bold; padding: 10px 20px; background: #666; border-radius: 5px;">Sair do Editor</a>
            </div>

            <main>
                <div class="folha-ambiente">
                    <div class="folha-a4">

                        <?php for ($e = 1; $e <= 2; $e++): ?>
                            <div class="etiqueta-ficha">
                                <div class="cabecalho-escola">
                                    <img src="/assets/image/logo_escola.png" class="logo-ficha" alt="Logo">
                                    <span class="nome-escola">E.E São José - Vicentina/MS</span>
                                </div>

                                <table class="tabela-principal">
                                    <tr>
                                        <td colspan="5" class="borda-baixo borda-direita" style="width: 60%;">
                                            <div class="celula-travada">
                                                <span class="label-ficha">ESTUDANTE:⠀⠀⠀</span>
                                                <div class="input-ficha" contenteditable="true"></div>
                                            </div>
                                        </td>
                                        <td colspan="5" class="borda-baixo" style="width: 40%;">
                                            <div class="celula-travada">
                                                <span class="label-ficha">SGDE:</span>
                                                <div class="input-ficha" contenteditable="true"></div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="5" class="borda-direita" style="vertical-align: top;">
                                            <table class="tabela-interna">
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">TELEFONE:⠀⠀⠀⠀</span>
                                                            <div class="input-ficha" contenteditable="true" data-mask="tel"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">ID:⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀</span>
                                                            <div class="input-ficha" contenteditable="true"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada centralizado"><strong>DOCUMENTOS</strong></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">CPF:⠀⠀⠀⠀⠀⠀⠀⠀⠀</span>
                                                            <div class="input-ficha" contenteditable="true" data-mask="cpf"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">RG:⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀</span>
                                                            <div class="input-ficha" contenteditable="true"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">SUS:⠀⠀⠀⠀⠀⠀⠀⠀⠀</span>
                                                            <div class="input-ficha" contenteditable="true" data-mask="sus"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">NATURALIDADE:</span>
                                                            <div class="input-ficha" contenteditable="true"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">NASCIMENTO:⠀⠀</span>
                                                            <div class="input-ficha" contenteditable="true" data-mask="data"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">PAI:⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀</span>
                                                            <div class="input-ficha" contenteditable="true"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">MÃE:⠀⠀⠀⠀⠀⠀⠀⠀⠀</span>
                                                            <div class="input-ficha" contenteditable="true"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">ENDEREÇO:⠀⠀⠀⠀</span>
                                                            <div class="input-ficha" contenteditable="true"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="borda-baixo">
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀</span>
                                                            <div class="input-ficha" contenteditable="true"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="celula-travada">
                                                            <span class="label-ficha">⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀</span>
                                                            <div class="input-ficha" contenteditable="true"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>

                                        <td colspan="5" style="vertical-align: top;">
                                            <table class="tabela-interna">
                                                <tr class="header-grade">
                                                    <td class="borda-direita borda-baixo">ANO LETIVO</td>
                                                    <td class="borda-direita borda-baixo">Nº</td>
                                                    <td class="borda-direita borda-baixo">TURMA</td>
                                                    <td class="borda-direita borda-baixo">TURNO</td>
                                                    <td class="borda-baixo">R. FIN</td>
                                                </tr>
                                                <?php for ($i = 0; $i < 12; $i++): ?>
                                                    <tr class="linha-grade">
                                                        <td class="borda-direita borda-baixo">
                                                            <div class="celula-travada">
                                                                <div class="input-ficha" contenteditable="true"></div>
                                                            </div>
                                                        </td>
                                                        <td class="borda-direita borda-baixo">
                                                            <div class="celula-travada">
                                                                <div class="input-ficha" contenteditable="true"></div>
                                                            </div>
                                                        </td>
                                                        <td class="borda-direita borda-baixo">
                                                            <div class="celula-travada">
                                                                <div class="input-ficha" contenteditable="true"></div>
                                                            </div>
                                                        </td>
                                                        <td class="borda-direita borda-baixo">
                                                            <div class="celula-travada">
                                                                <div class="input-ficha" contenteditable="true"></div>
                                                            </div>
                                                        </td>
                                                        <td class="borda-baixo">
                                                            <div class="celula-travada">
                                                                <div class="input-ficha" contenteditable="true"></div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endfor; ?>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('input', function(e) {
            const campo = e.target;
            const mascara = campo.getAttribute('data-mask');
            if (!mascara) return;

            let valor = campo.innerText.replace(/\D/g, '');

            if (mascara === 'tel') {
                valor = valor.substring(0, 11);
                valor = valor.replace(/^(\d{2})(\d)/g, '($1) $2');
                if (valor.length > 9) {
                    valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
                } else {
                    valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
                }
            } else if (mascara === 'cpf') {
                valor = valor.substring(0, 11);
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else if (mascara === 'sus') {
                valor = valor.substring(0, 15);
                valor = valor.replace(/(\d{3})(\d)/, '$1 $2');
                valor = valor.replace(/(\d{4})(\d)/, '$1 $2');
                valor = valor.replace(/(\d{4})(\d)/, '$1 $2');
            } else if (mascara === 'data') {
                valor = valor.substring(0, 8);
                valor = valor.replace(/(\d{2})(\d)/, '$1/$2');
                valor = valor.replace(/(\d{2})(\d)/, '$1/$2');
            }

            if (campo.innerText !== valor) {
                campo.innerText = valor;
                const range = document.createRange();
                const sel = window.getSelection();
                if (campo.childNodes.length > 0) {
                    range.setStart(campo.childNodes[0], valor.length);
                    range.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && event.target.classList.contains('input-ficha')) {
                event.preventDefault();
                return false;
            }
        });

        document.addEventListener('paste', function(e) {
            if (e.target.classList.contains('input-ficha')) {
                e.preventDefault();
                var text = e.clipboardData.getData('text/plain');
                document.execCommand('insertText', false, text);
            }
        });

        function limparTudo() {
            if (confirm("Tem certeza que deseja apagar todos os campos preenchidos?")) {
                document.querySelectorAll('.input-ficha').forEach(campo => {
                    campo.innerText = "";
                });
            }
        }
    </script>
</body>

</html>