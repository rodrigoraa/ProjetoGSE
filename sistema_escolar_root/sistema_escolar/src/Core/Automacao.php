<?php
require_once ROOT_PATH . '/src/Core/EmailService.php';

class Automacao
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = \src\Core\Database::getConnection();
        } catch (Exception $e) {
            $this->pdo = null;
        }
    }

    public function verificarEnviosDiarios()
    {
        if (!$this->pdo) {
            return;
        }

        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS configuracoes (chave TEXT PRIMARY KEY, valor TEXT)");

            $stmt = $this->pdo->query("SELECT valor FROM configuracoes WHERE chave = 'ultimo_disparo_email'");
            $ultimoEnvio = $stmt->fetchColumn();
            $hoje = date('Y-m-d');

            if ($ultimoEnvio == $hoje) {
                return;
            }

            $this->enviarAvisosDva();
            $this->enviarAvisosCertidoes();

            $stmtCheck = $this->pdo->prepare("SELECT 1 FROM configuracoes WHERE chave = 'ultimo_disparo_email'");
            $stmtCheck->execute();

            if ($stmtCheck->fetch()) {
                $sql = "UPDATE configuracoes SET valor = ? WHERE chave = 'ultimo_disparo_email'";
            } else {
                $sql = "INSERT INTO configuracoes (chave, valor) VALUES ('ultimo_disparo_email', ?)";
            }
            $this->pdo->prepare($sql)->execute([$hoje]);
        } catch (PDOException $e) {
            error_log("Erro (Automacao - verificarEnvios): Falha na execução - " . $e->getMessage());
        }
    }

    private function enviarAvisosDva()
    {
        try {
            $hoje = date('Y-m-d');
            $dataLimite = date('Y-m-d', strtotime('+30 days'));

            $sql = "SELECT a.nome_completo, t.nome_turma, d.data_vencimento 
                    FROM dvas d
                    JOIN alunos a ON d.id_aluno = a.id
                    LEFT JOIN turmas t ON a.id_turma = t.id
                    WHERE d.data_vencimento BETWEEN ? AND ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$hoje, $dataLimite]);
            $lista = $stmt->fetchAll();

            if (count($lista) > 0) {
                $html = "<h2>⚠️ Aviso de Vencimento de DVA</h2>";
                $html .= "<p>As seguintes DVAs vencem em 30 dias ($dataLimite):</p><ul>";
                foreach ($lista as $l) {
                    $html .= "<li><strong>{$l['nome_completo']}</strong> - " . ($l['nome_turma'] ?? 'Sem Turma') . "</li>";
                }
                $html .= "</ul><p>Acesse o sistema para renovar.</p>";

                $this->dispararEmails($html, '⚠️ Alerta de DVAs - Sistema Escolar');
            }
        } catch (PDOException $e) {
            error_log("Erro (Automacao - DVA): Falha ao buscar dados - " . $e->getMessage());
        }
    }

    private function enviarAvisosCertidoes()
    {
        try {
            $hoje = date('Y-m-d');
            $dataLimite = date('Y-m-d', strtotime('+30 days'));

            $sql = "SELECT fornecedor, tipo_certidao FROM certidoes WHERE data_vencimento BETWEEN ? AND ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$hoje, $dataLimite]);
            $lista = $stmt->fetchAll();

            if (count($lista) > 0) {
                $html = "<h2>⚠️ Aviso de Vencimento de Certidões</h2>";
                $html .= "<p>As seguintes certidões da escola vencem em 30 dias ($dataLimite):</p><ul>";
                foreach ($lista as $c) {
                    $html .= "<li><strong>{$c['tipo_certidao']}</strong> ({$c['fornecedor']})</li>";
                }
                $html .= "</ul><p>Acesse o sistema para renovar ou atualizar as certidões.</p>";

                $this->dispararEmails($html, '⚠️ Alerta de Certidões - Sistema Escolar');
            }
        } catch (PDOException $e) {
            error_log("Erro (Automacao - Certidoes): Falha ao buscar dados - " . $e->getMessage());
        }
    }

    private function dispararEmails($html, $assunto)
    {
        try {
            $sqlUsers = "SELECT email, nome FROM usuarios WHERE tipo IN ('admin', 'funcionario')";
            $destinatarios = $this->pdo->query($sqlUsers)->fetchAll();
            $emailService = new EmailService();

            foreach ($destinatarios as $user) {
                if (!empty($user['email'])) {
                    $emailService->enviar($user['email'], $assunto, $html);
                }
            }
        } catch (PDOException $e) {
            error_log("Erro (Automacao - dispararEmails): Falha ao buscar destinatários - " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Erro (Automacao - dispararEmails): Falha no envio pelo EmailService - " . $e->getMessage());
        }
    }
}
