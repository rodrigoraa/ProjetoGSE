<?php
require_once ROOT_PATH . '/src/Core/Model.php';

class Agenda extends Model
{
    public function listarAlertasPainel()
    {
        $sql = "SELECT a.*, u.nome as autor_nome 
                FROM agenda_avisos a 
                LEFT JOIN usuarios u ON a.usuario_id = u.id 
                WHERE a.data_aviso BETWEEN date('now', 'localtime') AND date('now', 'localtime', '+7 days') 
                ORDER BY a.data_aviso ASC";

        return self::$pdo->query($sql)->fetchAll();
    }

    public function listarProximosAvisos()
    {
        $sql = "SELECT a.*, u.nome as autor_nome 
                FROM agenda_avisos a 
                LEFT JOIN usuarios u ON a.usuario_id = u.id 
                WHERE a.data_aviso >= date('now', 'localtime') 
                ORDER BY a.data_aviso ASC, a.criado_em DESC";

        return self::$pdo->query($sql)->fetchAll();
    }

    public function adicionar($usuario_id, $data_aviso, $titulo, $descricao)
    {
        $sql = "INSERT INTO agenda_avisos (usuario_id, data_aviso, titulo, descricao) VALUES (?, ?, ?, ?)";
        return self::$pdo->prepare($sql)->execute([$usuario_id, $data_aviso, $titulo, $descricao]);
    }

    public function existeDuplicadoRecente($usuario_id, $data_aviso, $titulo, $descricao)
    {
        $sql = "SELECT id
                FROM agenda_avisos
                WHERE usuario_id = ?
                  AND data_aviso = ?
                  AND titulo = ?
                  AND COALESCE(descricao, '') = ?
                  AND criado_em >= datetime('now', '-2 minutes')
                LIMIT 1";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$usuario_id, $data_aviso, $titulo, $descricao]);

        return (bool)$stmt->fetch();
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT a.*, u.nome as autor_nome 
                FROM agenda_avisos a 
                LEFT JOIN usuarios u ON a.usuario_id = u.id 
                WHERE a.id = ?";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function excluir($id_aviso, $usuario_id_logado, $usuario_tipo_logado)
    {
        if ($usuario_tipo_logado === 'admin') {
            $sql = "DELETE FROM agenda_avisos WHERE id = ?";
            return self::$pdo->prepare($sql)->execute([$id_aviso]);
        }

        $sql = "DELETE FROM agenda_avisos WHERE id = ? AND usuario_id = ?";
        return self::$pdo->prepare($sql)->execute([$id_aviso, $usuario_id_logado]);
    }

    public function atualizar($id_aviso, $usuario_id_logado, $usuario_tipo_logado, $data_aviso, $titulo, $descricao)
    {
        if ($usuario_tipo_logado === 'admin') {
            $sql = "UPDATE agenda_avisos
                    SET data_aviso = ?, titulo = ?, descricao = ?
                    WHERE id = ?";
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$data_aviso, $titulo, $descricao, $id_aviso]);

            return $stmt->rowCount() > 0;
        }

        $sql = "UPDATE agenda_avisos
                SET data_aviso = ?, titulo = ?, descricao = ?
                WHERE id = ? AND usuario_id = ?";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$data_aviso, $titulo, $descricao, $id_aviso, $usuario_id_logado]);

        return $stmt->rowCount() > 0;
    }
}
