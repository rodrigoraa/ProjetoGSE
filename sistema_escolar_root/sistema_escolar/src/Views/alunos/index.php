<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Alunos</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Gerenciar Alunos</h1>
            </header>

            <main>
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <section class="aluno-hero">
                    <div>
                        <h2>Central de cadastro e acompanhamento dos alunos</h2>
                        <p>Pesquise rapidamente por nome, revise a turma atual, abra o perfil completo e mantenha os dados de contato e DVA sempre atualizados.</p>
                    </div>
                    <div class="aluno-stats">
                        <div class="aluno-stat">
                            <strong><?php echo (int)$total_registros; ?></strong>
                            <span>Total de alunos</span>
                        </div>
                        <div class="aluno-stat">
                            <strong><?php echo (int)$pagina_atual; ?></strong>
                            <span>página atual</span>
                        </div>
                        <div class="aluno-stat">
                            <strong><?php echo (int)$total_paginas; ?></strong>
                            <span>total de páginas</span>
                        </div>
                    </div>
                </section>

                <div class="toolbar-alunos">
                    <div class="toolbar-copy">
                        <h3>Ações rápidas</h3>
                        <p>Cadastre novos alunos ou use a busca para localizar um registro específico.</p>
                    </div>

                    <a href="/aluno/cadastrar" class="btn-primary" style="text-decoration:none;">+ Novo Aluno</a>

                    <form action="/aluno" method="GET" class="form-busca">
                        <input type="search" name="busca" value="<?php echo e($termo); ?>" placeholder="Nome do aluno..." class="sistema" style="margin-bottom:0;">
                        <button type="submit" class="btn-secondary">Buscar</button>
                    </form>
                </div>

                <div class="relatorio">
                    <div class="section-head">
                        <div>
                            <h3>Alunos Cadastrados</h3>
                            <p>Abra o perfil para detalhes completos ou entre em edição para atualizar dados rapidamente.</p>
                        </div>
                        <span class="result-pill"><?php echo (int)$total_registros; ?> registros</span>
                    </div>

                    <table class="tabela-filtrada">
                        <thead>
                            <tr>
                                <th>Nome Completo</th>
                                <th>Turma</th>
                                <th>Data Nasc.</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lista_alunos)): ?>
                                <tr>
                                    <td colspan="4" class="table-empty">Nenhum aluno encontrado.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($lista_alunos as $aluno): ?>
                                <tr>
                                    <td class="nome-aluno">
                                        <a href="/aluno/perfil/<?php echo (int)$aluno['id']; ?>"><?php echo e($aluno['nome_completo']); ?></a>
                                    </td>
                                    <td><span class="turma-badge"><?php echo e($aluno['nome_turma'] ?? 'Sem turma'); ?></span></td>
                                    <td><?php echo date('d/m/Y', strtotime($aluno['data_nascimento'])); ?></td>
                                    <td class="col-acoes">
                                        <a href="/aluno/perfil/<?php echo (int)$aluno['id']; ?>" class="link-acao">👤 Perfil</a>
                                        <a href="/aluno/editar/<?php echo (int)$aluno['id']; ?>" class="link-acao">✏️ Editar</a>

                                        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                                            <form action="/aluno/excluir/<?php echo (int)$aluno['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza absoluta? Isso apagara o aluno e a DVA dele.');">
                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                <button type="submit" class="btn-inline-danger">🗑️ Apagar</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($total_paginas > 1): ?>
                        <div class="paginacao">
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <?php if ($i == $pagina_atual): ?>
                                    <span class="ativo"><?php echo (int)$i; ?></span>
                                <?php else: ?>
                                    <a href="/aluno?pagina=<?php echo (int)$i; ?>&busca=<?php echo urlencode($termo); ?>"><?php echo (int)$i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
