<?php
$totalAvisos = count($avisos ?? []);
$autoresAgenda = [];
$proximoAviso = null;

foreach (($avisos ?? []) as $avisoResumo) {
    $autorNome = trim((string)($avisoResumo['autor_nome'] ?? ''));
    if ($autorNome !== '') {
        $autoresAgenda[$autorNome] = true;
    }

    if ($proximoAviso === null) {
        $proximoAviso = $avisoResumo['data_aviso'] ?? null;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Agenda de Avisos</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/agenda.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/agenda.css'); ?>">

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/pt-br.global.min.js"></script>
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <div>
                    <h1>Agenda de Avisos</h1>
                    <p class="agenda-subtitle">Acompanhe os próximos compromissos no calendário e gerencie os avisos da equipe em um só lugar.</p>
                </div>
                <?php if (($_SESSION['usuario_tipo'] ?? '') === 'admin'): ?>
                    <a href="/agenda/importarCalendario" class="btn-secondary">Importar calendário escolar</a>
                <?php endif; ?>
            </header>

            <main>
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <section class="agenda-hero">
                    <div>
                        <h2>Painel central dos avisos da equipe</h2>
                        <p>Concentre lembretes, eventos e comunicações internas em uma agenda visual com consulta rápida e registro por autor.</p>
                    </div>
                    <div class="agenda-hero-stats">
                        <div class="agenda-stat">
                            <strong><?php echo (int)$totalAvisos; ?></strong>
                            <span>Avisos</span>
                        </div>
                        <div class="agenda-stat">
                            <strong><?php echo count($autoresAgenda); ?></strong>
                            <span>Autores</span>
                        </div>
                        <div class="agenda-stat">
                            <strong><?php echo $proximoAviso ? date('d/m', strtotime($proximoAviso)) : '--'; ?></strong>
                            <span>Próximo aviso</span>
                        </div>
                    </div>
                </section>

                <div class="agenda-grid">
                    <div class="relatorio agenda-card agenda-card-form">
                        <h3 class="form-section-title" id="agendaFormTitle">Novo Aviso</h3>
                        <form action="/agenda/cadastrar" method="POST" class="sistema" id="agendaForm" data-create-action="/agenda/cadastrar">
                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                            <div class="grid-form-inline agenda-form-grid">
                                <div class="form-group">
                                    <label>Data do Aviso</label>
                                    <input type="date" name="data_aviso" class="sistema" id="agendaDataAviso" required value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Título / Assunto</label>
                                    <input type="text" name="titulo" class="sistema" id="agendaTitulo" required placeholder="Ex: Reunião de Pais e Mestres">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Descrição (Opcional)</label>
                                <textarea name="descricao" class="sistema" id="agendaDescricao" rows="2" placeholder="Detalhes extras sobre o aviso..."></textarea>
                            </div>

                            <div class="toolbar-actions toolbar-actions--end agenda-form-actions">
                                <button type="button" class="btn-secondary agenda-cancel-edit" id="agendaCancelEdit" hidden>Cancelar edição</button>
                                <button type="submit" class="btn-primary" id="agendaSubmitButton">Adicionar à Agenda</button>
                            </div>
                        </form>
                    </div>

                    <div class="relatorio agenda-card agenda-card-tips">
                        <h3 class="form-section-title">Como usar</h3>
                        <div class="agenda-tip-list">
                            <p>Clique em um dia do calendário para preencher a data do novo aviso.</p>
                            <p>Clique em um evento para ver os detalhes sem sair da página.</p>
                            <p>Use a lista abaixo para conferir rapidamente quem registrou cada compromisso.</p>
                        </div>
                    </div>
                </div>

                <div class="relatorio agenda-calendar-wrap">
                    <div id="calendar"></div>
                </div>

                <div class="relatorio">
                    <h3 class="form-section-title">Compromissos</h3>

                    <table class="tabela-filtrada">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Data</th>
                                <th>Assunto</th>
                                <th>Descrição</th>
                                <th>Registrado por</th>
                                <th style="text-align: center; width: 170px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($avisos)): ?>
                                <tr>
                                    <td colspan="5" class="agenda-empty-state">
                                        Nenhum aviso programado. Tudo tranquilo por aqui.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($avisos as $aviso): ?>
                                    <tr>
                                        <td><strong><?php echo date('d/m/Y', strtotime($aviso['data_aviso'])); ?></strong></td>
                                        <td><strong><?php echo e($aviso['titulo']); ?></strong></td>
                                        <td class="agenda-description-cell">
                                            <?php if (!empty($aviso['descricao'])): ?>
                                                <?php echo e($aviso['descricao']); ?>
                                            <?php else: ?>
                                                <span class="agenda-muted">Sem detalhes adicionais.</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><small><?php echo e($aviso['autor_nome'] ?? 'Desconhecido'); ?></small></td>
                                        <td style="text-align: center;">
                                            <div class="table-actions">
                                                <?php
                                                $is_admin = ($_SESSION['usuario_tipo'] ?? '') === 'admin';
                                                $is_dono = $aviso['usuario_id'] == $_SESSION['usuario_id'];

                                                if ($is_admin || $is_dono):
                                                ?>
                                                    <button
                                                        type="button"
                                                        class="btn-secondary btn-sm agenda-edit-button"
                                                        title="Editar aviso"
                                                        data-id="<?php echo (int)$aviso['id']; ?>"
                                                        data-data="<?php echo e($aviso['data_aviso']); ?>"
                                                        data-titulo="<?php echo e($aviso['titulo']); ?>"
                                                        data-descricao="<?php echo e($aviso['descricao'] ?? ''); ?>">
                                                        Editar
                                                    </button>
                                                    <form action="/agenda/excluir/<?php echo (int)$aviso['id']; ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja apagar este aviso?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                        <button type="submit" class="btn-danger btn-sm" title="Apagar aviso">Apagar</button>
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
                'id' => (int)$aviso['id'],
                'title' => $aviso['titulo'],
                'start' => $aviso['data_aviso'],
                'description' => $aviso['descricao'],
                'author' => $aviso['autor_nome'] ?? 'Desconhecido',
                'color' => '#0056b3'
            ];
        }
    }
    if (!empty($feriados)) {
        foreach ($feriados as $indice => $feriado) {
            $tipoFeriado = $feriado['type'] ?? 'nacional';
            $rotuloFeriado = 'Feriado Nacional Oficial';
            $corFeriado = '#28a745';

            if ($tipoFeriado === 'municipal') {
                $rotuloFeriado = 'Feriado Municipal';
                $corFeriado = '#d97706';
            } elseif ($tipoFeriado === 'ponto_facultativo') {
                $rotuloFeriado = 'Ponto facultativo / Data móvel';
                $corFeriado = '#7c3aed';
            }

            $eventos_calendario[] = [
                'id' => 'feriado_' . $indice,
                'title' => '🌟 ' . $feriado['name'],
                'start' => $feriado['date'],
                'description' => $rotuloFeriado,
                'author' => 'Calendário',
                'color' => $corFeriado
            ];
        }
    }
    ?>

    <div id="agendaModal" class="agenda-modal" aria-hidden="true">
        <div class="agenda-modal-backdrop" data-close-modal="true"></div>
        <div class="agenda-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="agendaModalTitulo">
            <div class="agenda-modal-header">
                <div>
                    <p class="agenda-modal-kicker">Detalhes do Aviso</p>
                    <h3 id="agendaModalTitulo">Aviso</h3>
                </div>
                <button type="button" class="agenda-modal-close" id="agendaModalClose" aria-label="Fechar">X</button>
            </div>

            <div class="agenda-modal-body">
                <div class="agenda-modal-meta">
                    <span id="agendaModalData">--/--/----</span>
                    <span id="agendaModalAutor">Registrado por --</span>
                </div>
                <p id="agendaModalDescricao" class="agenda-modal-description">Nenhum detalhe adicional.</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const agendaForm = document.getElementById('agendaForm');
            const agendaFormTitle = document.getElementById('agendaFormTitle');
            const formDataAviso = document.getElementById('agendaDataAviso');
            const formTitulo = document.getElementById('agendaTitulo');
            const formDescricao = document.getElementById('agendaDescricao');
            const submitButton = document.getElementById('agendaSubmitButton');
            const cancelEditButton = document.getElementById('agendaCancelEdit');
            const modal = document.getElementById('agendaModal');
            const modalTitulo = document.getElementById('agendaModalTitulo');
            const modalData = document.getElementById('agendaModalData');
            const modalAutor = document.getElementById('agendaModalAutor');
            const modalDescricao = document.getElementById('agendaModalDescricao');
            const modalClose = document.getElementById('agendaModalClose');
            const eventos = <?php echo json_encode($eventos_calendario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

            function resetarFormularioAgenda() {
                agendaForm.setAttribute('action', agendaForm.dataset.createAction);
                agendaFormTitle.textContent = 'Novo Aviso';
                submitButton.textContent = 'Adicionar à Agenda';
                submitButton.disabled = false;
                cancelEditButton.hidden = true;
                formDataAviso.value = '<?php echo date('Y-m-d'); ?>';
                formTitulo.value = '';
                formDescricao.value = '';
            }

            function ativarEdicaoAviso(botao) {
                agendaForm.setAttribute('action', '/agenda/editar/' + botao.dataset.id);
                agendaFormTitle.textContent = 'Editar Aviso';
                submitButton.textContent = 'Salvar alterações';
                submitButton.disabled = false;
                cancelEditButton.hidden = false;
                formDataAviso.value = botao.dataset.data || '<?php echo date('Y-m-d'); ?>';
                formTitulo.value = botao.dataset.titulo || '';
                formDescricao.value = botao.dataset.descricao || '';
                formTitulo.focus();

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            function formatarData(dataIso) {
                const partes = String(dataIso).split('-');

                if (partes.length !== 3) {
                    return dataIso;
                }

                return partes[2] + '/' + partes[1] + '/' + partes[0];
            }

            function abrirModalAviso(evento) {
                modalTitulo.textContent = evento.title || 'Aviso';
                modalData.textContent = formatarData(evento.startStr || '');
                modalAutor.textContent = 'Registrado por ' + (evento.extendedProps.author || 'Desconhecido');
                modalDescricao.textContent = evento.extendedProps.description || 'Nenhum detalhe adicional.';
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            }

            function fecharModalAviso() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'pt-br',
                initialView: 'dayGridMonth',
                height: 550,
                contentHeight: 'auto',
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
                eventClick: function(info) {
                    abrirModalAviso(info.event);
                },
                dateClick: function(info) {
                    if (agendaForm.getAttribute('action') !== agendaForm.dataset.createAction) {
                        resetarFormularioAgenda();
                    }

                    formDataAviso.value = info.dateStr;
                    formTitulo.focus();

                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });

            calendar.render();

            document.querySelectorAll('.agenda-edit-button').forEach(function(botao) {
                botao.addEventListener('click', function() {
                    ativarEdicaoAviso(botao);
                });
            });

            cancelEditButton.addEventListener('click', resetarFormularioAgenda);

            agendaForm.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.textContent = agendaForm.getAttribute('action') === agendaForm.dataset.createAction ? 'Adicionando...' : 'Salvando...';
            });

            modalClose.addEventListener('click', fecharModalAviso);

            modal.addEventListener('click', function(event) {
                if (event.target.hasAttribute('data-close-modal')) {
                    fecharModalAviso();
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    fecharModalAviso();
                }
            });
        });
    </script>
</body>

</html>
