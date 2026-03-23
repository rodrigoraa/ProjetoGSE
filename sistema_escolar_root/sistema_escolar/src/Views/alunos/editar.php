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

                <div class="form-container">
                    <form action="/aluno/editar/<?php echo $aluno['id']; ?>" method="POST" class="sistema">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                        <h3 class="form-section-title" style="margin-bottom: 15px; color: #666; font-size: 1.1em; border-bottom: 1px solid #eee; padding-bottom: 5px;">Dados Pessoais</h3>

                        <div style="margin-bottom: 20px;">
                            <label>Nome Completo:</label>
                            <input type="text" name="nome" value="<?php echo htmlspecialchars($aluno['nome_completo']); ?>" required>
                        </div>

                        <div class="grid-3-col">
                            <div>
                                <label>Data de Nascimento:</label>
                                <input type="date" name="data_nascimento" value="<?php echo $aluno['data_nascimento']; ?>" required>
                            </div>

                            <div>
                                <label>Turma:</label>
                                <select name="id_turma">
                                    <option value="">Sem turma</option>
                                    <?php foreach ($turmas as $t): ?>
                                        <option value="<?php echo $t['id']; ?>" <?php echo selected($aluno['id_turma'], $t['id']); ?>>
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

                        <h3 class="form-section-title" style="margin-top: 30px; margin-bottom: 15px; color: #666; font-size: 1.1em; border-bottom: 1px solid #eee; padding-bottom: 5px;">Contatos</h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label>📱 WhatsApp / Celular do Aluno:</label>
                                <input type="text" name="telefone_aluno" value="<?php echo htmlspecialchars($aluno['telefone_aluno'] ?? ''); ?>" placeholder="(00) 00000-0000">
                            </div>

                            <div>
                                <label>👨‍👩‍👦 Telefone do Responsável:</label>
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