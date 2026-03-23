<?php
require_once ROOT_PATH . '/src/Models/Contrato.php';

class ContratoController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        $contratoModel = new Contrato();
        $contratos = $contratoModel->listarTodos();

        $this->view('contratos/index', ['contratos' => $contratos]);
    }

    public function cadastrar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token']);

            $titulo = $_POST['titulo'] ?? 'Contrato Sem Título';
            $valor_total = (float)($_POST['valor_total'] ?? 0);
            $qtd_folhas = (int)($_POST['qtd_folhas'] ?? 1);

            $produtos = [];
            if (isset($_POST['produto_nome'])) {
                for ($i = 0; $i < count($_POST['produto_nome']); $i++) {
                    $produtos[] = [
                        'nome'  => $_POST['produto_nome'][$i],
                        'marca' => $_POST['produto_marca'][$i] ?? '',
                        'unidade' => $_POST['produto_unidade'][$i] ?? 'UN',
                        'quantidade' => (int)$_POST['produto_qtd'][$i],
                        'valor_unitario' => (float)$_POST['produto_valor'][$i]
                    ];
                }
            }

            $contratoModel = new Contrato();
            if ($contratoModel->salvarContratoCompleto($titulo, $valor_total, $qtd_folhas, $produtos)) {
                registrar_log(Model::getConexao(), 'CADASTRO_CONTRATO', "Novo contrato '$titulo' cadastrado.");
                redirect('/contrato');
                exit;
            } else {
                $erro = "Erro ao salvar contrato.";
            }
        }

        $this->view('contratos/cadastrar');
    }

    public function ver($id)
    {
        if (!$id) {
            redirect('/contrato');
            exit;
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->buscarPorId($id);

        if (!$contrato) {
            die("<h1>Erro 404</h1><p>Contrato não encontrado.</p>");
        }

        $produtos = $contratoModel->buscarProdutos($id);
        $folhas = $contratoModel->buscarFolhas($id);

        $aba_ativa = isset($_GET['folha']) ? (int)$_GET['folha'] : 1;

        $this->view('contratos/ver', [
            'contrato' => $contrato,
            'produtos' => $produtos,
            'folhas' => $folhas,
            'aba_ativa' => $aba_ativa
        ]);
    }

    public function excluir($id)
    {
        if ($_SESSION['usuario_tipo'] !== 'admin') {
            die("<h1>Acesso Negado</h1><p>Apenas administradores podem excluir contratos.</p>");
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->buscarPorId($id);

        if ($contrato) {
            if ($contratoModel->excluir($id)) {
                registrar_log(Model::getConexao(), 'EXCLUSAO_CONTRATO', "O Contrato '{$contrato['titulo']}' (ID: $id) foi excluído.");
            }
        }

        redirect('/contrato');
        exit;
    }

    public function editar($id)
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        $contratoModel = new Contrato();
        $contrato = $contratoModel->buscarPorId($id);

        if (!$contrato) {
            die("<h1>Erro 404</h1><p>Contrato não encontrado.</p>");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token']);

            $titulo = $_POST['titulo'] ?? 'Contrato Sem Título';
            $valor_total = (float)($_POST['valor_total'] ?? 0);
            $qtd_folhas = (int)($_POST['qtd_folhas'] ?? 1);

            $produtos = [];
            if (isset($_POST['produto_nome'])) {
                for ($i = 0; $i < count($_POST['produto_nome']); $i++) {
                    $produtos[] = [
                        'nome' => $_POST['produto_nome'][$i],
                        'marca' => $_POST['produto_marca'][$i] ?? '',
                        'unidade' => $_POST['produto_unidade'][$i] ?? 'UN',
                        'quantidade' => (int)$_POST['produto_qtd'][$i],
                        'valor_unitario' => (float)$_POST['produto_valor'][$i]
                    ];
                }
            }

            if ($contratoModel->atualizarContratoCompleto($id, $titulo, $valor_total, $qtd_folhas, $produtos)) {
                registrar_log(Model::getConexao(), 'EDITA_CONTRATO', "Contrato #$id editado com sucesso.");
                redirect('/contrato/ver/' . $id);
                exit;
            } else {
                $erro = "Erro ao atualizar contrato.";
            }
        }

        $produtos = $contratoModel->buscarProdutos($id);

        $this->view('contratos/editar', [
            'contrato' => $contrato,
            'produtos' => $produtos
        ]);
    }

    public function editar_produto($id_produto)
    {
        $model = new Contrato();
        $produto = $model->buscarProdutoPorId($id_produto);

        if (!$produto) {
            die("Produto não encontrado.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome  = $_POST['nome_produto'];
            $marca = $_POST['marca'];
            $unidade = $_POST['unidade'] ?? 'UN';
            $qtd   = (int)$_POST['quantidade'];
            $valor = (float)$_POST['valor_unitario'];

            $model->atualizarProduto($id_produto, $nome, $marca, $unidade, $qtd, $valor);

            redirect('/contrato/ver/' . $produto['id_contrato']);
            exit;
        }

        $this->view('contratos/produto_form', ['produto' => $produto, 'acao' => 'editar']);
    }

    public function excluir_produto($id_produto)
    {
        $model = new Contrato();
        $produto = $model->buscarProdutoPorId($id_produto);

        if ($produto) {
            $model->excluirProduto($id_produto);
            redirect('/contrato/ver/' . $produto['id_contrato']);
        }
        exit;
    }

    public function adicionar_produto_inline($id_contrato)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numero_folha = (int)$_POST['numero_folha'];
            $nome  = $_POST['nome_produto'];
            $marca = $_POST['marca'];
            $unidade = $_POST['unidade'] ?? 'UN';
            $qtd   = (int)$_POST['quantidade'];
            $valor = (float)$_POST['valor_unitario'];

            $model = new Contrato();
            $model->adicionarProdutoUnico($id_contrato, $numero_folha, $nome, $marca, $unidade, $qtd, $valor);

            redirect('/contrato/ver/' . $id_contrato . '?folha=' . $numero_folha);
            exit;
        }
    }
    public function excluir_folha($id_contrato, $numero_folha)
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        $model = new Contrato();
        $contrato = $model->buscarPorId($id_contrato);

        if ($contrato) {
            if ($contrato['qtd_folhas'] <= 1) {
                echo "<script>alert('Não é possível apagar a única folha do contrato. Exclua o contrato inteiro se necessário.'); window.location.href='/contrato/ver/{$id_contrato}';</script>";
                exit;
            }

            if ($model->excluirFolha($id_contrato, $numero_folha)) {
                registrar_log(Model::getConexao(), 'EXCLUSAO_FOLHA', "Folha $numero_folha do Contrato #$id_contrato excluída.");
            }
        }

        redirect('/contrato/ver/' . $id_contrato . '?folha=1');
        exit;
    }
    public function adicionar_folha($id_contrato)
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        $model = new Contrato();
        $contrato = $model->buscarPorId($id_contrato);

        if ($contrato) {
            if ($model->adicionarFolha($id_contrato)) {
                registrar_log(Model::getConexao(), 'NOVA_FOLHA', "Nova folha adicionada ao Contrato #$id_contrato.");
            }
        }

        redirect('/contrato/ver/' . $id_contrato);
        exit;
    }
    public function editar_valor_folha($id_contrato)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numero_folha = (int)$_POST['numero_folha'];
            $novo_valor = (float)$_POST['novo_valor'];

            $model = new Contrato();
            $model->atualizarValorFolha($id_contrato, $numero_folha, $novo_valor);

            redirect('/contrato/ver/' . $id_contrato . '?folha=' . $numero_folha);
            exit;
        }
    }
}
