<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Dados Gerais do Pedido</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/alunos.css'); ?>">
    <link rel="stylesheet" href="/assets/css/contrato.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/contrato.css'); ?>">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>✏️ Editar Dados Gerais #<?php echo $contrato['id']; ?></h1>
            </header>

            <main>
                <div class="toolbar-alunos">
                    <div class="toolbar-actions">
                        <a href="/contrato/ver/<?php echo $contrato['id']; ?>" class="btn-secondary page-back-link">Voltar</a>
                    </div>
                </div>

                <?php if (!empty($erro)): ?>
                    <div class="alert-box alert-erro" style="max-width: 600px; margin-bottom: 20px;">
                        <div class="alert-title">Nao foi possivel salvar</div>
                        <div class="alert-text"><?php echo e($erro); ?></div>
                    </div>
                <?php endif; ?>

                <form action="/contrato/editar/<?php echo $contrato['id']; ?>" method="POST" class="form-aluno" style="max-width: 600px;">
                    <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                    <div class="form-group">
                        <label>Título do Pedido</label>
                        <input type="text" name="titulo" class="sistema" value="<?php echo htmlspecialchars($contrato['titulo']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Valor total do Pedido (R$)</label>
                        <input type="number" name="valor_total" id="valor_total" class="sistema" step="0.01" value="<?php echo $contrato['valor_total']; ?>" required>
                        <small style="color: #666; margin-top: 5px; display: block;">
                            Nota: Alterar este valor não afeta os produtos ou a quantidade de notas criadas, apenas atualiza o limite financeiro disponível no saldo geral.
                        </small>
                    </div>

                    <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="btn-primary full-width-submit">Salvar Alterações</button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>

</html>
