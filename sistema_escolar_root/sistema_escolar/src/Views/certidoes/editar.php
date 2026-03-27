<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Certidão</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/certidoes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header class="cert-page-header">
                <h1><i class="fa-solid fa-pen-to-square"></i> Editar Certidão</h1>
            </header>
            <main>
                <?php if (!empty($mensagem)) echo $mensagem; ?>

                <form action="/certidao/editar/<?php echo (int)$certidao['id']; ?>" method="POST" enctype="multipart/form-data" class="form-certidao">

                    <?php if (!empty($certidao['arquivo_pdf'])): ?>
                        <div class="box-pdf-existente">
                            <span><i class="fa-solid fa-paperclip" style="margin-right: 8px;"></i> Existe um arquivo PDF em anexo.</span>
                            <a href="/uploads/certidoes/<?php echo rawurlencode($certidao['arquivo_pdf']); ?>" target="_blank" class="link-ver-pdf">
                                <i class="fa-solid fa-eye"></i> Visualizar PDF
                            </a>
                        </div>
                    <?php endif; ?>

                    <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                    <div class="grid-2-col">
                        <div class="form-group">
                            <label>Fornecedor:</label>
                            <select name="fornecedor" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($fornecedores as $f): ?>
                                    <option value="<?php echo (int)$f['id']; ?>" <?php echo ($f['id'] == $certidao['id_fornecedor']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($f['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tipo de Certidão:</label>
                            <select name="tipo_certidao" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($tipos as $t): ?>
                                    <option value="<?php echo (int)$t['id']; ?>" <?php echo ($t['id'] == $certidao['id_tipo_certidao']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($t['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid-3-col">
                        <div class="form-group compact">
                            <label>Emissão:</label>
                            <input type="date" name="data_emissao" id="emissao" value="<?php echo e($certidao['data_emissao']); ?>" required>
                        </div>
                        <div class="form-group compact">
                            <label>Vencimento:</label>
                            <input type="date" name="data_vencimento" id="vencimento" value="<?php echo e($certidao['data_vencimento']); ?>" required>
                        </div>
                        <div class="form-group compact">
                            <label>Validade (Dias):</label>
                            <input type="text" id="dias_calculados" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fa-solid fa-file-arrow-up inline-icon"></i> Substituir PDF (Opcional):</label>
                        <input type="file" name="arquivo_pdf" accept="application/pdf">
                    </div>

                    <div class="form-group compact">
                        <label>Observação:</label>
                        <textarea name="observacao" rows="2" placeholder="Notas adicionais..."><?php echo htmlspecialchars($certidao['observacao']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-novo"><i class="fa-solid fa-floppy-disk"></i> Atualizar Dados</button>
                        <a href="/certidao" class="cancelar"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script>
        const inpEmissao = document.getElementById('emissao');
        const inpVencimento = document.getElementById('vencimento');
        const inpDias = document.getElementById('dias_calculados');

        function calcularDiferenca() {
            if (inpEmissao.value && inpVencimento.value) {
                const dt1 = new Date(inpEmissao.value);
                const dt2 = new Date(inpVencimento.value);
                const diffTime = dt2 - dt1;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                if (!isNaN(diffDays)) {
                    if (diffDays <= 0) {
                        inpDias.value = 'Inválido';
                        inpDias.style.color = 'var(--danger-color)';
                    } else {
                        inpDias.value = diffDays + ' dias';
                        inpDias.style.color = 'var(--primary-color)';
                    }
                }
            } else {
                inpDias.value = '';
            }
        }

        inpEmissao.addEventListener('change', calcularDiferenca);
        inpVencimento.addEventListener('change', calcularDiferenca);
        window.onload = calcularDiferenca;
    </script>
</body>

</html>
