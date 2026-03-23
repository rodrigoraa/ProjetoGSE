<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Arquivo de Certidões</title>
    <link rel="stylesheet" href="/assets/css/painel.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/certidoes.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Ajustes Premium exclusivos para a Tabela de Arquivos */
        .grid-container {
            background: white;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        .tabela-arquivo th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            background-color: #f8fafc;
            padding: 15px 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .tabela-arquivo td {
            padding: 15px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-main);
            font-size: 0.9rem;
        }

        .tabela-arquivo tbody tr:hover {
            background-color: #f8fafc;
        }

        .tabela-arquivo tr:last-child td {
            border-bottom: none;
        }

        /* Crachás elegantes para as datas */
        .data-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #e2e8f0;
        }

        /* Botão bonito para o PDF */
        .pdf-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #e0f2fe;
            color: #0284c7;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .pdf-btn:hover {
            background: #bae6fd;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(14, 165, 233, 0.1);
        }

        .pdf-btn.disabled {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
    </style>
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header style="margin-bottom: 20px;">
                <h1 style="color: #1e293b; font-size: 1.8rem;">
                    <i class="fa-solid fa-folder-open" style="color: #64748b; margin-right: 10px;"></i> Certidões Arquivadas
                </h1>
            </header>

            <main>
                <div class="toolbar-arquivo">
                    <form action="/certidao/arquivadas" method="GET" class="form-filtro-ano">
                        <div class="filtro-box" style="padding: 6px 12px; border-radius: 8px;">
                            <label for="ano" style="margin-bottom: 0;"><i class="fa-regular fa-calendar-days"></i> Ano do Histórico:</label>
                            <select name="ano" id="ano" onchange="this.form.submit()">
                                <option value="todos" <?php echo ($ano_filtro === 'todos') ? 'selected' : ''; ?>>Todos os Anos</option>

                                <?php foreach ($anos_disponiveis as $ano): ?>
                                    <option value="<?php echo $ano; ?>" <?php echo ($ano == $ano_filtro) ? 'selected' : ''; ?>>
                                        <?php echo $ano; ?>
                                    </option>
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
                                <th>Tipo de Certidão</th>
                                <th>Emissão</th>
                                <th>Vencimento</th>
                                <th style="text-align:center;">Documento</th>
                                <th style="text-align:right;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($certidoes)): ?>
                                <tr>
                                    <td colspan="6" class="empty-state" style="padding: 50px 20px;">
                                        <i class="fa-solid fa-folder-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                                        Nenhuma certidão arquivada encontrada para o ano de <?php echo $ano_filtro; ?>.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($certidoes as $reg): ?>
                                <tr>
                                    <td style="font-weight: 700; color: var(--primary-color);">
                                        <i class="fa-regular fa-building" style="color: #94a3b8; margin-right: 6px;"></i>
                                        <?php echo htmlspecialchars($reg['fornecedor']); ?>
                                    </td>

                                    <td style="font-weight: 600; color: #334155;">
                                        <?php echo htmlspecialchars($reg['tipo_certidao']); ?>
                                    </td>

                                    <td>
                                        <span class="data-badge">
                                            <i class="fa-regular fa-calendar-plus" style="color: #94a3b8;"></i>
                                            <?php echo date('d/m/Y', strtotime($reg['data_emissao'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="data-badge">
                                            <i class="fa-regular fa-calendar-xmark" style="color: #94a3b8;"></i>
                                            <?php echo date('d/m/Y', strtotime($reg['data_vencimento'])); ?>
                                        </span>
                                    </td>

                                    <td style="text-align:center;">
                                        <?php if (!empty($reg['arquivo_pdf'])): ?>
                                            <a href="/uploads/certidoes/<?php echo $reg['arquivo_pdf']; ?>" target="_blank" class="pdf-btn" title="Baixar/Ver PDF">
                                                <i class="fa-solid fa-file-pdf"></i> Ver PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="pdf-btn disabled" title="Sem anexo">
                                                <i class="fa-solid fa-file-pdf"></i> S/ Anexo
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td style="text-align: right;">
                                        <div class="cert-actions" style="border: none; padding: 0; margin: 0; justify-content: flex-end; gap: 8px;">

                                            <a href="/certidao/desarquivar/<?php echo $reg['id']; ?>" class="action-btn text-success" title="Desarquivar (Voltar a ficar vigente)" onclick="return confirm('Deseja desarquivar esta certidão e enviá-la para a tela inicial?');">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </a>

                                            <a href="/certidao/editar/<?php echo $reg['id']; ?>" class="action-btn text-primary" title="Editar informações">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            <?php if ($_SESSION['usuario_tipo'] == 'admin'): ?>
                                                <a href="/certidao/excluir/<?php echo $reg['id']; ?>?origem=arquivo&ano=<?php echo $ano_filtro; ?>" class="action-btn text-danger" title="Apagar Definitivamente" onclick="return confirm('ATENÇÃO: Deseja excluir esta certidão permanentemente do histórico?');">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
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