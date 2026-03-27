<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Aluno</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css">
</head>

<body>

    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Editar Aluno</h1>
            </header>

            <main>
                <?php echo $mensagem; ?>

                <section class="page-intro">
                    <h2>Atualize dados pessoais, contatos e vencimento da DVA</h2>
                    <p>Revise os campos abaixo com atenção. Essa tela foi organizada para facilitar a manutenção do cadastro sem perder o contexto acadêmico e documental do aluno.</p>
                </section>

                <div class="form-container">
                    <form action="/aluno/editar/<?php echo (int)$aluno['id']; ?>" method="POST" class="sistema">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                        <h3 class="form-section-title">Dados Pessoais</h3>
                        <p class="section-subtitle">Campos principais do cadastro escolar e da vinculação com a turma.</p>

                        <div style="margin-bottom: 20px;">
                            <label>Nome Completo:</label>
                            <input type="text" name="nome" value="<?php echo htmlspecialchars($aluno['nome_completo']); ?>" required>
                        </div>

                        <div class="grid-3-col">
                            <div>
                                <label>Data de Nascimento:</label>
                                <input type="date" name="data_nascimento" value="<?php echo e($aluno['data_nascimento']); ?>" required>
                            </div>

                            <div>
                                <label>Turma:</label>
                                <select name="id_turma">
                                    <option value="">Sem turma</option>
                                    <?php foreach ($turmas as $t): ?>
                                        <option value="<?php echo (int)$t['id']; ?>" <?php echo selected($aluno['id_turma'], $t['id']); ?>>
                                            <?php echo htmlspecialchars($t['nome_turma']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="box-dva">
                                <label>Vencimento DVA:</label>
                                <input type="date" name="data_dva" value="<?php echo htmlspecialchars($aluno['data_dva'] ?? ''); ?>">
                            </div>
                        </div>

                        <h3 class="form-section-title">Contatos</h3>
                        <p class="section-subtitle">Informações úteis para comunicação rápida com o aluno e responsável.</p>

                        <div class="grid-2-col">
                            <div>
                                <label>WhatsApp / Celular do Aluno:</label>
                                <input type="text" name="telefone_aluno" value="<?php echo htmlspecialchars($aluno['telefone_aluno'] ?? ''); ?>" placeholder="(00) 00000-0000">
                            </div>

                            <div>
                                <label>Telefone do Responsável:</label>
                                <input type="text" name="telefone_responsavel" value="<?php echo htmlspecialchars($aluno['telefone_responsavel'] ?? ''); ?>" placeholder="(00) 00000-0000">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Salvar Alterações</button>
                            <a href="/aluno" class="cancelar">Cancelar</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
