<?php
$erro = $erro ?? null;
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/assets/image/logo_escola.png">
    <title>Login - Sistema Escolar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/assets/css/login.css">
</head>

<body class="login-container">

    <form action="/login/entrar" method="POST" class="login">
        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

        <h2>Login</h2>

        <?php if ($erro): ?>
            <p class="error-message"><?php echo htmlspecialchars($erro); ?></p>
        <?php endif; ?>

        <div class="form-group">
            <label for="email">E-mail:</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                placeholder="seu@email.com">
        </div>

        <div class="password-wrapper">
            <label for="senha">Senha:</label>

            <div class="input-with-toggle">
                <input
                    type="password"
                    id="senha"
                    name="senha"
                    required
                    placeholder="Sua senha">

                <button
                    type="button"
                    id="toggle-senha"
                    class="toggle-password"
                    aria-pressed="false"
                    title="Mostrar senha">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"
                            stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="12" cy="12" r="3"
                            stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-login">Entrar</button>

        <div style="margin-top: 15px; text-align: center;">
            <a href="/recuperar" style="color: #666; text-decoration: none; font-size: 0.9em;">Esqueci minha senha</a>
        </div>
    </form>

    <script>
        const toggleBtn = document.getElementById('toggle-senha');
        const senhaInput = document.getElementById('senha');

        const icons = {
            show: `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"
                        stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="12" r="3"
                        stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            `,
            hide: `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.6 21.6 0 0 1 5.76-5.94"
                        stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M1 1l22 22"
                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            `
        };

        toggleBtn.addEventListener("click", () => {
            const isPassword = senhaInput.type === "password";

            senhaInput.type = isPassword ? "text" : "password";
            toggleBtn.innerHTML = isPassword ? icons.hide : icons.show;
            toggleBtn.setAttribute("aria-pressed", isPassword);
            toggleBtn.title = isPassword ? "Ocultar senha" : "Mostrar senha";
        });
    </script>

</body>

</html>
