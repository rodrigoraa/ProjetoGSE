<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Perfil do Aluno</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css">
</head>

<body>

    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Perfil do Aluno</h1>
                <div style="font-size:0.9em; color:#666;">ID: #<?php echo $aluno['id']; ?></div>
            </header>

            <main>
                <div class="card-perfil">

                    <?php if ($status == 'vencida'): ?>
                        <div class="status-box st-vencida">
                            <h2>⚠️ DVA VENCIDA</h2>
                            <p>Venceu há <strong><?php echo abs($dias); ?> dias</strong> (<?php echo date('d/m/Y', strtotime($aluno['data_dva'])); ?>)</p>
                        </div>
                    <?php elseif ($status == 'avencer'): ?>
                        <div class="status-box st-avencer">
                            <h2>⚠️ ATENÇÃO</h2>
                            <p>Vence em <strong><?php echo $dias; ?> dias</strong> (<?php echo date('d/m/Y', strtotime($aluno['data_dva'])); ?>)</p>
                        </div>
                    <?php elseif ($status == 'vigente'): ?>
                        <div class="status-box st-vigente">
                            <h2>✅ DVA VIGENTE</h2>
                            <p>Vence em <strong><?php echo date('d/m/Y', strtotime($aluno['data_dva'])); ?></strong></p>
                        </div>
                    <?php else: ?>
                        <div class="status-box st-sem_dva">
                            <h2>⚪ SEM DVA REGISTRADA</h2>
                            <p>Este aluno não possui data de vencimento cadastrada.</p>
                        </div>
                    <?php endif; ?>

                    <div class="perfil-info-grid">
                        <div class="info-item">
                            <div class="info-label">Nome Completo</div>
                            <div class="info-valor"><?php echo htmlspecialchars($aluno['nome_completo']); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Turma</div>
                            <div class="info-valor"><?php echo htmlspecialchars($aluno['nome_turma'] ?? 'Sem turma'); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Data de Nascimento</div>
                            <div class="info-valor"><?php echo date('d/m/Y', strtotime($aluno['data_nascimento'])); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Observações DVA</div>
                            <div class="info-valor" style="font-weight: normal; font-size: 1rem;">
                                <?php echo nl2br(htmlspecialchars($aluno['obs_dva'] ?? '-')); ?>
                            </div>
                        </div>
                    </div>

                    <div class="contatos-container">
                        <div class="contatos-titulo">📞 Contatos Registrados</div>

                        <div class="contatos-grid">
                            <div class="contato-card">
                                <div class="contato-label">📱 Aluno</div>
                                <div class="contato-numero">
                                    <?php echo !empty($aluno['telefone_aluno']) ? htmlspecialchars($aluno['telefone_aluno']) : '<span style="color:#999; font-weight:normal;">Não informado</span>'; ?>
                                </div>
                                <?php if (!empty($aluno['telefone_aluno'])):
                                    $num = preg_replace('/[^0-9]/', '', $aluno['telefone_aluno']);
                                ?>
                                    <a href="https://wa.me/55<?php echo $num; ?>" target="_blank" class="btn-whatsapp-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592z" />
                                        </svg>
                                        Chamar Aluno
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="contato-card">
                                <div class="contato-label">👨‍👩‍👦 Responsável</div>
                                <div class="contato-numero">
                                    <?php echo !empty($aluno['telefone_responsavel']) ? htmlspecialchars($aluno['telefone_responsavel']) : '<span style="color:#999; font-weight:normal;">Não informado</span>'; ?>
                                </div>
                                <?php if (!empty($aluno['telefone_responsavel'])):
                                    $numResp = preg_replace('/[^0-9]/', '', $aluno['telefone_responsavel']);
                                ?>
                                    <a href="https://wa.me/55<?php echo $numResp; ?>" target="_blank" class="btn-whatsapp-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592z" />
                                        </svg>
                                        Chamar Responsável
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="perfil-actions" style="margin-top: 30px;">
                        <a href="/aluno/editar/<?php echo $aluno['id']; ?>" class="btn-primary" style="text-decoration:none;">
                            ✏️ Editar Dados / Renovar DVA
                        </a>

                        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                            <a href="/aluno/excluir/<?php echo $aluno['id']; ?>" class="btn-secondary" style="background:var(--danger-color); text-decoration:none;" onclick="return confirm('Tem certeza que deseja apagar este aluno permanentemente?');">
                                🗑️ Apagar Aluno
                            </a>
                        <?php endif; ?>

                        <a href="/aluno" class="cancelar">Voltar para Lista</a>
                    </div>

                </div>
            </main>
        </div>
    </div>
</body>

</html>