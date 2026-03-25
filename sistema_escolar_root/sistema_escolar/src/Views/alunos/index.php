<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Alunos</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/alunos.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Gerenciar Alunos</h1>
            </header>

            <main>
                <div class="toolbar-alunos">
                    <a href="/aluno/cadastrar" class="btn-primary" style="text-decoration:none;">+ Novo Aluno</a>

                    <form action="/aluno" method="GET" class="form-busca">
                        <input type="search" name="busca" value="<?php echo e($termo); ?>" placeholder="Nome do aluno..." class="sistema" style="margin-bottom:0;">
                        <button type="submit" class="btn-secondary">Buscar</button>
                    </form>
                </div>

                <div class="relatorio">
                    <h3 class="form-section-title">Alunos Cadastrados (<?php echo (int)$total_registros; ?>)</h3>

                    <table class="tabela-filtrada">
                        <thead>
                            <tr>
                                <th>Nome Completo</th>
                                <th>Turma</th>
                                <th>Data Nasc.</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lista_alunos)): ?>
                                <tr>
                                    <td colspan="4" style="text-align:center; padding:20px; color:#777;">Nenhum aluno encontrado.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($lista_alunos as $aluno): ?>
                                <tr>
                                    <td class="nome-aluno">
                                        <a href="/aluno/perfil/<?php echo (int)$aluno['id']; ?>"><?php echo e($aluno['nome_completo']); ?></a>
                                    </td>
                                    <td><?php echo e($aluno['nome_turma'] ?? 'Sem turma'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($aluno['data_nascimento'])); ?></td>

                                    <td class="col-acoes">
                                        <a href="/aluno/perfil/<?php echo (int)$aluno['id']; ?>" class="editar">Perfil</a>
                                        <a href="/aluno/editar/<?php echo (int)$aluno['id']; ?>" class="editar">Editar</a>

                                        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                                            <form action="/aluno/excluir/<?php echo (int)$aluno['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza absoluta? Isso apagara o aluno e a DVA dele.');">
                                                <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                <button type="submit" class="btn-danger" style="text-decoration:none; border:0; cursor:pointer;">Apagar</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($total_paginas > 1): ?>
                        <div class="paginacao">
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <?php if ($i == $pagina_atual): ?>
                                    <span class="ativo"><?php echo (int)$i; ?></span>
                                <?php else: ?>
                                    <a href="/aluno?pagina=<?php echo (int)$i; ?>&busca=<?php echo urlencode($termo); ?>"><?php echo (int)$i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
