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
            <header style="margin-bottom: 25px;">
                <h1 style="color: #1e293b; font-size: 1.8rem;"><i class="fa-solid fa-gear" style="color: #64748b; margin-right: 10px;"></i> Configurar Opções do Sistema</h1>
            </header>

            <div class="grid-2-col" style="align-items: start;">
                <div class="config-card">
                    <div class="config-header"><i class="fa-solid fa-truck-fast" style="color: #64748b; margin-right: 8px;"></i> Lista de Fornecedores</div>

                    <form action="/certidao/adicionarOpcao" method="POST" class="config-form">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                        <input type="hidden" name="tipo_lista" value="lista_fornecedores">
                        <input type="text" name="nome" placeholder="Adicionar novo (Ex: GRB)" required>
                        <button type="submit" class="btn-novo" style="padding: 10px 15px;" title="Adicionar Fornecedor"><i class="fa-solid fa-plus"></i></button>
                    </form>

                    <div class="config-list-container">
                        <?php if (empty($fornecedores)): ?>
                            <div class="empty-list">Nenhum fornecedor registrado.</div>
                        <?php else: ?>
                            <ul class="config-list">
                                <?php foreach ($fornecedores as $f): ?>
                                    <li class="config-item">
                                        <span class="config-item-name"><?php echo e($f['nome']); ?></span>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="button" onclick="renomearOpcao(<?php echo (int)$f['id']; ?>, 'lista_fornecedores', <?php echo json_encode($f['nome']); ?>)" class="action-btn text-primary" title="Renomear" style="background:none; border:none; cursor:pointer;"><i class="fa-solid fa-pen"></i></button>
                                            <form action="/certidao/excluirOpcao" method="POST" style="display:inline;" onsubmit="return confirm('Deseja excluir o fornecedor <?php echo e($f['nome']); ?> da lista?');">
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
                    <div class="config-header"><i class="fa-solid fa-certificate" style="color: #64748b; margin-right: 8px;"></i> Tipos de Certidão</div>

                    <form action="/certidao/adicionarOpcao" method="POST" class="config-form">
                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                        <input type="hidden" name="tipo_lista" value="lista_tipos_certidao">
                        <input type="text" name="nome" placeholder="Adicionar novo (Ex: CND FEDERAL)" required>
                        <button type="submit" class="btn-novo" style="padding: 10px 15px;" title="Adicionar Tipo"><i class="fa-solid fa-plus"></i></button>
                    </form>

                    <div class="config-list-container">
                        <?php if (empty($tipos)): ?>
                            <div class="empty-list">Nenhum tipo de certidão registrado.</div>
                        <?php else: ?>
                            <ul class="config-list">
                                <?php foreach ($tipos as $t): ?>
                                    <li class="config-item">
                                        <span class="config-item-name"><?php echo e($t['nome']); ?></span>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="button" onclick="renomearOpcao(<?php echo (int)$t['id']; ?>, 'lista_tipos_certidao', <?php echo json_encode($t['nome']); ?>)" class="action-btn text-primary" title="Renomear" style="background:none; border:none; cursor:pointer;"><i class="fa-solid fa-pen"></i></button>
                                            <form action="/certidao/excluirOpcao" method="POST" style="display:inline;" onsubmit="return confirm('Deseja excluir o tipo <?php echo e($t['nome']); ?> da lista?');">
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

            <div style="margin-top: 30px;">
                <a href="/certidao" class="cancelar"><i class="fa-solid fa-arrow-left"></i> Voltar para a Tela Inicial</a>
            </div>
        </div>
    </div>

    <script>
        function renomearOpcao(id, tipoTabela, nomeAntigo) {
            const novoNome = prompt("Introduza o novo nome para corrigir:", nomeAntigo);
            if (novoNome !== null && novoNome.trim() !== "" && novoNome.trim().toUpperCase() !== nomeAntigo.toUpperCase()) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/certidao/editarOpcao';

                const campos = {
                    csrf_token: <?php echo json_encode(gerar_csrf_token()); ?>,
                    id: id,
                    tipo: tipoTabela,
                    novo_nome: novoNome.trim()
                };

                Object.entries(campos).forEach(([nome, valor]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = nome;
                    input.value = valor;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>
