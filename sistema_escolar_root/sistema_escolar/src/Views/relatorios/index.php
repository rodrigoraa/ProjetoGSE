<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerar Relatórios</title>
    <link rel="stylesheet" href="/assets/css/painel.css">
    <link rel="stylesheet" href="/assets/css/relatorios.css">
</head>
<body>
    <div class="layout-container">
        <?php include VIEW_PATH . '/partials/menu.php'; ?>

        <div class="main-content-wrapper">
            <header>
                <h1>📊 Gerador de Relatórios</h1>
            </header>
            
            <main>
                <div class="box-relatorio">
                    
                    <form action="/relatorio/gerar" method="POST" target="_blank" class="sistema">
                        
                        <div style="margin-bottom: 25px;">
                            <label class="label-destaque">1. Filtrar por Turma (Opcional):</label>
                            <select name="turma" class="select-grande">
                                <option value="">Todas as Turmas</option>
                                <?php foreach ($turmas as $t): ?>
                                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nome_turma']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label class="label-destaque">2. Status da DVA:</label>
                            <select name="status" class="select-grande">
                                <option value="todos">Todos (Geral)</option>
                                <option value="sem_dva">⚪ Sem DVA (Pendentes)</option>
                                <option value="vencida">🔴 Vencidas</option>
                                <option value="avencer">🟡 A Vencer (30 dias)</option>
                                <option value="vigente">🟢 Vigentes (Em dia)</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <label class="label-destaque">3. Formato do Arquivo:</label>
                            
                            <div class="opcoes-formato">
                                <label class="radio-card pdf">
                                    <input type="radio" name="formato" value="pdf" checked> 
                                    📄 PDF
                                </label>
                                
                                <label class="radio-card excel">
                                    <input type="radio" name="formato" value="excel"> 
                                    📊 Excel
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary btn-gerar">
                            📥 Gerar Relatório
                        </button>

                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>