<?php
require_once ROOT_PATH . '/src/Core/Model.php';

class Contrato extends Model
{
    public function salvarContratoCompleto($titulo, $valor_total, $qtd_folhas, $produtos)
    {
        try {
            self::$pdo->beginTransaction();

            $sqlContrato = "INSERT INTO contratos (titulo, valor_total, qtd_folhas) VALUES (?, ?, ?)";
            $stmt = self::$pdo->prepare($sqlContrato);
            $stmt->execute([$titulo, $valor_total, $qtd_folhas]);
            $id_contrato = self::$pdo->lastInsertId();

            $valor_por_folha = $qtd_folhas > 0 ? ($valor_total / $qtd_folhas) : $valor_total;
            $sqlFolha = "INSERT INTO contrato_folhas (id_contrato, numero_folha, valor_folha) VALUES (?, ?, ?)";
            $stmtFolha = self::$pdo->prepare($sqlFolha);
            for ($i = 1; $i <= $qtd_folhas; $i++) {
                $stmtFolha->execute([$id_contrato, $i, $valor_por_folha]);
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
            return true;
        } catch (Throwable $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            error_log("Erro Critico (Contrato -> salvarContratoCompleto): " . $e->getMessage());
            return false;
        }
    }

    public function atualizarContratoCompleto($id, $titulo, $valor_total, $qtd_folhas, $produtos)
    {
        try {
            self::$pdo->beginTransaction();

            $sqlContrato = "UPDATE contratos SET titulo = ?, valor_total = ?, qtd_folhas = ? WHERE id = ?";
            $stmt = self::$pdo->prepare($sqlContrato);
            $stmt->execute([$titulo, $valor_total, $qtd_folhas, $id]);

            self::$pdo->prepare("DELETE FROM contrato_folhas WHERE id_contrato = ?")->execute([$id]);
            self::$pdo->prepare("DELETE FROM contrato_produtos WHERE id_contrato = ?")->execute([$id]);

            $valor_por_folha = $qtd_folhas > 0 ? ($valor_total / $qtd_folhas) : $valor_total;
            $sqlFolha = "INSERT INTO contrato_folhas (id_contrato, numero_folha, valor_folha) VALUES (?, ?, ?)";
            $stmtFolha = self::$pdo->prepare($sqlFolha);
            for ($i = 1; $i <= $qtd_folhas; $i++) {
                $stmtFolha->execute([$id, $i, $valor_por_folha]);
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
            return true;
        } catch (Exception $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            error_log("Erro (Contrato -> atualizarContratoCompleto ID {$id}): " . $e->getMessage());
            return false;
        }
    }

    public function listarTodos()
    {
        $sql = "SELECT * FROM contratos ORDER BY id DESC";
        return self::$pdo->query($sql)->fetchAll();
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM contratos WHERE id = ?";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function buscarProdutos($id_contrato)
    {
        $sql = "SELECT * FROM contrato_produtos WHERE id_contrato = ?";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$id_contrato]);
        return $stmt->fetchAll();
    }

    public function buscarFolhas($id_contrato)
    {
        $sql = "SELECT * FROM contrato_folhas WHERE id_contrato = ? ORDER BY numero_folha ASC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$id_contrato]);
        return $stmt->fetchAll();
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
            error_log("Erro ao excluir contrato {$id}: " . $e->getMessage());
            return false;
        }
    }

    public function adicionarProdutoUnico($id_contrato, $numero_folha, $nome, $marca, $unidade, $quantidade, $valor_unitario)
    {
        $valor_total = $quantidade * $valor_unitario;
        $sql = "INSERT INTO contrato_produtos (id_contrato, numero_folha, nome_produto, marca, unidade, quantidade, valor_unitario, valor_total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        return self::$pdo->prepare($sql)->execute([$id_contrato, $numero_folha, $nome, $marca, $unidade, $quantidade, $valor_unitario, $valor_total]);
    }

