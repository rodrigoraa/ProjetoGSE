<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <style>
        .btn-acao { text-decoration: none; font-weight: bold; padding: 5px 10px; border-radius: 4px; font-size: 0.9em; }
        .btn-editar { color: #007bff; background: #e7f1ff; }
        .btn-editar:hover { background: #d0e2ff; }
        .btn-excluir { color: #dc3545; background: #ffeef0; border: 0; cursor: pointer; }
        .btn-excluir:hover { background: #ffdce0; }
        .inline-form { display: inline-block; margin: 0; }
    </style>
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Gerenciar Usuários</h1>
            </header>

            <main>
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <div style="margin-bottom:20px;">
                    <a href="/usuario/cadastrar" class="btn-primary" style="text-decoration:none;">+ Novo Usuário</a>
                </div>

                <div class="relatorio">
                    <table class="tabela-filtrada">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Tipo</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $dados = isset($funcionarios) ? $funcionarios : (isset($lista) ? $lista : []); ?>

                            <?php if (empty($dados)): ?>
                                <tr>
                                    <td colspan="4" style="text-align:center; padding:20px;">Nenhum usuário encontrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dados as $u): ?>
                                    <tr>
                                        <td><?php echo e($u['nome']); ?></td>
                                        <td><?php echo e($u['email']); ?></td>
                                        <td>
                                            <span style="padding:2px 8px; border-radius:10px; font-size:0.75em; font-weight:bold; background:<?php echo $u['tipo'] == 'admin' ? '#004a91' : '#28a745'; ?>; color:white;">
                                                <?php echo e(strtoupper($u['tipo'])); ?>
                                            </span>
                                        </td>
                                        <td class="col-acoes" style="text-align: center;">
                                            <a href="/usuario/editar/<?php echo (int)$u['id']; ?>" class="btn-acao btn-editar">✏️ Editar</a>

                                            <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                                <form action="/usuario/excluir/<?php echo (int)$u['id']; ?>" method="POST" class="inline-form" onsubmit="return confirm('Tem certeza que deseja apagar o usuário <?php echo e($u['nome']); ?>?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                    <button type="submit" class="btn-acao btn-excluir">🗑️ Apagar</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="font-size:0.8em; color:#999;">(Você)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
