<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nova Senha</title>
    <link rel="stylesheet" href="/assets/css/login.css">
</head>
<body class="login-container">
    <form action="/recuperar/salvar" method="POST" class="login">
        <h2>Criar Nova Senha</h2>
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        
        <p style="text-align:center; margin-bottom:20px;">Defina sua nova senha para: <br><strong><?php echo $email; ?></strong></p>

        <div class="form-group">
            <label>Nova Senha:</label>
            <input type="password" name="senha" required minlength="6" placeholder="Mínimo 6 caracteres">
        </div>
        
        <button type="submit">Alterar Senha</button>
    </form>
</body>
</html>