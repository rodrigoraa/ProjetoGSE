<?php
require_once ROOT_PATH . '/src/Core/Model.php';

class Usuario extends Model
{

    public function buscarPorEmail($email)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function buscarPorId($id)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function listar()
    {
        return self::$pdo->query("SELECT * FROM usuarios ORDER BY nome")->fetchAll();
    }
    public function cadastrar($nome, $email, $senha, $tipo)
    {
        try {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([$nome, $email, $hash, $tipo]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function atualizar($id, $nome, $email, $tipo, $novaSenha = null)
    {
        try {
            $sql = "UPDATE usuarios SET nome=?, email=?, tipo=?";
            $params = [$nome, $email, $tipo];

            if ($novaSenha) {
                $sql .= ", senha=?";
                $params[] = password_hash($novaSenha, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id=?";
            $params[] = $id;

            $stmt = self::$pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            return false;
        }
    }

    public function atualizarPerfil($id, $nome, $email, $novaSenha = null)
    {
        try {
            $sql = "UPDATE usuarios SET nome=?, email=?";
            $params = [$nome, $email];

            if ($novaSenha) {
                $sql .= ", senha=?";
                $params[] = password_hash($novaSenha, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id=?";
            $params[] = $id;

            return self::$pdo->prepare($sql)->execute($params);
        } catch (Exception $e) {
            return false;
        }
    }

    public function excluir($id)
    {
        try {
            $stmt = self::$pdo->prepare("DELETE FROM usuarios WHERE id=?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return 'tem_registros';
            }
            return false;
        }
    }
}