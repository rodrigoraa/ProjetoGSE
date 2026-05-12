<?php
require_once ROOT_PATH . '/src/Core/Model.php';

class Contrato extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->garantirColunaFaturado();
    }

    private function garantirColunaFaturado()
    {
        $colunas = self::$pdo->query("PRAGMA table_info(contratos)")->fetchAll();
        foreach ($colunas as $coluna) {
            if (($coluna['name'] ?? '') === 'faturado') {
                return;
            }
        }

        self::$pdo->exec("ALTER TABLE contratos ADD COLUMN faturado INTEGER NOT NULL DEFAULT 0");
    }

    public function recalcularValorFolha($id_contrato, $numero_folha)
    {
        $sql = "SELECT SUM(valor_total) as total FROM contrato_produtos WHERE id_contrato = ? AND numero_folha = ?";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$id_contrato, $numero_folha]);
        $total = $stmt->fetch()['total'] ?? 0;

        $sqlUpd = "UPDATE contrato_folhas SET valor_folha = ? WHERE id_contrato = ? AND numero_folha = ?";
        self::$pdo->prepare($sqlUpd)->execute([$total, $id_contrato, $numero_folha]);
    }

    public function salvarContratoCompleto($titulo, $valor_total, $qtd_folhas, $produtos, $faturado = 0)
    {
        try {
            self::$pdo->beginTransaction();

            $sqlContrato = "INSERT INTO contratos (titulo, valor_total, qtd_folhas, faturado) VALUES (?, ?, ?, ?)";
            $stmt = self::$pdo->prepare($sqlContrato);
            $stmt->execute([$titulo, $valor_total, $qtd_folhas, $faturado ? 1 : 0]);
            $id_contrato = self::$pdo->lastInsertId();

            $sqlFolha = "INSERT INTO contrato_folhas (id_contrato, numero_folha, valor_folha, observacao, data_faturamento) VALUES (?, ?, 0, '', NULL)";
            $stmtFolha = self::$pdo->prepare($sqlFolha);
            for ($i = 1; $i <= $qtd_folhas; $i++) {
                $stmtFolha->execute([$id_contrato, $i]);
            }

            $sqlProduto = "INSERT INTO contrato_produtos (id_contrato, numero_folha, nome_produto, marca, unidade, quantidade, valor_unitario, valor_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtProduto = self::$pdo->prepare($sqlProduto);

            foreach ($produtos as $p) {
                $valor_total_produto = $p['quantidade'] * $p['valor_unitario'];
                $stmtProduto->execute([
                    $id_contrato,
                    1,
                    $p['nome'],
                    $p['marca'] ?? '',
                    $p['unidade'],
                    $p['quantidade'],
                    $p['valor_unitario'],
                    $valor_total_produto
                ]);
            }

            self::$pdo->commit();
            $this->recalcularValorFolha($id_contrato, 1);
            return true;
        } catch (Throwable $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            return false;
        }
    }

    public function atualizarContratoCompleto($id, $titulo, $valor_total, $qtd_folhas, $produtos)
    {
        try {
            self::$pdo->beginTransaction();

            $sqlContrato = "UPDATE contratos SET titulo = ?, valor_total = ?, qtd_folhas = ? WHERE id = ?";
            self::$pdo->prepare($sqlContrato)->execute([$titulo, $valor_total, $qtd_folhas, $id]);

            self::$pdo->prepare("DELETE FROM contrato_folhas WHERE id_contrato = ?")->execute([$id]);
            self::$pdo->prepare("DELETE FROM contrato_produtos WHERE id_contrato = ?")->execute([$id]);

            $sqlFolha = "INSERT INTO contrato_folhas (id_contrato, numero_folha, valor_folha, observacao, data_faturamento) VALUES (?, ?, 0, '', NULL)";
            $stmtFolha = self::$pdo->prepare($sqlFolha);
            for ($i = 1; $i <= $qtd_folhas; $i++) {
                $stmtFolha->execute([$id, $i]);
            }

            $sqlProduto = "INSERT INTO contrato_produtos (id_contrato, numero_folha, nome_produto, marca, unidade, quantidade, valor_unitario, valor_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtProduto = self::$pdo->prepare($sqlProduto);

            foreach ($produtos as $p) {
                $valor_total_produto = $p['quantidade'] * $p['valor_unitario'];
                $stmtProduto->execute([
                    $id,
                    1,
                    $p['nome'],
                    $p['marca'] ?? '',
                    $p['unidade'],
                    $p['quantidade'],
                    $p['valor_unitario'],
                    $valor_total_produto
                ]);
            }

            self::$pdo->commit();
            $this->recalcularValorFolha($id, 1);
            return true;
        } catch (Exception $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            return false;
        }
    }

    public function atualizarDadosGerais($id, $titulo, $valor_total, $faturado)
    {
        try {
            $sql = "UPDATE contratos SET titulo = ?, valor_total = ?, faturado = ? WHERE id = ?";
            return self::$pdo->prepare($sql)->execute([$titulo, $valor_total, $faturado ? 1 : 0, $id]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function atualizarFaturado($id, $faturado)
    {
        $stmt = self::$pdo->prepare("UPDATE contratos SET faturado = ? WHERE id = ?");
        return $stmt->execute([$faturado ? 1 : 0, $id]);
    }

    public function listarTodos()
    {
        return self::$pdo->query("SELECT * FROM contratos ORDER BY id DESC")->fetchAll();
    }

    public function buscarPorId($id)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM contratos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function buscarProdutos($id_contrato)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM contrato_produtos WHERE id_contrato = ?");
        $stmt->execute([$id_contrato]);
        return $stmt->fetchAll();
    }

    public function buscarFolhas($id_contrato)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM contrato_folhas WHERE id_contrato = ? ORDER BY numero_folha ASC");
        $stmt->execute([$id_contrato]);
        return $stmt->fetchAll();
    }

    public function buscarFolha($id_contrato, $numero_folha)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM contrato_folhas WHERE id_contrato = ? AND numero_folha = ?");
        $stmt->execute([$id_contrato, $numero_folha]);
        return $stmt->fetch();
    }

    public function folhaExiste($id_contrato, $numero_folha)
    {
        $stmt = self::$pdo->prepare("SELECT 1 FROM contrato_folhas WHERE id_contrato = ? AND numero_folha = ?");
        $stmt->execute([$id_contrato, $numero_folha]);
        return (bool)$stmt->fetchColumn();
    }

    public function contarFolhas($id_contrato)
    {
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM contrato_folhas WHERE id_contrato = ?");
        $stmt->execute([$id_contrato]);
        return (int)$stmt->fetchColumn();
    }

    public function atualizarObservacaoFolha($id_contrato, $numero_folha, $observacao)
    {
        $stmt = self::$pdo->prepare("UPDATE contrato_folhas SET observacao = ? WHERE id_contrato = ? AND numero_folha = ?");
        return $stmt->execute([$observacao, $id_contrato, $numero_folha]);
    }

    public function atualizarDataFaturamentoFolha($id_contrato, $numero_folha, $data_faturamento)
    {
        $stmt = self::$pdo->prepare("UPDATE contrato_folhas SET data_faturamento = ? WHERE id_contrato = ? AND numero_folha = ?");
        return $stmt->execute([$data_faturamento, $id_contrato, $numero_folha]);
    }

    public function excluir($id)
    {
        try {
            self::$pdo->beginTransaction();
            self::$pdo->prepare("DELETE FROM contrato_produtos WHERE id_contrato = ?")->execute([$id]);
            self::$pdo->prepare("DELETE FROM contrato_folhas WHERE id_contrato = ?")->execute([$id]);
            self::$pdo->prepare("DELETE FROM contratos WHERE id = ?")->execute([$id]);
            self::$pdo->commit();
            return true;
        } catch (Exception $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            return false;
        }
    }

    public function adicionarProdutoUnico($id_contrato, $numero_folha, $nome, $marca, $unidade, $quantidade, $valor_unitario)
    {
        $valor_total = $quantidade * $valor_unitario;
        $sql = "INSERT INTO contrato_produtos (id_contrato, numero_folha, nome_produto, marca, unidade, quantidade, valor_unitario, valor_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $resultado = self::$pdo->prepare($sql)->execute([$id_contrato, $numero_folha, $nome, $marca, $unidade, $quantidade, $valor_unitario, $valor_total]);

        if ($resultado) {
            $this->recalcularValorFolha($id_contrato, $numero_folha);
        }
        return $resultado;
    }

    public function buscarProdutoPorId($id_produto)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM contrato_produtos WHERE id = ?");
        $stmt->execute([$id_produto]);
        return $stmt->fetch();
    }

    public function buscarProdutoPorIdComContrato($id_produto)
    {
        $stmt = self::$pdo->prepare("SELECT p.*, c.id AS id_contrato, c.titulo FROM contrato_produtos p JOIN contratos c ON c.id = p.id_contrato WHERE p.id = ?");
        $stmt->execute([$id_produto]);
        return $stmt->fetch();
    }

    public function atualizarProduto($id_produto, $nome, $marca, $unidade, $quantidade, $valor_unitario)
    {
        $produto = $this->buscarProdutoPorId($id_produto);
        if (!$produto) {
            return false;
        }

        $valor_total = $quantidade * $valor_unitario;
        $sql = "UPDATE contrato_produtos SET nome_produto = ?, marca = ?, unidade = ?, quantidade = ?, valor_unitario = ?, valor_total = ? WHERE id = ?";
        $resultado = self::$pdo->prepare($sql)->execute([$nome, $marca, $unidade, $quantidade, $valor_unitario, $valor_total, $id_produto]);

        if ($resultado) {
            $this->recalcularValorFolha($produto['id_contrato'], $produto['numero_folha']);
        }
        return $resultado;
    }

    public function excluirProduto($id_produto)
    {
        $produto = $this->buscarProdutoPorId($id_produto);
        if (!$produto) {
            return false;
        }

        $resultado = self::$pdo->prepare("DELETE FROM contrato_produtos WHERE id = ?")->execute([$id_produto]);

        if ($resultado) {
            $this->recalcularValorFolha($produto['id_contrato'], $produto['numero_folha']);
        }
        return $resultado;
    }

    public function excluirFolha($id_contrato, $numero_folha)
    {
        try {
            if ($this->contarFolhas($id_contrato) <= 1) {
                return false;
            }

            self::$pdo->beginTransaction();
            self::$pdo->prepare("DELETE FROM contrato_produtos WHERE id_contrato = ? AND numero_folha = ?")->execute([$id_contrato, $numero_folha]);
            self::$pdo->prepare("DELETE FROM contrato_folhas WHERE id_contrato = ? AND numero_folha = ?")->execute([$id_contrato, $numero_folha]);

            self::$pdo->prepare("UPDATE contrato_produtos SET numero_folha = numero_folha - 1 WHERE id_contrato = ? AND numero_folha > ?")->execute([$id_contrato, $numero_folha]);
            self::$pdo->prepare("UPDATE contrato_folhas SET numero_folha = numero_folha - 1 WHERE id_contrato = ? AND numero_folha > ?")->execute([$id_contrato, $numero_folha]);
            self::$pdo->prepare("UPDATE contratos SET qtd_folhas = qtd_folhas - 1 WHERE id = ?")->execute([$id_contrato]);

            self::$pdo->commit();
            return true;
        } catch (Exception $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            return false;
        }
    }

    public function adicionarFolha($id_contrato)
    {
        try {
            self::$pdo->beginTransaction();
            $stmtMax = self::$pdo->prepare("SELECT MAX(numero_folha) as max_folha FROM contrato_folhas WHERE id_contrato = ?");
            $stmtMax->execute([$id_contrato]);
            $nova_folha = ($stmtMax->fetch()['max_folha'] ?? 0) + 1;

            self::$pdo->prepare("INSERT INTO contrato_folhas (id_contrato, numero_folha, valor_folha, observacao, data_faturamento) VALUES (?, ?, 0, '', NULL)")->execute([$id_contrato, $nova_folha]);
            self::$pdo->prepare("UPDATE contratos SET qtd_folhas = qtd_folhas + 1 WHERE id = ?")->execute([$id_contrato]);

            self::$pdo->commit();
            return true;
        } catch (Exception $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            return false;
        }
    }

    public function duplicarFolha($id_contrato, $numero_folha_origem)
    {
        try {
            self::$pdo->beginTransaction();

            $stmtMax = self::$pdo->prepare("SELECT MAX(numero_folha) as max_folha FROM contrato_folhas WHERE id_contrato = ?");
            $stmtMax->execute([$id_contrato]);
            $nova_folha = ($stmtMax->fetch()['max_folha'] ?? 0) + 1;

            $folhaOrigem = $this->buscarFolha($id_contrato, $numero_folha_origem);
            $observacaoFolha = trim((string)($folhaOrigem['observacao'] ?? ''));

            self::$pdo->prepare("INSERT INTO contrato_folhas (id_contrato, numero_folha, valor_folha, observacao, data_faturamento) VALUES (?, ?, 0, ?, NULL)")->execute([$id_contrato, $nova_folha, $observacaoFolha]);

            $stmtProd = self::$pdo->prepare("SELECT * FROM contrato_produtos WHERE id_contrato = ? AND numero_folha = ?");
            $stmtProd->execute([$id_contrato, $numero_folha_origem]);
            $produtos = $stmtProd->fetchAll();

            $sqlInsertProd = "INSERT INTO contrato_produtos (id_contrato, numero_folha, nome_produto, marca, unidade, quantidade, valor_unitario, valor_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtIns = self::$pdo->prepare($sqlInsertProd);

            foreach ($produtos as $p) {
                $stmtIns->execute([
                    $id_contrato,
                    $nova_folha,
                    $p['nome_produto'],
                    $p['marca'],
                    $p['unidade'],
                    $p['quantidade'],
                    $p['valor_unitario'],
                    $p['valor_total']
                ]);
            }

            self::$pdo->prepare("UPDATE contratos SET qtd_folhas = qtd_folhas + 1 WHERE id = ?")->execute([$id_contrato]);
            self::$pdo->commit();

            $this->recalcularValorFolha($id_contrato, $nova_folha);
            return $nova_folha;
        } catch (Exception $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            return false;
        }
    }
}
