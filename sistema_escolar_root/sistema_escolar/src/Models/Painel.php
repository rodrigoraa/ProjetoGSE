<?php
require_once ROOT_PATH . '/src/Core/Model.php';

class Painel extends Model
{

    public function getTotalAlunos()
    {
        return self::$pdo->query("SELECT COUNT(id) FROM alunos")->fetchColumn();
    }

    public function getTotalSemDva()
    {
        return self::$pdo->query("SELECT COUNT(id) FROM alunos WHERE id NOT IN (SELECT id_aluno FROM dvas)")->fetchColumn();
    }

    public function getDvasVencidas()
    {
        $hoje = date('Y-m-d');
        $sql = "SELECT a.nome_completo, t.nome_turma, d.data_vencimento, d.id AS dva_id, a.id AS aluno_id 
                FROM dvas d 
                JOIN alunos a ON d.id_aluno = a.id 
                LEFT JOIN turmas t ON a.id_turma = t.id 
                WHERE d.data_vencimento < ? ORDER BY d.data_vencimento ASC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$hoje]);
        return $stmt->fetchAll();
    }

    public function getDvasAVencer()
    {
        $hoje = date('Y-m-d');
        $sql = "SELECT a.nome_completo, t.nome_turma, d.data_vencimento, d.id AS dva_id, a.id AS aluno_id 
                FROM dvas d 
                JOIN alunos a ON d.id_aluno = a.id 
                LEFT JOIN turmas t ON a.id_turma = t.id 
                WHERE d.data_vencimento >= ? AND d.data_vencimento <= date('now', '+30 days') 
                ORDER BY d.data_vencimento ASC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$hoje]);
        return $stmt->fetchAll();
    }

    public function getDvasVigentes()
    {
        $sql = "SELECT a.nome_completo, t.nome_turma, d.data_vencimento, d.id AS dva_id, a.id AS aluno_id 
                FROM dvas d 
                JOIN alunos a ON d.id_aluno = a.id 
                LEFT JOIN turmas t ON a.id_turma = t.id 
                WHERE d.data_vencimento > date('now', '+30 days') 
                ORDER BY a.nome_completo";
        return self::$pdo->query($sql)->fetchAll();
    }

    public function getListaAlunosSemDva()
    {
        $sql = "SELECT a.id, a.nome_completo, t.nome_turma 
            FROM alunos a
            LEFT JOIN turmas t ON a.id_turma = t.id
            WHERE a.id NOT IN (SELECT id_aluno FROM dvas)
            ORDER BY a.nome_completo ASC";

        return self::$pdo->query($sql)->fetchAll();
    }
}
