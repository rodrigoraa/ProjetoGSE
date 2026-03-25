<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Arquivo Passivo</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/passivo.css">
</head>

<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>Arquivo Passivo (Ex-Alunos)</h1>
                <div class="date-display"><?php echo date('d/m/Y'); ?></div>
            </header>

            <main>
                <div class="relatorio" style="padding: 20px; display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; align-items: center;">
                    <form action="/passivo" method="GET" style="display: flex; gap: 10px; flex: 1; min-width: 300px; max-width: 800px;">
                        <select name="filtro_caixa" class="sistema" style="margin-bottom: 0; width: 150px; cursor: pointer;" onchange="this.form.submit()">
                            <option value="">Todas as Caixas</option>
                            <?php foreach ($lista_caixas as $cx): ?>
                                <option value="<?php echo e($cx); ?>" <?php echo ($cx == $caixa_atual) ? 'selected' : ''; ?>>
                                    <?php echo e($cx); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="search" name="busca" value="<?php echo e($termo); ?>" placeholder="Pesquisar nome ou numero..." class="sistema" style="margin-bottom: 0; flex: 1;">
                        <button type="submit" class="btn-primary">Buscar</button>

                        <?php if (!empty($termo) || !empty($caixa_atual)): ?>
                            <a href="/passivo" class="btn-secondary" style="display:flex; align-items:center;" title="Limpar Filtros">Limpar</a>
                        <?php endif; ?>
                    </form>

                    <div style="display: flex; gap: 10px;">
                        <a href="/passivo/cadastrar" class="btn-primary" style="background-color: #28a745;">+ Novo</a>
                        <a href="/passivo/ferramentas" class="btn-secondary">Ferramentas</a>
                        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                            <a href="/passivo/importar" class="btn-secondary">Importar</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="relatorio">
                    <?php if ($modo_exibicao == 'dashboard'): ?>
                        <div style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
                            <h3 style="margin: 0; color: var(--primary-color);">Visao Geral do Arquivo</h3>
                        </div>

                        <?php if (empty($resumo_caixas)): ?>
                            <div style="text-align: center; padding: 40px; color: #666;">
                                <p>Nenhuma caixa encontrada. Importe um CSV ou cadastre manualmente.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid-caixas">
                                <?php foreach ($resumo_caixas as $item): ?>
                                    <a href="/passivo?filtro_caixa=<?php echo urlencode($item['caixa']); ?>" class="card-caixa">
                                        <span class="card-title"><?php echo e($item['caixa']); ?></span>
                                        <span class="card-count"><?php echo (int)$item['total']; ?> alunos</span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                            <h3 style="margin: 0; color: var(--primary-color);">
                                <?php if (!empty($caixa_atual)): ?>
                                    Conteudo da Caixa: <strong><?php echo e($caixa_atual); ?></strong>
                                <?php else: ?>
                                    Resultados da Busca
                                <?php endif; ?>
                            </h3>
                            <span style="background: #e9ecef; padding: 2px 10px; border-radius: 12px; font-size: 0.85em; color: #555; font-weight:bold;">
                                <?php echo count($resultados); ?> registros
                            </span>
                        </div>

                        <?php if (empty($resultados)): ?>
                            <div style="padding: 40px; text-align: center; color: #dc3545;">
                                <h3>Nenhum aluno encontrado.</h3>
                            </div>
                        <?php else: ?>
                            <?php if ($modo_exibicao == 'conteudo_caixa' && !empty($nav_caixas)): ?>
                                <div class="nav-caixas">
                                    <?php if ($nav_caixas['prev']): ?>
                                        <a href="/passivo?filtro_caixa=<?php echo urlencode($nav_caixas['prev']); ?>" class="nav-link" title="Caixa Anterior">
                                            &laquo; <?php echo e($nav_caixas['prev']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="nav-link disabled">&laquo; Inicio</span>
                                    <?php endif; ?>

                                    <?php foreach ($nav_caixas['lista_visual'] as $cx): ?>
                                        <a href="/passivo?filtro_caixa=<?php echo urlencode($cx); ?>" class="nav-link <?php echo ($cx == $caixa_atual) ? 'active' : ''; ?>">
                                            <?php echo e($cx); ?>
                                        </a>
                                    <?php endforeach; ?>

                                    <?php if ($nav_caixas['next']): ?>
                                        <a href="/passivo?filtro_caixa=<?php echo urlencode($nav_caixas['next']); ?>" class="nav-link" title="Proxima Caixa">
                                            <?php echo e($nav_caixas['next']); ?> &raquo;
                                        </a>
                                    <?php else: ?>
                                        <span class="nav-link disabled">Fim &raquo;</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <table class="tabela-filtrada">
                                <thead>
                                    <tr>
                                        <th>Nome Completo</th>
                                        <th>Data Nasc.</th>
                                        <th>Numero</th>
                                        <th>Caixa</th>
                                        <th style="text-align: right;">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultados as $reg): ?>
                                        <tr>
                                            <td style="font-weight: 600; color: #333;"><?php echo e($reg['nome_completo']); ?></td>
                                            <td>
                                                <?php if (!empty($reg['data_nascimento'])): ?>
                                                    <?php echo date('d/m/Y', strtotime($reg['data_nascimento'])); ?>
                                                <?php else: ?>
                                                    <span style="color: #999; font-style: italic;">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($reg['numero']); ?></td>
                                            <td>
                                                <span style="background: #e3f2fd; color: #004a91; padding: 4px 10px; border-radius: 15px; font-weight: 700; font-size: 0.85em;">
                                                    <?php echo e($reg['caixa']); ?>
                                                </span>
                                            </td>
                                            <td class="col-acoes" style="text-align: right;">
                                                <a href="/passivo/editar/<?php echo (int)$reg['id']; ?>" style="color: #007bff; margin-right: 10px;">Editar</a>
                                                <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                                                    <form action="/passivo/excluir/<?php echo (int)$reg['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Apagar?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                        <button type="submit" style="color: #dc3545; border:0; background:none; cursor:pointer;">Apagar</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
