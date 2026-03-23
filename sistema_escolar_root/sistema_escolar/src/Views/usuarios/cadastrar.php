<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Novo Usuário</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/usuarios.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>
        <div class="main-content-wrapper">
            <header>
                <h1>Novo Usuário</h1>
            </header>
            <main>
                <?php if (!empty($mensagem))
                    echo $mensagem; ?>
                <form method="POST" class="sistema">
                    <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                    <div><label>Nome:</label><input type="text" name="nome" required></div>
                    <div><label>E-mail:</label><input type=\"email\" name="email" required></div>
                    <div><label>Senha:</label><input type="password" name="senha" required minlength="6"></div>

                    <div><label>Tipo:</label>
                        <select name="tipo" class="sistema">
                            <option value="funcionario">Funcionário</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div style="margin-top:20px;">
                        <button type="submit">Salvar</button> <a href="/usuario" class="cancelar">Cancelar</a>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>

</html>