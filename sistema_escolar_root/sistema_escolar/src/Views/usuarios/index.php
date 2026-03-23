<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid transparent;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .btn-acao {
            text-decoration: none;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .btn-editar {
            color: #007bff;
            background: #e7f1ff;
        }

        .btn-editar:hover {
            background: #d0e2ff;
        }

        .btn-excluir {
            color: #dc3545;
            background: #ffeef0;
        }

        .btn-excluir:hover {
            background: #ffdce0;
        }
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
                <?php if (!empty($msg_sucesso)): ?>
                    <div class="alert alert-success">
                        ✅ <?php echo $msg_sucesso; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($msg_erro)): ?>
                    <div class="alert alert-danger">
                        ⚠️ <?php echo $msg_erro; ?>
                    </div>
                <?php endif; ?>
                <div style="margin-bottom:20px;">
                    <a href="/usuario/cadastrar" class="btn-primary" style="text-decoration:none;">+ Novo Usuário</a>
                </div>

                <div class="relatorio">
                    <table class="tabela-filtrada">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $dados = isset($funcionarios) ? $funcionarios : (isset($lista) ? $lista : []);
                            ?>

                            <?php if (empty($dados)): ?>
                                <tr>
                                    <td colspan="4" style="text-align:center; padding:20px;">Nenhum usuário encontrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dados as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <span style="padding:2px 8px; border-radius:10px; font-size:0.75em; font-weight:bold; background:<?php echo $u['tipo'] == 'admin' ? '#004a91' : '#28a745'; ?>; color:white;">
                                                <?php echo strtoupper($u['tipo']); ?>
                                            </span>
                                        </td>
                                        <td class="col-acoes" style="text-align: center;">
                                            <a href="/usuario/editar/<?php echo $u['id']; ?>" class="btn-acao btn-editar">
                                                ✏️ Editar
                                            </a>

                                            <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                                <a href="/usuario/excluir/<?php echo $u['id']; ?>"
                                                    class="btn-acao btn-excluir"
                                                    onclick="return confirm('Tem certeza que deseja apagar o usuário <?php echo $u['nome']; ?>?');">
                                                    🗑️ Apagar
                                                </a>
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