<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuarios</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <style>
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; border: 1px solid transparent; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
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
                <h1>Gerenciar Usuarios</h1>
            </header>

            <main>
                <?php if (!empty($msg_sucesso)): ?>
                    <div class="alert alert-success"><?php echo e($msg_sucesso); ?></div>
                <?php endif; ?>

                <?php if (!empty($msg_erro)): ?>
                    <div class="alert alert-danger"><?php echo e($msg_erro); ?></div>
                <?php endif; ?>

                <div style="margin-bottom:20px;">
                    <a href="/usuario/cadastrar" class="btn-primary" style="text-decoration:none;">+ Novo Usuario</a>
                </div>

                <div class="relatorio">
                    <table class="tabela-filtrada">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th style="text-align: center;">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $dados = isset($funcionarios) ? $funcionarios : (isset($lista) ? $lista : []); ?>

                            <?php if (empty($dados)): ?>
                                <tr>
                                    <td colspan="4" style="text-align:center; padding:20px;">Nenhum usuario encontrado.</td>
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
                                            <a href="/usuario/editar/<?php echo (int)$u['id']; ?>" class="btn-acao btn-editar">Editar</a>

                                            <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                                <form action="/usuario/excluir/<?php echo (int)$u['id']; ?>" method="POST" class="inline-form" onsubmit="return confirm('Tem certeza que deseja apagar o usuario <?php echo e($u['nome']); ?>?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                    <button type="submit" class="btn-acao btn-excluir">Apagar</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="font-size:0.8em; color:#999;">(Voce)</span>
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
