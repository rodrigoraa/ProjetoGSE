<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/usuarios.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>✏️ Editar Usuário</h1>
            </header>
            <main>
                <?php if (!empty($mensagem))
                    echo $mensagem; ?>

                <div class="relatorio" style="max-width: 800px; margin: 0 auto;">

                    <form action="/usuario/editar/<?php echo $user['id']; ?>" method="POST" class="sistema">

                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                        <h3
                            style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; color: var(--primary-color);">
                            Dados da Conta
                        </h3>

                        <div style="margin-bottom: 20px;">
                            <label>Nome Completo:</label>
                            <input type="text" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>"
                                required>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label>E-mail de Acesso:</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                required>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

                            <div>
                                <label>Tipo de Permissão:</label>
                                <select name="tipo" class="sistema" required>
                                    <option value="funcionario" <?php echo ($user['tipo'] == 'funcionario') ? 'selected' : ''; ?>>Funcionário</option>
                                    <option value="admin" <?php echo ($user['tipo'] == 'admin') ? 'selected' : ''; ?>>
                                        Administrador</option>
                                </select>
                            </div>

                            <div
                                style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px dashed #ccc;">
                                <label style="margin-bottom: 5px; display: block; color: #666;">Redefinir Senha
                                    (Opcional):</label>
                                <input type="password" name="senha" placeholder="Deixe em branco para manter a atual"
                                    minlength="6" style="margin-bottom: 0;">
                            </div>
                        </div>

                        <label class="usuario-check-option">
                            <input type="checkbox" name="recebe_avisos_email" value="1" <?php echo !empty($user['recebe_avisos_email']) ? 'checked' : ''; ?>>
                            <span>
                                <strong>Receber avisos por e-mail</strong>
                                <small>Inclui alertas automáticos de vencimento de DVA e certidões.</small>
                            </span>
                        </label>

                        <div
                            style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; display: flex; align-items: center;">
                            <button type="submit" class="btn-primary">Salvar Alterações</button>
                            <a href="/usuario" class="cancelar">Cancelar</a>
                        </div>

                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
