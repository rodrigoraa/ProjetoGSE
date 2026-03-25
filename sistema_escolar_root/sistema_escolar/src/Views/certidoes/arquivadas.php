<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Arquivo de Certidoes</title>
    <link rel="stylesheet" href="/assets/css/painel.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/certidoes.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header style="margin-bottom: 20px;">
                <h1 style="color: #1e293b; font-size: 1.8rem;"><i class="fa-solid fa-folder-open" style="color: #64748b; margin-right: 10px;"></i> Certidoes Arquivadas</h1>
            </header>

            <main>
                <div class="toolbar-arquivo">
                    <form action="/certidao/arquivadas" method="GET" class="form-filtro-ano">
                        <div class="filtro-box" style="padding: 6px 12px; border-radius: 8px;">
                            <label for="ano" style="margin-bottom: 0;"><i class="fa-regular fa-calendar-days"></i> Ano do Historico:</label>
                            <select name="ano" id="ano" onchange="this.form.submit()">
                                <option value="todos" <?php echo ($ano_filtro === 'todos') ? 'selected' : ''; ?>>Todos os Anos</option>
                                <?php foreach ($anos_disponiveis as $ano): ?>
                                    <option value="<?php echo e($ano); ?>" <?php echo ($ano == $ano_filtro) ? 'selected' : ''; ?>><?php echo e($ano); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <a href="/certidao" class="cancelar" style="margin-left: auto; background: white; border: 1px solid var(--border-color); padding: 8px 15px; border-radius: 6px;"><i class="fa-solid fa-arrow-left"></i> Voltar para Vigentes</a>
                </div>

                <div class="grid-container">
                    <table class="tabela-arquivo">
                        <thead>
                            <tr>
                                <th>Fornecedor</th>
                                <th>Tipo de Certidao</th>
                                <th>Emissao</th>
                                <th>Vencimento</th>
                                <th style="text-align:center;">Documento</th>
                                <th style="text-align:right;">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($certidoes)): ?>
                                <tr>
                                    <td colspan="6" class="empty-state" style="padding: 50px 20px;">Nenhuma certidao arquivada encontrada para o ano de <?php echo e($ano_filtro); ?>.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($certidoes as $reg): ?>
                                <tr>
                                    <td style="font-weight: 700; color: var(--primary-color);"><?php echo e($reg['fornecedor']); ?></td>
                                    <td style="font-weight: 600; color: #334155;"><?php echo e($reg['tipo_certidao']); ?></td>
                                    <td><span class="data-badge"><?php echo date('d/m/Y', strtotime($reg['data_emissao'])); ?></span></td>
                                    <td><span class="data-badge"><?php echo date('d/m/Y', strtotime($reg['data_vencimento'])); ?></span></td>
                                    <td style="text-align:center;">
                                        <?php if (!empty($reg['arquivo_pdf'])): ?>
                                            <a href="/uploads/certidoes/<?php echo rawurlencode($reg['arquivo_pdf']); ?>" target="_blank" class="pdf-btn" title="Baixar/Ver PDF"><i class="fa-solid fa-file-pdf"></i> Ver PDF</a>
                                        <?php else: ?>
                                            <span class="pdf-btn disabled" title="Sem anexo"><i class="fa-solid fa-file-pdf"></i> S/ Anexo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="cert-actions" style="border: none; padding: 0; margin: 0; justify-content: flex-end; gap: 8px;">
                                            <form action="/certidao/desarquivar/<?php echo (int)$reg['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Deseja desarquivar esta certidao e envia-la para a tela inicial?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                <button type="submit" class="action-btn text-success" title="Desarquivar" style="border:0; cursor:pointer;"><i class="fa-solid fa-rotate-left"></i></button>
                                            </form>

                                            <a href="/certidao/editar/<?php echo (int)$reg['id']; ?>" class="action-btn text-primary" title="Editar informacoes"><i class="fa-solid fa-pen"></i></a>

                                            <?php if ($_SESSION['usuario_tipo'] == 'admin'): ?>
                                                <form action="/certidao/excluir/<?php echo (int)$reg['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Deseja excluir esta certidao permanentemente do historico?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                    <input type="hidden" name="origem" value="arquivo">
                                                    <input type="hidden" name="ano" value="<?php echo e($ano_filtro); ?>">
                                                    <button type="submit" class="action-btn text-danger" title="Apagar Definitivamente" style="border:0; cursor:pointer;"><i class="fa-solid fa-trash-can"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
