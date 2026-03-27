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
            </header>

            <main>
                <div class="card-perfil">
                    <div class="perfil-head">
                        <div>
                            <h2><?php echo e($aluno['nome_completo']); ?></h2>
                            <p>Resumo completo do cadastro, situação da DVA e canais de contato disponíveis.</p>
                        </div>
                        <span class="perfil-id">ID: #<?php echo (int)$aluno['id']; ?></span>
                    </div>

                    <?php if ($status == 'vencida'): ?>
                        <div class="status-box st-vencida">
                            <h2>DVA VENCIDA</h2>
                            <p>Venceu há <strong><?php echo abs($dias); ?> dias</strong> (<?php echo date('d/m/Y', strtotime($aluno['data_dva'])); ?>)</p>
                        </div>
                    <?php elseif ($status == 'avencer'): ?>
                        <div class="status-box st-avencer">
                            <h2>ATENÇÃO</h2>
                            <p>Vence em <strong><?php echo (int)$dias; ?> dias</strong> (<?php echo date('d/m/Y', strtotime($aluno['data_dva'])); ?>)</p>
                        </div>
                    <?php elseif ($status == 'vigente'): ?>
                        <div class="status-box st-vigente">
                            <h2>DVA VIGENTE</h2>
                            <p>Vence em <strong><?php echo date('d/m/Y', strtotime($aluno['data_dva'])); ?></strong></p>
                        </div>
                    <?php else: ?>
                        <div class="status-box st-sem_dva">
                            <h2>SEM DVA REGISTRADA</h2>
                            <p>Este aluno não possui data de vencimento cadastrada.</p>
                        </div>
                    <?php endif; ?>

                    <div class="perfil-info-grid">
                        <div class="info-item">
                            <div class="info-label">Nome Completo</div>
                            <div class="info-valor"><?php echo e($aluno['nome_completo']); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Turma</div>
                            <div class="info-valor"><?php echo e($aluno['nome_turma'] ?? 'Sem turma'); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Data de Nascimento</div>
                            <div class="info-valor"><?php echo date('d/m/Y', strtotime($aluno['data_nascimento'])); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Vencimento DVA</div>
                            <div class="info-valor"><?php echo !empty($aluno['data_dva']) ? date('d/m/Y', strtotime($aluno['data_dva'])) : '-'; ?></div>
                        </div>

                        <div class="info-item wide">
                            <div class="info-label">Observações DVA</div>
                            <div class="info-valor textual"><?php echo nl2br(e($aluno['obs_dva'] ?? '-')); ?></div>
                        </div>
                    </div>

                    <div class="contatos-container">
                        <div class="contatos-titulo">Contatos Registrados</div>

                        <div class="contatos-grid">
                            <div class="contato-card">
                                <div class="contato-label">Aluno</div>
                                <div class="contato-numero">
                                    <?php echo !empty($aluno['telefone_aluno']) ? e($aluno['telefone_aluno']) : '<span class="contato-vazio">Não informado</span>'; ?>
                                </div>
                                <?php if (!empty($aluno['telefone_aluno'])): ?>
                                    <?php $num = preg_replace('/[^0-9]/', '', $aluno['telefone_aluno']); ?>
                                    <a href="https://wa.me/55<?php echo e($num); ?>" target="_blank" class="btn-whatsapp-full" rel="noopener noreferrer">Chamar Aluno</a>
                                <?php endif; ?>
                            </div>

                            <div class="contato-card">
                                <div class="contato-label">Responsável</div>
                                <div class="contato-numero">
                                    <?php echo !empty($aluno['telefone_responsavel']) ? e($aluno['telefone_responsavel']) : '<span class="contato-vazio">Não informado</span>'; ?>
                                </div>
                                <?php if (!empty($aluno['telefone_responsavel'])): ?>
                                    <?php $numResp = preg_replace('/[^0-9]/', '', $aluno['telefone_responsavel']); ?>
                                    <a href="https://wa.me/55<?php echo e($numResp); ?>" target="_blank" class="btn-whatsapp-full" rel="noopener noreferrer">Chamar Responsável</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="perfil-actions">
                        <a href="/aluno/editar/<?php echo (int)$aluno['id']; ?>" class="btn-primary" style="text-decoration:none;">✏️ Editar Dados / Renovar DVA</a>

                        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                            <form action="/aluno/excluir/<?php echo (int)$aluno['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja apagar este aluno permanentemente?');">
                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                <button type="submit" class="btn-secondary" style="background:var(--danger-color); text-decoration:none; border:0; cursor:pointer;">🗑️ Apagar Aluno</button>
                            </form>
                        <?php endif; ?>

                        <a href="/aluno" class="cancelar">Voltar para a Lista</a>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
