<?php
require_once ROOT_PATH . '/src/Models/Usuario.php';

class UsuarioController extends Controller
{
    public function index()
    {
        $this->verificarAdmin();

        $usuarioModel = new Usuario();
        $lista = $usuarioModel->listar();

        $this->view('usuarios/index', [
            'funcionarios' => $lista
        ]);
    }

    public function cadastrar()
    {
        $this->verificarAdmin();
        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $usuarioModel = new Usuario();
            $recebeAvisosEmail = isset($_POST['recebe_avisos_email']) ? 1 : 0;

            if ($usuarioModel->cadastrar($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['tipo'], $recebeAvisosEmail)) {
                registrar_log(Model::getConexao(), "Usuario - Criar", "Criou: " . $_POST['nome']);

                definir_flash(
                    'sucesso',
                    'Usuário cadastrado com sucesso',
                    'O novo usuário foi criado e já pode acessar o sistema.',
                    'Confira se o tipo de acesso está correto antes de encerrar.'
                );
                redirect('/usuario');
                exit;
            }

            $mensagem = alerta_html(
                'erro',
                'Não foi possível cadastrar o usuário',
                'Este e-mail pode já estar sendo usado por outra conta ou algum dado informado é inválido.',
                'Revise nome, e-mail, senha e tipo de acesso antes de tentar novamente.'
            );
        }
        $this->view('usuarios/cadastrar', ['mensagem' => $mensagem]);
    }

    public function editar($id)
    {
        $this->verificarAdmin();
        if (!$id) {
            redirect('/usuario');
            exit;
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->buscarPorId($id);
        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $novaSenha = !empty($_POST['senha']) ? $_POST['senha'] : null;
            $recebeAvisosEmail = isset($_POST['recebe_avisos_email']) ? 1 : 0;

            if ($usuarioModel->atualizar($id, $_POST['nome'], $_POST['email'], $_POST['tipo'], $recebeAvisosEmail, $novaSenha)) {
                registrar_log(Model::getConexao(), "Usuario - Editar", "Editou ID: $id");

                definir_flash(
                    'sucesso',
                    'Usuário atualizado com sucesso',
                    'As informações do usuário foram salvas.',
                    'Se você alterou o tipo de acesso, a nova permissão já vale para os próximos acessos.'
                );
                redirect('/usuario');
                exit;
            }

            $mensagem = alerta_html(
                'erro',
                'Não foi possível atualizar o usuário',
                'As alterações não foram salvas.',
                'Confira se o e-mail informado não está em uso por outra conta e tente novamente.'
            );
        }
        $this->view('usuarios/editar', ['user' => $user, 'mensagem' => $mensagem]);
    }

    public function excluir($id)
    {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/usuario');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        if ($id == $_SESSION['usuario_id']) {
            definir_flash(
                'erro',
                'Não é permitido apagar a própria conta',
                'O sistema bloqueia a exclusão do usuário atualmente conectado.',
                'Se você realmente precisar remover essa conta, entre com outro administrador.'
            );
            redirect('/usuario');
            exit;
        }

        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->excluir($id);

        if ($resultado === true) {
            registrar_log(Model::getConexao(), "Usuario - Excluir", "Excluiu ID: $id");
            definir_flash(
                'sucesso',
                'Usuário apagado com sucesso',
                'A conta selecionada foi removida do sistema.',
                'Confirme se não havia mais necessidade de acesso para esse usuário.'
            );
        } elseif ($resultado === 'tem_registros') {
            definir_flash(
                'erro',
                'Não foi possível apagar este usuário',
                'Essa conta possui registros vinculados no sistema e, por isso, não pode ser excluída.',
                'Se necessário, mantenha a conta inativa ou reavalie os vínculos existentes antes de tentar novamente.'
            );
        } else {
            definir_flash(
                'erro',
                'Não foi possível apagar o usuário',
                'O sistema encontrou um problema inesperado durante a exclusão.',
                'Tente novamente em alguns instantes. Se continuar falhando, avise o suporte.'
            );
        }

        redirect('/usuario');
        exit;
    }

    public function perfil()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        $usuarioModel = new Usuario();
        $id = $_SESSION['usuario_id'];
        $user = $usuarioModel->buscarPorId($id);
        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            if (!password_verify($_POST['senha_atual'], $user['senha'])) {
                $mensagem = alerta_html(
                    'erro',
                    'Senha atual incorreta',
                    'A senha informada para confirmar as alterações não confere com a sua conta.',
                    'Digite novamente a senha usada no login para salvar o perfil.'
                );
            } elseif (!empty($_POST['nova_senha']) && $_POST['nova_senha'] !== ($_POST['confirma_senha'] ?? '')) {
                $mensagem = alerta_html(
                    'erro',
                    'A nova senha não foi confirmada corretamente',
                    'Os campos de nova senha e confirmação estão diferentes.',
                    'Repita a mesma senha nos dois campos para atualizar o acesso.'
                );
            } elseif (!empty($_POST['nova_senha']) && strlen($_POST['nova_senha']) < 6) {
                $mensagem = alerta_html(
                    'erro',
                    'A nova senha está muito curta',
                    'Para sua segurança, a nova senha precisa ter pelo menos 6 caracteres.',
                    'Escolha uma senha maior e tente novamente.'
                );
            } else {
                $novaSenha = !empty($_POST['nova_senha']) ? $_POST['nova_senha'] : null;

                if ($usuarioModel->atualizarPerfil($id, $_POST['nome'], $_POST['email'], $novaSenha)) {
                    $_SESSION['usuario_nome'] = $_POST['nome'];
                    $mensagem = alerta_html(
                        'sucesso',
                        'Perfil atualizado com sucesso',
                        'Suas informações foram salvas.',
                        'Se você trocou a senha, use a nova senha no próximo login.'
                    );
                    $user = $usuarioModel->buscarPorId($id);
                } else {
                    $mensagem = alerta_html(
                        'erro',
                        'Não foi possível atualizar o perfil',
                        'O sistema não conseguiu salvar suas alterações agora.',
                        'Revise os dados informados e tente novamente.'
                    );
                }
            }
        }

        $this->view('usuarios/perfil', ['user' => $user, 'mensagem' => $mensagem]);
    }

    private function verificarAdmin()
    {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
            redirect('/painel');
            exit;
        }
    }
}
