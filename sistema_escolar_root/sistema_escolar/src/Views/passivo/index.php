<?php
$totalCaixas = count($lista_caixas);
$totalRegistrosResumo = 0;
foreach ($resumo_caixas as $item) {
    $totalRegistrosResumo += (int)$item['total'];
}
?>
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
                <?php $flash = consumir_flash(); ?>
                <?php if (!empty($flash)): ?>
                    <?php echo $flash; ?>
                <?php endif; ?>

                <section class="passivo-hero">
                    <div>
                        <h2>Consulta central do acervo físico e histórico</h2>
                        <p>Use os filtros para localizar rapidamente caixas, pesquisar ex-alunos e importar ou exportar conteúdos.</p>
                    </div>
                    <div class="passivo-stats">
                        <div class="passivo-stat">
                            <strong><?php echo (int)$totalCaixas; ?></strong>
                            <span>Caixas</span>
                        </div>
                        <div class="passivo-stat">
                            <strong><?php echo (int)$totalRegistrosResumo; ?></strong>
                            <span>Registros</span>
                        </div>
                        <div class="passivo-stat">
                            <strong><?php echo $modo_exibicao === 'dashboard' ? 'Visão geral' : count($resultados); ?></strong>
                            <span><?php echo $modo_exibicao === 'dashboard' ? 'modo atual' : 'resultados visíveis'; ?></span>
                        </div>
                    </div>
                </section>

                <div class="toolbar-passivo">
                    <div class="toolbar-headline">
                        <h3>Busca e navegação</h3>
                        <p>Filtre por caixa, pesquise por nome ou número e acesse ferramentas rápidas do módulo.</p>
                    </div>

                    <form action="/passivo" method="GET" class="form-busca">
                        <select name="filtro_caixa" class="sistema" style="margin-bottom: 0; width: 150px; cursor: pointer;" onchange="this.form.submit()">
                            <option value="">Todas as Caixas</option>
                            <?php foreach ($lista_caixas as $cx): ?>
                                <option value="<?php echo e($cx); ?>" <?php echo ($cx == $caixa_atual) ? 'selected' : ''; ?>>
                                    📦<?php echo e($cx); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="search" name="busca" value="<?php echo e($termo); ?>" placeholder="Pesquisar nome ou número..." class="sistema" style="margin-bottom: 0; flex: 1;">
                        <button type="submit" class="btn-primary">Buscar</button>

                        <?php if (!empty($termo) || !empty($caixa_atual)): ?>
                            <a href="/passivo" class="btn-secondary" style="display:flex; align-items:center;" title="Limpar Filtros">Limpar</a>
                        <?php endif; ?>
                    </form>

                    <div class="toolbar-actions">
                        <a href="/passivo/cadastrar" class="btn-primary" style="background-color: #28a745;">+ Novo</a>
                        <a href="/passivo/ferramentas" class="btn-secondary">Ferramentas</a>
                        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                            <a href="/passivo/importar" class="btn-secondary">Importar</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="relatorio">
                    <?php if ($modo_exibicao == 'dashboard'): ?>
                        <div class="section-head">
                            <div>
                                <h3>Visão Geral do Arquivo</h3>
                                <p>Clique em uma caixa para abrir seu conteúdo e continuar a navegação por sequência.</p>
                            </div>
                            <span class="result-count"><?php echo (int)$totalCaixas; ?> caixas</span>
                        </div>

                        <?php if (empty($resumo_caixas)): ?>
                            <div class="passivo-empty">
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
                        <div class="section-head">
                            <div>
                                <h3>
                                    <?php if (!empty($caixa_atual)): ?>
                                        Conteúdo da Caixa: <strong><?php echo e($caixa_atual); ?></strong>
                                    <?php else: ?>
                                        Resultados da Busca
                                    <?php endif; ?>
                                </h3>
                                <p><?php echo !empty($caixa_atual) ? 'Use a navegação abaixo para avançar entre caixas vizinhas.' : 'Resultados limitados à pesquisa atual.'; ?></p>
                            </div>
                            <span class="result-count"><?php echo count($resultados); ?> registros</span>
                        </div>

                        <?php if (empty($resultados)): ?>
                            <div class="passivo-empty danger">
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
                                        <span class="nav-link disabled">&laquo; Início</span>
                                    <?php endif; ?>

                                    <?php foreach ($nav_caixas['lista_visual'] as $cx): ?>
                                        <a href="/passivo?filtro_caixa=<?php echo urlencode($cx); ?>" class="nav-link <?php echo ($cx == $caixa_atual) ? 'active' : ''; ?>">
                                            <?php echo e($cx); ?>
                                        </a>
                                    <?php endforeach; ?>

                                    <?php if ($nav_caixas['next']): ?>
                                        <a href="/passivo?filtro_caixa=<?php echo urlencode($nav_caixas['next']); ?>" class="nav-link" title="Próxima Caixa">
                                            <?php echo e($nav_caixas['next']); ?> &raquo;
                                        </a>
                                    <?php else: ?>
                                        <span class="nav-link disabled">Fim &raquo;</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="tabela-filtrada-wrap">
                                <table class="tabela-filtrada">
                                    <thead>
                                        <tr>
                                            <th>Nome Completo</th>
                                            <th>Data Nasc.</th>
                                            <th>Número</th>
                                            <th>Caixa</th>
                                            <th style="text-align: right;">Ações</th>
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
                                                    <span class="badge-caixa"><?php echo e($reg['caixa']); ?></span>
                                                </td>
                                                <td class="passivo-actions">
                                                    <a href="/passivo/editar/<?php echo (int)$reg['id']; ?>" class="link-editar">✏️ Editar</a>
                                                    <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                                                        <form action="/passivo/excluir/<?php echo (int)$reg['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Apagar?');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                                                            <button type="submit" class="link-excluir">🗑️ Apagar</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