    public function buscarProdutoPorId($id_produto)
    {
        $sql = "SELECT * FROM contrato_produtos WHERE id = ?";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$id_produto]);
        return $stmt->fetch();
    }

    public function buscarProdutoPorIdComContrato($id_produto)
    {
        $sql = "SELECT p.*, c.id AS id_contrato, c.titulo
                FROM contrato_produtos p
                JOIN contratos c ON c.id = p.id_contrato
                WHERE p.id = ?";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$id_produto]);
        return $stmt->fetch();
    }

    public function atualizarProduto($id_produto, $nome, $marca, $unidade, $quantidade, $valor_unitario)
    {
        $valor_total = $quantidade * $valor_unitario;
        $sql = "UPDATE contrato_produtos SET nome_produto = ?, marca = ?, unidade = ?, quantidade = ?, valor_unitario = ?, valor_total = ? WHERE id = ?";
        return self::$pdo->prepare($sql)->execute([$nome, $marca, $unidade, $quantidade, $valor_unitario, $valor_total, $id_produto]);
    }

    public function excluirProduto($id_produto)
    {
        $sql = "DELETE FROM contrato_produtos WHERE id = ?";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([$id_produto]);
    }

    public function excluirFolha($id_contrato, $numero_folha)
    {
        try {
            self::$pdo->beginTransaction();

            self::$pdo->prepare("DELETE FROM contrato_produtos WHERE id_contrato = ? AND numero_folha = ?")->execute([$id_contrato, $numero_folha]);
            self::$pdo->prepare("DELETE FROM contrato_folhas WHERE id_contrato = ? AND numero_folha = ?")->execute([$id_contrato, $numero_folha]);

            self::$pdo->prepare("UPDATE contrato_produtos SET numero_folha = numero_folha - 1 WHERE id_contrato = ? AND numero_folha > ?")->execute([$id_contrato, $numero_folha]);
            self::$pdo->prepare("UPDATE contrato_folhas SET numero_folha = numero_folha - 1 WHERE id_contrato = ? AND numero_folha > ?")->execute([$id_contrato, $numero_folha]);

            $sqlContrato = "SELECT valor_total, qtd_folhas FROM contratos WHERE id = ?";
            $stmtC = self::$pdo->prepare($sqlContrato);
            $stmtC->execute([$id_contrato]);
            $contrato = $stmtC->fetch();

            $nova_qtd_folhas = $contrato['qtd_folhas'] - 1;

            if ($nova_qtd_folhas > 0) {
                $novo_valor_por_folha = $contrato['valor_total'] / $nova_qtd_folhas;
                self::$pdo->prepare("UPDATE contrato_folhas SET valor_folha = ? WHERE id_contrato = ?")->execute([$novo_valor_por_folha, $id_contrato]);
            }

            self::$pdo->prepare("UPDATE contratos SET qtd_folhas = ? WHERE id = ?")->execute([$nova_qtd_folhas, $id_contrato]);

            self::$pdo->commit();
            return true;
        } catch (Exception $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            error_log("Erro (Contrato -> excluirFolha Contrato {$id_contrato}, Folha {$numero_folha}): " . $e->getMessage());
            return false;
        }
    }

    public function adicionarFolha($id_contrato)
    {
        try {
            self::$pdo->beginTransaction();

            $sqlContrato = "SELECT valor_total, qtd_folhas FROM contratos WHERE id = ?";
            $stmtContrato = self::$pdo->prepare($sqlContrato);
            $stmtContrato->execute([$id_contrato]);
            $contrato = $stmtContrato->fetch();

            if (!$contrato) {
                throw new Exception("Contrato nao encontrado.");
            }

            $nova_qtd_folhas = $contrato['qtd_folhas'] + 1;
            $novo_valor_por_folha = $contrato['valor_total'] / $nova_qtd_folhas;

            $sqlMaxFolha = "SELECT MAX(numero_folha) as max_folha FROM contrato_folhas WHERE id_contrato = ?";
            $stmtMax = self::$pdo->prepare($sqlMaxFolha);
            $stmtMax->execute([$id_contrato]);
            $resultado = $stmtMax->fetch();
            $nova_folha = ($resultado['max_folha'] ?? 0) + 1;

            $sqlNovaFolha = "INSERT INTO contrato_folhas (id_contrato, numero_folha, valor_folha) VALUES (?, ?, ?)";
            self::$pdo->prepare($sqlNovaFolha)->execute([$id_contrato, $nova_folha, $novo_valor_por_folha]);

            $sqlAtualizaFolhas = "UPDATE contrato_folhas SET valor_folha = ? WHERE id_contrato = ?";
            self::$pdo->prepare($sqlAtualizaFolhas)->execute([$novo_valor_por_folha, $id_contrato]);

            $sqlUpdContrato = "UPDATE contratos SET qtd_folhas = ? WHERE id = ?";
            self::$pdo->prepare($sqlUpdContrato)->execute([$nova_qtd_folhas, $id_contrato]);

            self::$pdo->commit();
            return true;
        } catch (Exception $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            error_log("Erro (Contrato -> adicionarFolha ID {$id_contrato}): " . $e->getMessage());
            return false;
        }
    }

    public function atualizarValorFolha($id_contrato, $numero_folha, $novo_valor)
    {
        try {
            self::$pdo->beginTransaction();

            $sqlUpdFolha = "UPDATE contrato_folhas SET valor_folha = ?, modificado_manualmente = 1 WHERE id_contrato = ? AND numero_folha = ?";
            self::$pdo->prepare($sqlUpdFolha)->execute([$novo_valor, $id_contrato, $numero_folha]);

            $sqlContrato = "SELECT valor_total FROM contratos WHERE id = ?";
            $stmtC = self::$pdo->prepare($sqlContrato);
            $stmtC->execute([$id_contrato]);
            $contrato = $stmtC->fetch();

            $sqlSomaTravadas = "SELECT SUM(valor_folha) as total_travado FROM contrato_folhas WHERE id_contrato = ? AND modificado_manualmente = 1";
            $stmtSoma = self::$pdo->prepare($sqlSomaTravadas);
            $stmtSoma->execute([$id_contrato]);
            $soma_travadas = $stmtSoma->fetch()['total_travado'] ?? 0;

            $sqlCountLivres = "SELECT COUNT(*) as qtd_livres FROM contrato_folhas WHERE id_contrato = ? AND modificado_manualmente = 0";
            $stmtCount = self::$pdo->prepare($sqlCountLivres);
            $stmtCount->execute([$id_contrato]);
            $qtd_livres = $stmtCount->fetch()['qtd_livres'] ?? 0;

            if ($qtd_livres > 0) {
                $valor_restante = $contrato['valor_total'] - $soma_travadas;

                if ($valor_restante < 0) {
                    $valor_restante = 0;
                }

                $valor_rateado = $valor_restante / $qtd_livres;

                $sqlUpdLivres = "UPDATE contrato_folhas SET valor_folha = ? WHERE id_contrato = ? AND modificado_manualmente = 0";
                self::$pdo->prepare($sqlUpdLivres)->execute([$valor_rateado, $id_contrato]);
            }

            self::$pdo->commit();
            return true;
        } catch (Exception $e) {
            if (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            error_log("Erro (Contrato -> atualizarValorFolha Contrato {$id_contrato}, Folha {$numero_folha}): " . $e->getMessage());
            return false;
        }
    }
}
