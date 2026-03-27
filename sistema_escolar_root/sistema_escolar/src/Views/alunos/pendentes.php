<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Alunos Sem DVA</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/alunos.css'); ?>">
</head>

<body>

    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Alunos com DVA Pendente</h1>
            </header>

            <main>
                <section class="aluno-hero">
                    <div>
                        <h2>Regularização de vencimentos e cadastros incompletos</h2>
                        <p>Esta lista concentra os alunos que ainda não possuem DVA registrada para facilitar a atualização dos perfis e reduzir pendências operacionais.</p>
                    </div>
                    <div class="aluno-stats">
                        <div class="aluno-stat">
                            <strong><?php echo count($lista_alunos); ?></strong>
                            <span>pendências atuais</span>
                        </div>
                    </div>
                </section>

                <div class="relatorio pendencia-shell">
                    <div class="section-head">
                        <div>
                            <h3>Lista de Pendências</h3>
                            <p>Abra o perfil ou entre direto na edição para regularizar a informação faltante.</p>
                        </div>
                        <span class="result-pill"><?php echo count($lista_alunos); ?> itens</span>
                    </div>

                    <table class="tabela-filtrada">
                        <thead>
                            <tr>
                                <th>Nome do Aluno</th>
                                <th>Turma</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lista_alunos)): ?>
                                <tr>
                                    <td colspan="3" class="pendencia-ok">
                                        Parabéns! Todos os alunos possuem DVA.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($lista_alunos as $aluno): ?>
                                <tr>
                                    <td class="nome-aluno">
                                        <a href="/aluno/perfil/<?php echo (int)$aluno['id']; ?>">
                                            <?php echo htmlspecialchars($aluno['nome_completo']); ?>
                                        </a>
                                    </td>
                                    <td><span class="turma-badge"><?php echo htmlspecialchars($aluno['nome_turma'] ?? 'Sem turma'); ?></span></td>
                                    <td>
                                        <a href="/aluno/editar/<?php echo (int)$aluno['id']; ?>" class="btn-primary" style="padding:5px 10px; font-size:0.9em;">
                                            Regularizar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pendencia-actions">
                    <a href="/painel" class="cancelar">Voltar ao Painel</a>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
