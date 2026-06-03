<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Configurações de Certidões</title>
    <link rel="stylesheet" href="/assets/css/painel.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/certidoes.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header class="cert-page-header">
                <h1><i class="fa-solid fa-gear"></i> Configurar Opções do Sistema</h1>
            </header>

            <?php $flash = consumir_flash(); ?>
            <?php if (!empty($flash)): ?>
                <?php echo $flash; ?>
            <?php endif; ?>

            <section class="config-intro">
                <div>
                    <h2>Organize os catálogos usados nas certidões</h2>
                    <p>Use esta tela para manter a lista de fornecedores e os tipos de certidão atualizados. As alterações feitas aqui já aparecem nos formulários de cadastro e edição.</p>
                </div>
                <div class="config-chip"><i class="fa-solid fa-shield-halved"></i> Alterações com feedback imediato</div>
            </section>

            <div class="grid-2-col config-layout">
                <div class="config-card">
                    <div class="config-card-header">
                        <div class="config-header"><i class="fa-solid fa-truck-fast"></i> Lista de Fornecedores</div>
                        <p>Cadastre e mantenha os nomes usados para vincular cada certidão ao respectivo fornecedor.</p>
                    </div>

                    <form action="/certidao/adicionarOpcao" method="POST" class="config-form">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                        <input type="hidden" name="tipo_lista" value="lista_fornecedores">
                        <input type="text" name="nome" placeholder="Adicionar novo (Ex: GRB)" required>
                        <button type="submit" class="btn-novo" title="Adicionar Fornecedor"><i class="fa-solid fa-plus"></i></button>
                    </form>

                    <div class="config-list-container">
                        <?php if (empty($fornecedores)): ?>
                            <div class="empty-list">Nenhum fornecedor registrado.</div>
                        <?php else: ?>
                            <ul class="config-list">
                                <?php foreach ($fornecedores as $f): ?>
                                    <li class="config-item">
                                        <span class="config-item-name"><?php echo e($f['nome']); ?></span>
                                        <div class="config-item-actions">
                                            <button type="button" onclick="abrirModalRenomear(<?php echo (int)$f['id']; ?>, 'lista_fornecedores', <?php echo json_encode($f['nome']); ?>, 'fornecedor')" class="action-btn text-primary" title="Renomear" style="background:none; border:none; cursor:pointer;"><i class="fa-solid fa-pen"></i></button>
                                            <form action="/certidao/excluirOpcao" method="POST" onsubmit="return confirm('Deseja excluir o fornecedor <?php echo e($f['nome']); ?> da lista?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$f['id']; ?>">
                                                <input type="hidden" name="tipo" value="lista_fornecedores">
                                                <button type="submit" class="action-btn text-danger" title="Excluir" style="border:0; cursor:pointer;"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="config-card">
                    <div class="config-card-header">
                        <div class="config-header"><i class="fa-solid fa-certificate"></i> Tipos de Certidão</div>
                        <p>Mantenha a nomenclatura dos documentos padronizada para evitar cadastros duplicados ou nomes divergentes.</p>
                    </div>

                    <form action="/certidao/adicionarOpcao" method="POST" class="config-form">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                        <input type="hidden" name="tipo_lista" value="lista_tipos_certidao">
                        <input type="text" name="nome" placeholder="Adicionar novo (Ex: CND FEDERAL)" required>
                        <button type="submit" class="btn-novo" title="Adicionar Tipo"><i class="fa-solid fa-plus"></i></button>
                    </form>

                    <div class="config-list-container">
                        <?php if (empty($tipos)): ?>
                            <div class="empty-list">Nenhum tipo de certidão registrado.</div>
                        <?php else: ?>
                            <ul class="config-list">
                                <?php foreach ($tipos as $t): ?>
                                    <li class="config-item">
                                        <span class="config-item-name"><?php echo e($t['nome']); ?></span>
                                        <div class="config-item-actions">
                                            <button type="button" onclick="abrirModalRenomear(<?php echo (int)$t['id']; ?>, 'lista_tipos_certidao', <?php echo json_encode($t['nome']); ?>, 'tipo de certidão')" class="action-btn text-primary" title="Renomear" style="background:none; border:none; cursor:pointer;"><i class="fa-solid fa-pen"></i></button>
                                            <form action="/certidao/excluirOpcao" method="POST" onsubmit="return confirm('Deseja excluir o tipo <?php echo e($t['nome']); ?> da lista?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                                                <input type="hidden" name="tipo" value="lista_tipos_certidao">
                                                <button type="submit" class="action-btn text-danger" title="Excluir" style="border:0; cursor:pointer;"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="page-footer-link">
                <a href="/certidao" class="cancelar"><i class="fa-solid fa-arrow-left"></i> Voltar para a Tela Inicial</a>
            </div>
        </div>
    </div>

    <div class="rename-modal" id="renameModal" hidden>
        <div class="rename-modal-backdrop" onclick="fecharModalRenomear()"></div>
        <div class="rename-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="renameModalTitle">
            <div class="rename-modal-header">
                <h2 id="renameModalTitle">Renomear opção</h2>
                <p id="renameModalDescription">Atualize o texto desta opção para manter a lista organizada.</p>
            </div>

            <form action="/certidao/editarOpcao" method="POST" class="rename-modal-body" id="renameForm">
                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                <input type="hidden" name="id" id="renameId">
                <input type="hidden" name="tipo" id="renameTipo">

                <label for="renameNome">Novo nome</label>
                <input type="text" name="novo_nome" id="renameNome" required>

                <div class="rename-modal-actions">
                    <button type="button" class="btn-modal-secondary" onclick="fecharModalRenomear()">Cancelar</button>
                    <button type="submit" class="btn-novo">Salvar alteração</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const renameModal = document.getElementById('renameModal');
        const renameId = document.getElementById('renameId');
        const renameTipo = document.getElementById('renameTipo');
        const renameNome = document.getElementById('renameNome');
        const renameModalTitle = document.getElementById('renameModalTitle');
        const renameModalDescription = document.getElementById('renameModalDescription');

        function abrirModalRenomear(id, tipoTabela, nomeAntigo, label) {
            renameId.value = id;
            renameTipo.value = tipoTabela;
            renameNome.value = nomeAntigo;
            renameModalTitle.textContent = 'Renomear ' + label;
            renameModalDescription.textContent = 'Atualize o nome desta opção sem precisar sair da tela de configuração.';
            renameModal.hidden = false;
            setTimeout(() => renameNome.focus(), 50);
            renameNome.select();
        }

        function fecharModalRenomear() {
            renameModal.hidden = true;
            renameId.value = '';
            renameTipo.value = '';
            renameNome.value = '';
        }

        document.getElementById('renameForm').addEventListener('submit', function(event) {
            if (renameNome.value.trim() === '') {
                event.preventDefault();
                renameNome.focus();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !renameModal.hidden) {
                fecharModalRenomear();
            }
        });
    </script>
</body>

</html>
