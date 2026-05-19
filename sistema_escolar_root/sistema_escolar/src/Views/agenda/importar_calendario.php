<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Importar Calendário Escolar</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/agenda.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/agenda.css'); ?>">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <div>
                    <h1>Importar Calendário Escolar</h1>
                    <p class="agenda-subtitle">Envie o PDF do calendário e revise os itens antes de salvar na Agenda.</p>
                </div>
                <a href="/agenda" class="btn-secondary">Voltar</a>
            </header>

            <main>
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <div class="relatorio agenda-import-card">
                    <h3 class="form-section-title">Enviar calendário</h3>

                    <form action="/agenda/importarCalendario" method="POST" enctype="multipart/form-data" class="sistema">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                        <input type="hidden" name="acao" value="analisar">

                        <div class="grid-form-inline agenda-import-grid">
                            <div class="form-group">
                                <label>Ano do calendário</label>
                                <input type="number" name="ano" class="sistema" min="2020" max="2100" value="<?php echo (int)$ano; ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Arquivo PDF</label>
                                <input type="file" name="calendario_pdf" class="sistema" accept="application/pdf,.pdf">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Texto do calendário (opcional)</label>
                            <textarea name="texto_calendario" class="sistema" rows="5" placeholder="Use este campo se o PDF for imagem ou se a extração automática não encontrar os eventos."><?php echo e($resultado['texto_extraido'] ?? ''); ?></textarea>
                        </div>

                        <div class="toolbar-actions toolbar-actions--end agenda-form-actions">
                            <button type="submit" class="btn-primary">Analisar calendário</button>
                        </div>
                    </form>
                </div>

                <?php if (!empty($resultado)): ?>
                    <div class="relatorio agenda-import-card">
                        <h3 class="form-section-title">Resultado da análise</h3>

                        <?php if (!empty($resultado['avisos'])): ?>
                            <?php foreach ($resultado['avisos'] as $aviso): ?>
                                <?php echo alerta_html('aviso', 'Atenção', $aviso); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php echo alerta_html(!empty($resultado['sucesso']) ? 'info' : 'aviso', 'Importação', $resultado['mensagem'] ?? ''); ?>

                        <?php if (!empty($eventos)): ?>
                            <form action="/agenda/importarCalendario" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                <input type="hidden" name="acao" value="salvar">

                                <div class="agenda-import-actions">
                                    <button type="button" class="btn-secondary" id="marcarTodos">Marcar todos</button>
                                    <button type="button" class="btn-secondary" id="desmarcarTodos">Desmarcar todos</button>
                                </div>

                                <table class="tabela-filtrada agenda-import-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">Salvar</th>
                                            <th style="width: 120px;">Data</th>
                                            <th>Evento</th>
                                            <th style="width: 160px;">Tipo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($eventos as $indice => $evento): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="eventos[]" value="<?php echo (int)$indice; ?>" checked>
                                                </td>
                                                <td><strong><?php echo date('d/m/Y', strtotime($evento['data'])); ?></strong></td>
                                                <td>
                                                    <strong><?php echo e($evento['titulo']); ?></strong>
                                                    <?php if (!empty($evento['descricao'])): ?>
                                                        <small class="agenda-import-desc"><?php echo e($evento['descricao']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo e($evento['tipo'] ?? 'Evento'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <div class="toolbar-actions toolbar-actions--end agenda-form-actions">
                                    <button type="submit" class="btn-primary">Salvar selecionados na Agenda</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        const marcarTodos = document.getElementById('marcarTodos');
        const desmarcarTodos = document.getElementById('desmarcarTodos');
        const checkboxes = () => document.querySelectorAll('input[name="eventos[]"]');

        if (marcarTodos) {
            marcarTodos.addEventListener('click', function() {
                checkboxes().forEach(function(campo) {
                    campo.checked = true;
                });
            });
        }

        if (desmarcarTodos) {
            desmarcarTodos.addEventListener('click', function() {
                checkboxes().forEach(function(campo) {
                    campo.checked = false;
                });
            });
        }
    </script>
</body>

</html>
