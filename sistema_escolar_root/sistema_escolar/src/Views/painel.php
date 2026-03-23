<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Sistema</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
</head>

<body>

    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Painel de Controle</h1>
                <div style="color: var(--text-muted);">Data: <?php echo date('d/m/Y'); ?></div>
            </header>

            <main>
                <?php if (!empty($msg_backup)): ?>
                    <div style="background-color: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 8px; margin-bottom: 20px;">
                        ✅ <?php echo $msg_backup; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($certidoes_alerta)): ?>
                    <div class="alerta-container" style="margin-bottom: 25px;">
                        <div class="alerta-header" onclick="toggleSanfona('listaCertidoes', 'setaCert')" style="cursor: pointer; display: flex; justify-content: space-between; background: #fff3cd; padding: 15px; border-radius: 8px; border: 1px solid #ffeeba; color: #856404;">
                            <span style="font-weight: bold;">⚠️ ATENÇÃO: Certidões prestes a vencer (<?php echo count($certidoes_alerta); ?>)</span>
                            <span id="setaCert" class="seta-toggle">▼</span>
                        </div>
                        <div id="listaCertidoes" class="conteudo-sanfona" style="display: none;">
                            <?php foreach ($certidoes_alerta as $c): ?>
                                <div class="certidao-card">
                                    <div class="certidao-info">
                                        <strong style="display:block;"><?php echo htmlspecialchars($c['tipo_certidao']); ?></strong>
                                        <small>Fornecedor: <?php echo htmlspecialchars($c['fornecedor']); ?></small>
                                    </div>
                                    <div class="certidao-data">
                                        <span class="tag-vencimento" style="background:#dc3545; color:#fff; padding:4px 8px; border-radius:4px; font-size:0.85em;">
                                            Vence: <?php echo date('d/m/Y', strtotime($c['data_vencimento'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($aniversariantes_hoje)): ?>
                    <div class="niver-hoje" style="margin-bottom: 25px; background: #fce4ec; padding: 15px; border-radius: 8px; border: 1px solid #f8bbd0;">
                        <h2 style="color: #c2185b; margin-top: 0;">🎉 Feliz Aniversário Hoje!</h2>
                        <?php foreach ($aniversariantes_hoje as $niver): ?>
                            <div class="niver-card" style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                <span style="font-size: 1.5em;">🎂</span>
                                <span>
                                    Hoje é o dia de <strong><?php echo htmlspecialchars($niver['nome_completo']); ?></strong>,
                                    completando <strong><?php echo $niver['idade_nova']; ?> anos</strong>!
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="stats-container">
                    <a href="/aluno" class="stat-card total">
                        <h3>Total Alunos</h3><span class="stat-number"><?php echo $total_alunos; ?></span>
                    </a>
                    <div class="stat-card pendente" onclick="toggleSanfona('secSemDva', 'setaSem')" style="cursor:pointer">
                        <h3>Sem DVA</h3><span class="stat-number"><?php echo $total_sem_dva; ?></span>
                    </div>
                    <div class="stat-card vencida" onclick="toggleSanfona('secVencidas', 'setaVenc')" style="cursor:pointer">
                        <h3>DVAs Vencidas</h3><span class="stat-number"><?php echo $total_vencidas; ?></span>
                    </div>
                    <div class="stat-card avencer" onclick="toggleSanfona('secAvencer', 'setaAvenc')" style="cursor:pointer">
                        <h3>A Vencer</h3><span class="stat-number"><?php echo $total_avencer; ?></span>
                    </div>
                </div>

                <div style="margin: 25px 0 15px 0;">
                    <label style="font-weight: bold; color: #666;">Pesquisar na tela:</label>
                    <input type="search" id="filtroPainel" placeholder="Digite o nome do aluno ou aniversariante..." class="sistema" style="width: 100%; padding: 12px; border-radius: 6px; border: 1px solid #ccc; margin-top: 5px;">
                </div>

                <div class="relatorio-niver" style="background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
                    <h3 style="color: #C2185B; margin-top:0; border-bottom: 2px solid #fce4ec; padding-bottom: 10px;">
                        📅 Aniversariantes de <?php
                                                $meses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                                                echo $meses[(int)date('m')];
                                                ?>
                    </h3>
                    <?php if (empty($aniversariantes_mes)): ?>
                        <p style="color: #999;">Nenhum aniversariante este mês.</p>
                    <?php else: ?>
                        <div class="grid-niver">
                            <?php
                            $hoje_dia = date('d');
                            foreach ($aniversariantes_mes as $niver):
                                $dia = date('d', strtotime($niver['data_nascimento']));
                                $eh_hoje = ($dia == $hoje_dia);
                            ?>
                                <div class="card-mes item-filtrado <?php echo $eh_hoje ? 'eh-hoje' : ''; ?>">
                                    <div style="font-size: 1.5em;"><?php echo $eh_hoje ? '🎈' : '📅'; ?></div>
                                    <div>
                                        <div class="nome-aluno" style="font-weight: bold; font-size: 0.9em;">
                                            <?php echo htmlspecialchars($niver['nome_completo']); ?>
                                        </div>
                                        <div style="font-size: 0.85em; color: var(--text-muted);">Dia <strong><?php echo $dia; ?></strong></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="relatorio">
                    <h3 style="color: #666;" onclick="toggleSanfona('secSemDva', 'setaSem')" style="cursor: pointer;">
                        ⚪ Alunos Sem DVA (<?php echo $total_sem_dva; ?>)
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
                                            <a href="/aluno/perfil/<?php echo $aluno['id']; ?>" style="color:#666; font-weight:bold">
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
                    <h3 style="color: #dc3545;" onclick="toggleSanfona('secVencidas', 'setaVenc')" style="cursor: pointer;">
                        🔴 DVAs Vencidas (<?php echo $total_vencidas; ?>)
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
                                        <td class="nome-aluno"><a href="/aluno/perfil/<?php echo $dva['aluno_id']; ?>" style="color:#dc3545;font-weight:bold"><?php echo htmlspecialchars($dva['nome_completo']); ?></a></td>
                                        <td><?php echo htmlspecialchars($dva['nome_turma'] ?? '-'); ?></td>
                                        <td style="color:#dc3545;font-weight:bold"><?php echo date('d/m/Y', strtotime($dva['data_vencimento'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="relatorio">
                    <h3 style="color: #d39e00;" onclick="toggleSanfona('secAvencer', 'setaAvenc')" style="cursor: pointer;">
                        🟡 A Vencer (<?php echo $total_avencer; ?>)
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
                                        <td class="nome-aluno"><a href="/aluno/perfil/<?php echo $dva['aluno_id']; ?>" style="color:#d39e00;font-weight:bold"><?php echo htmlspecialchars($dva['nome_completo']); ?></a></td>
                                        <td><?php echo htmlspecialchars($dva['nome_turma'] ?? '-'); ?></td>
                                        <td style="color:#d39e00;font-weight:bold"><?php echo date('d/m/Y', strtotime($dva['data_vencimento'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="relatorio">
                    <h3 style="color: #28a745;" onclick="toggleSanfona('secVigentes', 'setaVig')" style="cursor: pointer;">
                        🟢 Vigentes
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
                                        <td class="nome-aluno"><a href="/aluno/perfil/<?php echo $dva['aluno_id']; ?>" style="color:var(--primary-color);font-weight:bold"><?php echo htmlspecialchars($dva['nome_completo']); ?></a></td>
                                        <td><?php echo htmlspecialchars($dva['nome_turma'] ?? '-'); ?></td>
                                        <td style="color:#28a745;font-weight:bold"><?php echo date('d/m/Y', strtotime($dva['data_vencimento'])); ?></td>
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
            if (!conteudo) return;

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
            return texto ? texto.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "") : "";
        }

        document.getElementById('filtroPainel').addEventListener('input', function() {
            let filtro = removerAcentos(this.value);

            document.querySelectorAll('.item-filtrado').forEach(item => {
                let nomeEl = item.querySelector('.nome-aluno');
                if (nomeEl) {
                    let nome = removerAcentos(nomeEl.textContent);
                    item.style.display = nome.includes(filtro) ? '' : 'none';
                }
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