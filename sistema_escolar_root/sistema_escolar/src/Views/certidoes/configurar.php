<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Configurações de Certidões</title>
    <link rel="stylesheet" href="/assets/css/painel.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/certidoes.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Estilos Premium exclusivos para a Tela de Configurações */
        .config-card {
            background: white;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            padding: 25px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .config-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
            color: var(--text-main);
            font-size: 1.15rem;
            font-weight: 700;
        }

        .config-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .config-form input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.95rem;
            color: var(--text-main);
            transition: all 0.2s;
        }

        .config-form input:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .config-list-container {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            background: #ffffff;
        }

        .config-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 350px;
            overflow-y: auto;
        }

        /* Scrollbar Personalizada para a lista */
        .config-list::-webkit-scrollbar {
            width: 6px;
        }

        .config-list::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .config-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .config-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .config-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.2s;
        }

        .config-item:last-child {
            border-bottom: none;
        }

        .config-item:hover {
            background-color: #f8fafc;
        }

        .config-item-name {
            font-weight: 600;
            color: #475569;
            font-size: 0.95rem;
        }

        .empty-list {
            padding: 20px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            background: #f8fafc;
        }
    </style>
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header style="margin-bottom: 25px;">
                <h1 style="color: #1e293b; font-size: 1.8rem;">
                    <i class="fa-solid fa-gear" style="color: #64748b; margin-right: 10px;"></i> Configurar Opções do Sistema
                </h1>
            </header>

            <div class="grid-2-col" style="align-items: start;">

                <div class="config-card">
                    <div class="config-header">
                        <i class="fa-solid fa-truck-fast" style="color: #64748b; margin-right: 8px;"></i>
                        Lista de Fornecedores
                    </div>

                    <form action="/certidao/adicionarOpcao" method="POST" class="config-form">
                        <input type="hidden" name="tipo_lista" value="lista_fornecedores">
                        <input type="text" name="nome" placeholder="Adicionar novo (Ex: GRB)" required>
                        <button type="submit" class="btn-novo" style="padding: 10px 15px;" title="Adicionar Fornecedor">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </form>

                    <div class="config-list-container">
                        <?php if (empty($fornecedores)): ?>
                            <div class="empty-list">Nenhum fornecedor registado.</div>
                        <?php else: ?>
                            <ul class="config-list">
                                <?php foreach ($fornecedores as $f): ?>
                                    <li class="config-item">
                                        <span class="config-item-name"><?= htmlspecialchars($f['nome']) ?></span>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="button" onclick="renomearOpcao(<?= $f['id'] ?>, 'lista_fornecedores', '<?= htmlspecialchars(addslashes($f['nome'])) ?>')" class="action-btn text-primary" title="Renomear" style="background:none; border:none; cursor:pointer;">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <a href="/certidao/excluirOpcao?id=<?= $f['id'] ?>&tipo=lista_fornecedores" onclick="return confirm('Deseja excluir o fornecedor <?= htmlspecialchars($f['nome']) ?> da lista?')" class="action-btn text-danger" title="Excluir">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-header">
                        <i class="fa-solid fa-certificate" style="color: #64748b; margin-right: 8px;"></i>
                        Tipos de Certidão
                    </div>

                    <form action="/certidao/adicionarOpcao" method="POST" class="config-form">
                        <input type="hidden" name="tipo_lista" value="lista_tipos_certidao">
                        <input type="text" name="nome" placeholder="Adicionar novo (Ex: CND FEDERAL)" required>
                        <button type="submit" class="btn-novo" style="padding: 10px 15px;" title="Adicionar Tipo">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </form>

                    <div class="config-list-container">
                        <?php if (empty($tipos)): ?>
                            <div class="empty-list">Nenhum tipo de certidão registado.</div>
                        <?php else: ?>
                            <ul class="config-list">
                                <?php foreach ($tipos as $t): ?>
                                    <li class="config-item">
                                        <span class="config-item-name"><?= htmlspecialchars($t['nome']) ?></span>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="button" onclick="renomearOpcao(<?= $t['id'] ?>, 'lista_tipos_certidao', '<?= htmlspecialchars(addslashes($t['nome'])) ?>')" class="action-btn text-primary" title="Renomear" style="background:none; border:none; cursor:pointer;">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <a href="/certidao/excluirOpcao?id=<?= $t['id'] ?>&tipo=lista_tipos_certidao" onclick="return confirm('Deseja excluir o tipo <?= htmlspecialchars($t['nome']) ?> da lista?')" class="action-btn text-danger" title="Excluir">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div style="margin-top: 30px;">
                <a href="/certidao" class="cancelar"><i class="fa-solid fa-arrow-left"></i> Voltar para a Tela Inicial</a>
            </div>
        </div>
    </div>

    <script>
        function renomearOpcao(id, tipo_tabela, nome_antigo) {
            let novo_nome = prompt("Introduza o novo nome para corrigir:", nome_antigo);

            if (novo_nome !== null && novo_nome.trim() !== "") {
                if (novo_nome.trim().toUpperCase() !== nome_antigo.toUpperCase()) {
                    window.location.href = `/certidao/editarOpcao?id=${id}&tipo=${tipo_tabela}&nome_antigo=${encodeURIComponent(nome_antigo)}&novo_nome=${encodeURIComponent(novo_nome)}`;
                }
            }
        }
    </script>
</body>

</html>