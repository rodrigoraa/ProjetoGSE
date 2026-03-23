<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Aluno</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Cadastrar Novo Aluno</h1>
            </header>

            <main>
                <?php if (!empty($mensagem)) echo $mensagem; ?>

                <div class="form-container">
                    <form action="/aluno/cadastrar" method="POST" class="sistema">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                        <h3 class="form-section-title">Dados Pessoais</h3>

                        <div style="margin-bottom: 20px;">
                            <label>Nome Completo:</label>
                            <input type="text" name="nome" value="<?php echo htmlspecialchars($d['nome'] ?? ''); ?>" required placeholder="Ex: Maria da Silva" autofocus>
                        </div>

                        <div class="grid-3-col">
                            <div>
                                <label>Data de Nascimento:</label>
                                <input type="date" name="data_nascimento" value="<?php echo htmlspecialchars($d['data_nascimento'] ?? ''); ?>" required>
                            </div>

                            <div>
                                <label>Turma:</label>
                                <select name="id_turma">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($turmas as $t): ?>
                                        <option value="<?php echo $t['id']; ?>" <?php echo selected($d['id_turma'] ?? '', $t['id']); ?>>
                                            <?php echo htmlspecialchars($t['nome_turma']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="box-dva">
                                <label>📅 Vencimento DVA:</label>
                                <input type="date" name="data_dva" value="<?php echo htmlspecialchars($d['data_dva'] ?? ''); ?>">
                            </div>
                        </div>

                        <h3 class="form-section-title" style="margin-top: 30px;">Contatos</h3>

                        <div class="grid-2-col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label>📱 WhatsApp / Celular do Aluno:</label>
                                <input type="text" name="telefone_aluno" value="<?php echo htmlspecialchars($d['telefone_aluno'] ?? ''); ?>" placeholder="(00) 00000-0000">
                            </div>

                            <div>
                                <label>👨‍👩‍👦 Telefone do Responsável:</label>
                                <input type="text" name="telefone_responsavel" value="<?php echo htmlspecialchars($d['telefone_responsavel'] ?? ''); ?>" placeholder="(00) 00000-0000">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Salvar Cadastro</button>
                            <a href="/aluno" class="cancelar">Cancelar e Voltar</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>