<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Alunos Sem DVA</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css">
</head>

<body>

    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>⚠️ Alunos com DVA Pendente</h1>
            </header>

            <main>
                <div class="relatorio" style="border-left: 5px solid #f57c00;">
                    <h3 style="margin-top:0; color:#f57c00;">Lista de Pendências (<?php echo count($lista_alunos); ?>)</h3>

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
                                    <td colspan="3" style="padding:20px; text-align:center; color:green;">
                                        ✅ Parabéns! Todos os alunos possuem DVA.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($lista_alunos as $aluno): ?>
                                <tr>
                                    <td class="nome-aluno">
                                        <a href="/aluno/perfil/<?php echo $aluno['id']; ?>">
                                            <?php echo htmlspecialchars($aluno['nome_completo']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($aluno['nome_turma'] ?? 'Sem turma'); ?></td>
                                    <td>
                                        <a href="/aluno/editar/<?php echo $aluno['id']; ?>" class="btn-primary" style="padding:5px 10px; font-size:0.9em;">
                                            📝 Regularizar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:20px;">
                    <a href="/painel" class="cancelar">Voltar ao Painel</a>
                </div>
            </main>
        </div>
    </div>
</body>

</html>