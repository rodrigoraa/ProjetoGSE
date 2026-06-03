<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/assets/image/logo_escola.png">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="/assets/css/login.css">
</head>
<body class="login-container">
    <form action="/recuperar/enviar" method="POST" class="login">
        <h2>Recuperar Senha</h2>
        <?php if (!empty($mensagem)): ?>
            <p style="margin-bottom:15px"><?php echo e($mensagem); ?></p>
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

        <div class="form-group">
            <label>Digite seu e-mail cadastrado:</label>
            <input type="email" name="email" required placeholder="email@exemplo.com">
        </div>

        <button type="submit">Enviar Link</button>
        <div style="text-align:center; margin-top:15px;">
            <a href="/login" style="color:#004a91; text-decoration:none;">Voltar para o Login</a>
        </div>
    </form>
</body>
</html>
