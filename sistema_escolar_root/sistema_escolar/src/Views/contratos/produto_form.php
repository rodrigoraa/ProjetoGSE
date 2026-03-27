<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title><?php echo $acao === 'editar' ? 'Editar Produto' : 'Novo Produto'; ?></title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/alunos.css'); ?>">
    <link rel="stylesheet" href="/assets/css/contrato.css?v=<?php echo filemtime(ROOT_PATH . '/public/assets/css/contrato.css'); ?>">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>
        <div class="main-content-wrapper">
            <header>
                <h1><?php echo $acao === 'editar' ? 'Editar Produto' : 'Adicionar Novo Produto'; ?></h1>
            </header>
            <main>
                <div class="toolbar-alunos">
                    <?php $link_voltar = $acao === 'editar' ? $produto['id_contrato'] : $id_contrato; ?>
                    <a href="/contrato/ver/<?php echo (int)$link_voltar; ?>" class="btn-secondary" style="text-decoration:none;">Voltar</a>
                </div>

                <?php if (!empty($erro)): ?>
                    <div class="alert-box alert-erro" style="max-width: 600px; margin-bottom: 20px;">
                        <div class="alert-title">Nao foi possivel salvar</div>
                        <div class="alert-text"><?php echo e($erro); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-aluno" style="max-width: 600px;">
                    <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                    <?php if (isset($_GET['folha'])): ?>
                        <input type="hidden" name="numero_folha" value="<?php echo (int)$_GET['folha']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Nome do Produto</label>
                        <input type="text" name="nome_produto" class="sistema" required value="<?php echo $acao === 'editar' ? e($produto['nome_produto']) : ''; ?>" placeholder="Ex: Papel A4">
                    </div>

                    <div class="form-group">
                        <label>Marca / Fabricante</label>
                        <input type="text" name="marca" class="sistema" value="<?php echo $acao === 'editar' ? e($produto['marca'] ?? '') : ''; ?>" placeholder="Ex: Chamex, HP, Bic...">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Unidade</label>
                            <select name="unidade" class="sistema" required>
                                <?php $unid_atual = ($acao === 'editar') ? ($produto['unidade'] ?? 'UN') : 'UN'; ?>
                                <option value="KG" <?php echo ($unid_atual == 'KG') ? 'selected' : ''; ?>>KG</option>
                                <option value="L" <?php echo ($unid_atual == 'L') ? 'selected' : ''; ?>>L</option>
                                <option value="UN" <?php echo ($unid_atual == 'UN') ? 'selected' : ''; ?>>UN</option>
                                <option value="PCT" <?php echo ($unid_atual == 'PCT') ? 'selected' : ''; ?>>PCT</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Quantidade</label>
                            <input type="number" name="quantidade" class="sistema" min="1" required value="<?php echo $acao === 'editar' ? (int)$produto['quantidade'] : '1'; ?>">
                        </div>

                        <div class="form-group">
                            <label>Valor Unitário (R$)</label>
                            <input type="number" name="valor_unitario" class="sistema" step="0.01" required value="<?php echo $acao === 'editar' ? e($produto['valor_unitario']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="btn-primary" style="width: 100%;">Salvar Informações do Produto</button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>

</html>
