<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>👤 Meu Perfil</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/usuarios.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>👤 Meu Perfil</h1>
            </header>

            <main>
                <?php if (!empty($mensagem)) echo $mensagem; ?>

                <div class="relatorio" style="max-width: 800px; margin: 0 auto;">
                    <form method="POST" class="sistema">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                        <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; color: var(--primary-color);">
                            Dados Pessoais
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label>Nome:</label>
                                <input type="text" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" required>
                            </div>
                            <div>
                                <label>E-mail:</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>

                        <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; margin-top: 20px; color: var(--danger-color);">
                            Segurança
                        </h3>

                        <div style="background-color: #fff5f5; padding: 20px; border-radius: 8px; border: 1px solid #ffc9c9; margin-bottom: 20px;">
                            <p style="margin-top: 0; color: #c92a2a; font-size: 0.9rem; font-weight: bold;">
                                🔄 Alterar Senha (Opcional)
                            </p>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <label>Nova Senha:</label>
                                    <input type="password" name="nova_senha" placeholder="Mínimo de 6 caracteres" style="margin-bottom: 0;">
                                </div>
                                <div>
                                    <label>Confirmar Nova Senha:</label>
                                    <input type="password" name="confirma_senha" placeholder="Repita a nova senha" style="margin-bottom: 0;">
                                </div>
                            </div>
                        </div>

                        <div style="background-color: #e8f4ff; padding: 20px; border-radius: 8px; border: 1px solid #b8daff; margin-top: 30px;">
                            <label style="color: #004a91; margin-bottom: 10px; display: block;">
                                🔒 Para salvar qualquer alteração, digite sua <strong>SENHA ATUAL</strong>:
                            </label>
                            <input type="password" name="senha_atual" required placeholder="Sua senha usada para entrar no sistema" style="margin-bottom: 0;">
                        </div>

                        <div style="margin-top: 30px; text-align: right;">
                            <button type="submit" class="btn-primary">👤 Atualizar Perfil</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
