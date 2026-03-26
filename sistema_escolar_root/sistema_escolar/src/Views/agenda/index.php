<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Agenda de Avisos</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css">
    <link rel="stylesheet" href="/assets/css/contrato.css">
    <link rel="stylesheet" href="/assets/css/agenda.css">

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/pt-br.global.min.js'></script>
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>📅 Agenda de Avisos</h1>
                <p style="color: #64748b; margin-top: 5px;">Acompanhe os próximos compromissos no calendário abaixo.</p>
            </header>

            <main>
                <div class="relatorio" style="margin-bottom: 30px;">
                    <h3 class="form-section-title">Novo Aviso</h3>
                    <form action="/agenda/cadastrar" method="POST" class="formulario-sistema">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                        <div class="grid-form-inline" style="grid-template-columns: 1fr 3fr;">
                            <div class="form-group">
                                <label>Data do Aviso</label>
                                <input type="date" name="data_aviso" class="sistema" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label>Título / Assunto</label>
                                <input type="text" name="titulo" class="sistema" required placeholder="Ex: Reunião de Pais e Mestres">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Descrição (Opcional)</label>
                            <textarea name="descricao" class="sistema" rows="2" placeholder="Detalhes extras sobre o aviso..."></textarea>
                        </div>

                        <div class="toolbar-actions toolbar-actions--end" style="margin-top: 15px;">
                            <button type="submit" class="btn-primary">➕ Adicionar à Agenda</button>
                        </div>
                    </form>
                </div>

                <div class="relatorio" style="margin-bottom: 30px;">
                    <div id="calendar"></div>
                </div>

                <div class="relatorio">
                    <h3 class="form-section-title">Compromissos</h3>

                    <table class="tabela-filtrada">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Data</th>
                                <th>Assunto</th>
                                <th>Registrado por</th>
                                <th style="text-align: center; width: 100px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($avisos)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 30px; color: #666;">
                                        Nenhum aviso programado. Tudo tranquilo por aqui! 🏖️
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($avisos as $aviso): ?>
                                    <tr>
                                        <td><strong><?php echo date('d/m/Y', strtotime($aviso['data_aviso'])); ?></strong></td>
                                        <td><strong><?php echo htmlspecialchars($aviso['titulo']); ?></strong></td>
                                        <td><small><?php echo htmlspecialchars($aviso['autor_nome'] ?? 'Desconhecido'); ?></small></td>
                                        <td style="text-align: center;">
                                            <div class="table-actions">
                                                <?php
                                                $is_admin = ($_SESSION['usuario_tipo'] ?? '') === 'admin';
                                                $is_dono = $aviso['usuario_id'] == $_SESSION['usuario_id'];

                                                if ($is_admin || $is_dono):
                                                ?>
                                                    <form action="/agenda/excluir/<?php echo (int)$aviso['id']; ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja apagar este aviso?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                        <button type="submit" class="btn-danger btn-sm" title="Apagar aviso">🗑️</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <?php
    $eventos_calendario = [];
    if (!empty($avisos)) {
        foreach ($avisos as $aviso) {
            $eventos_calendario[] = [
                'title' => $aviso['titulo'],
                'start' => $aviso['data_aviso'],
                'description' => $aviso['descricao'], // guardamos a descrição para exibir no clique
                'color' => '#0056b3' // O azul do seu sistema
            ];
        }
    }
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var eventos = <?php echo json_encode($eventos_calendario); ?>;

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'pt-br',
                initialView: 'dayGridMonth',

                // --- NOVIDADES AQUI ---
                height: 550, // Define uma altura fixa para não estourar a tela (ajuste se precisar)
                contentHeight: 'auto', // Evita que os dias fiquem muito esticados

                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listWeek'
                },
                buttonText: {
                    today: 'Hoje',
                    month: 'Mês',
                    list: 'Lista da Semana'
                },
                events: eventos,

                // Ao clicar em um EVENTO JÁ EXISTENTE (Aviso)
                eventClick: function(info) {
                    let detalhes = info.event.extendedProps.description || 'Nenhum detalhe adicional.';
                    alert('Assunto: ' + info.event.title + '\n\nDetalhes: ' + detalhes);
                },

                // --- NOVA FUNÇÃO: Ao clicar em um DIA VAZIO no calendário ---
                dateClick: function(info) {
                    // 1. Preenche a data clicada direto no input do formulário
                    document.querySelector('input[name="data_aviso"]').value = info.dateStr;

                    // 2. Foca o cursor no campo de Título para você já sair digitando
                    document.querySelector('input[name="titulo"]').focus();

                    // 3. Rola a página suavemente para o topo (onde está o formulário)
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });

            calendar.render();
        });
    </script>
</body>

</html>