<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Sistema</title>
    <link rel="stylesheet" href="./assets/css/painel.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Painel de Controle</h1>
                <div class="header-meta">Data: <?php echo date('d/m/Y'); ?></div>
            </header>

            <main>
                <?php if (($_SESSION['usuario_tipo'] ?? '') === 'admin'): ?>
                    <?php if (!empty($msg_backup)): ?>
                        <div class="flash-message success">
                            Status do backup: <?php echo e($msg_backup); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($alertas_agenda)): ?>
                    <div class="niver-hoje" style="background-color: #e0f2fe; border-color: #bae6fd; color: #0369a1;">
                        <h2 style="color: #0c4a6e;">📅 Lembretes da Agenda (Próximos dias)</h2>
                        <?php foreach ($alertas_agenda as $aviso):
                            $data_br = date('d/m/Y', strtotime($aviso['data_aviso']));
                            $eh_hoje = ($aviso['data_aviso'] === date('Y-m-d'));
                        ?>
                            <div class="niver-card" style="background-color: #f0f9ff; border-color: #bae6fd;">
                                <span>
                                    <strong style="color: #0284c7; margin-right: 10px;">
                                        <?php echo $eh_hoje ? '📍 HOJE' : '📅 ' . $data_br; ?>
                                    </strong>
                                    <strong><?php echo htmlspecialchars($aviso['titulo']); ?></strong>

                                    <?php if (!empty($aviso['descricao'])): ?>
                                        <small style="display: block; color: #475569; margin-top: 5px;"><?php echo htmlspecialchars($aviso['descricao']); ?></small>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($feriados_painel)): ?>
                    <div class="niver-hoje painel-feriados">
                        <h2>Feriados próximos</h2>
                        <?php foreach ($feriados_painel as $feriado):
                            $data_br = date('d/m/Y', strtotime($feriado['date']));
                            $eh_hoje = ($feriado['date'] === date('Y-m-d'));
                            if (($feriado['type'] ?? '') === 'municipal') {
                                $tipo = 'Municipal';
                            } elseif (($feriado['type'] ?? '') === 'ponto_facultativo') {
                                $tipo = 'Ponto facultativo';
                            } else {
                                $tipo = 'Nacional';
                            }
                        ?>
                            <div class="niver-card painel-feriado-card">
                                <span>
                                    <strong class="painel-feriado-data">
                                        <?php echo $eh_hoje ? 'HOJE' : $data_br; ?>
                                    </strong>
                                    <strong><?php echo e($feriado['name']); ?></strong>
                                    <small><?php echo e($tipo); ?></small>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($certidoes_alerta)): ?>
                    <div class="alerta-container">
                        <div class="alerta-header" onclick="toggleSanfona('listaCertidoes', 'setaCert')">
                            <span>Atenção: certidões prestes a vencer (<?php echo count($certidoes_alerta); ?>)</span>
                            <span id="setaCert" class="seta-toggle">▼</span>
                        </div>
                        <div id="listaCertidoes" class="conteudo-sanfona" style="display: none;">
                            <div class="lista-certidoes">
                                <?php foreach ($certidoes_alerta as $c): ?>
                                    <div class="certidao-card">
                                        <div class="certidao-info">
                                            <strong><?php echo htmlspecialchars($c['tipo_certidao']); ?></strong>
                                            <small>Fornecedor: <?php echo htmlspecialchars($c['fornecedor']); ?></small>
                                        </div>
                                        <div class="certidao-data">
                                            <span class="tag-vencimento">
                                                Vence: <?php echo date('d/m/Y', strtotime($c['data_vencimento'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($aniversariantes_hoje)): ?>
                    <div class="niver-hoje">
                        <h2>🎈 Feliz aniversário</h2>
                        <?php foreach ($aniversariantes_hoje as $niver): ?>
                            <div class="niver-card">
                                <span>🎈 Hoje é o dia de <strong><?php echo htmlspecialchars($niver['nome_completo']); ?></strong>, completando <strong><?php echo $niver['idade_nova']; ?> anos</strong>.</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="stats-container">
                    <a href="/aluno" class="stat-card total">
                        <h3>Total de Alunos</h3>
                        <span class="stat-number"><?php echo $total_alunos; ?></span>
                    </a>
                    <div class="stat-card pendente is-clickable-card" onclick="toggleSanfona('secSemDva', 'setaSem')">
                        <h3>Sem DVA</h3>
                        <span class="stat-number"><?php echo $total_sem_dva; ?></span>
                    </div>
                    <div class="stat-card vencida is-clickable-card" onclick="toggleSanfona('secVencidas', 'setaVenc')">
                        <h3>DVAs Vencidas</h3>
                        <span class="stat-number"><?php echo $total_vencidas; ?></span>
                    </div>
                    <div class="stat-card avencer is-clickable-card" onclick="toggleSanfona('secAvencer', 'setaAvenc')">
                        <h3>A Vencer</h3>
                        <span class="stat-number"><?php echo $total_avencer; ?></span>
                    </div>
                </div>

                <div class="dashboard-search">
                    <label for="filtroPainel">Pesquisar na tela</label>
                    <input type="search" id="filtroPainel" class="sistema search-input" placeholder="Digite o nome do aluno ou aniversariante...">
                </div>

                <div class="relatorio relatorio-niver">
                    <h3 class="dashboard-section-title">
                        <span>Aniversariantes de <?php
                                                    $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                                                    echo $meses[(int)date('m')];
                                                    ?></span>
                    </h3>

                    <?php if (empty($aniversariantes_mes)): ?>
                        <p class="muted">Nenhum aniversariante este mês.</p>
                    <?php else: ?>
                        <div class="grid-niver">
                            <?php
                            $hoje_dia = date('d');
                            foreach ($aniversariantes_mes as $niver):
                                $dia = date('d', strtotime($niver['data_nascimento']));
                                $eh_hoje = ($dia == $hoje_dia);
                            ?>
                                <div class="card-mes item-filtrado <?php echo $eh_hoje ? 'eh-hoje' : ''; ?>">
                                    <div class="icon-badge"><?php echo $eh_hoje ? '🎈' : '🎂'; ?></div>
                                    <div>
                                        <div class="student-link nome-aluno"><?php echo htmlspecialchars($niver['nome_completo']); ?></div>
                                        <div class="muted">Dia <strong><?php echo $dia; ?></strong></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="relatorio">
                    <h3 class="dashboard-section-title is-clickable" onclick="toggleSanfona('secSemDva', 'setaSem')">
                        <span>Alunos Sem DVA (<?php echo $total_sem_dva; ?>)</span>
                        <span id="setaSem" class="seta-toggle">▼</span>
                    </h3>
                    <div id="secSemDva" class="conteudo-sanfona" style="display: none;">
                        <table class="tabela-filtrada">
                            <thead>
                                <tr>
                                    <th>Aluno</th>
                                    <th>Turma</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($lista_sem_dva ?? []) as $aluno): ?>
                                    <tr class="item-filtrado">
                                        <td class="nome-aluno">
                                            <a href="/aluno/perfil/<?php echo $aluno['id']; ?>" class="student-link">
                                                <?php echo htmlspecialchars($aluno['nome_completo']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($aluno['nome_turma'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="relatorio">
                    <h3 class="dashboard-section-title is-clickable danger" onclick="toggleSanfona('secVencidas', 'setaVenc')">
                        <span>DVAs Vencidas (<?php echo $total_vencidas; ?>)</span>
                        <span id="setaVenc" class="seta-toggle">▼</span>
                    </h3>
                    <div id="secVencidas" class="conteudo-sanfona" style="display: none;">
                        <table class="tabela-filtrada">
                            <thead>
                                <tr>
                                    <th>Aluno</th>
                                    <th>Turma</th>
                                    <th>Vencimento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista_vencidas as $dva): ?>
                                    <tr class="item-filtrado">
                                        <td class="nome-aluno">
                                            <a href="/aluno/perfil/<?php echo $dva['aluno_id']; ?>" class="student-link danger">
                                                <?php echo htmlspecialchars($dva['nome_completo']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($dva['nome_turma'] ?? '-'); ?></td>
                                        <td class="date-danger"><?php echo date('d/m/Y', strtotime($dva['data_vencimento'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="relatorio">
                    <h3 class="dashboard-section-title is-clickable warning" onclick="toggleSanfona('secAvencer', 'setaAvenc')">
                        <span>A Vencer (<?php echo $total_avencer; ?>)</span>
                        <span id="setaAvenc" class="seta-toggle">▼</span>
                    </h3>
                    <div id="secAvencer" class="conteudo-sanfona" style="display: none;">
                        <table class="tabela-filtrada">
                            <thead>
                                <tr>
                                    <th>Aluno</th>
                                    <th>Turma</th>
                                    <th>Vencimento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista_avencer as $dva): ?>
                                    <tr class="item-filtrado">
                                        <td class="nome-aluno">
                                            <a href="/aluno/perfil/<?php echo $dva['aluno_id']; ?>" class="student-link warning">
                                                <?php echo htmlspecialchars($dva['nome_completo']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($dva['nome_turma'] ?? '-'); ?></td>
                                        <td class="date-warning"><?php echo date('d/m/Y', strtotime($dva['data_vencimento'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="relatorio">
                    <h3 class="dashboard-section-title is-clickable success" onclick="toggleSanfona('secVigentes', 'setaVig')">
                        <span>Vigentes</span>
                        <span id="setaVig" class="seta-toggle">▼</span>
                    </h3>
                    <div id="secVigentes" class="conteudo-sanfona" style="display: none;">
                        <table class="tabela-filtrada">
                            <thead>
                                <tr>
                                    <th>Aluno</th>
                                    <th>Turma</th>
                                    <th>Vencimento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista_vigentes as $dva): ?>
                                    <tr class="item-filtrado">
                                        <td class="nome-aluno">
                                            <a href="/aluno/perfil/<?php echo $dva['aluno_id']; ?>" class="student-link">
                                                <?php echo htmlspecialchars($dva['nome_completo']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($dva['nome_turma'] ?? '-'); ?></td>
                                        <td class="date-success"><?php echo date('d/m/Y', strtotime($dva['data_vencimento'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function toggleSanfona(idConteudo, idSeta) {
            const conteudo = document.getElementById(idConteudo);
            const seta = document.getElementById(idSeta);
            if (!conteudo || !seta) return;

            if (conteudo.style.display !== 'block') {
                conteudo.style.display = 'block';
                seta.style.transform = 'rotate(180deg)';
                seta.textContent = '▲';
                setTimeout(() => {
                    conteudo.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }, 100);
            } else {
                conteudo.style.display = 'none';
                seta.style.transform = 'rotate(0deg)';
                seta.textContent = '▼';
            }
        }

        function removerAcentos(texto) {
            return texto ? texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '') : '';
        }

        document.getElementById('filtroPainel').addEventListener('input', function() {
            const filtro = removerAcentos(this.value);

            document.querySelectorAll('.item-filtrado').forEach(item => {
                const nomeEl = item.querySelector('.nome-aluno');
                if (!nomeEl) return;

                const nome = removerAcentos(nomeEl.textContent);
                item.style.display = nome.includes(filtro) ? '' : 'none';
            });

            if (filtro.length > 2) {
                document.querySelectorAll('.relatorio .conteudo-sanfona').forEach(c => {
                    c.style.display = 'block';
                });
                document.querySelectorAll('.relatorio .seta-toggle').forEach(s => {
                    s.style.transform = 'rotate(180deg)';
                    s.textContent = '▲';
                });
            }
        });
    </script>
</body>

</html>
