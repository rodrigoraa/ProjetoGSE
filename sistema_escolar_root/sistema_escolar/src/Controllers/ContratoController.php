<?php
require_once ROOT_PATH . '/src/Models/Contrato.php';

class ContratoController extends Controller
{
    private $contratoModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }
        $this->contratoModel = new Contrato();
    }

    public function index()
    {
        $contratos = $this->contratoModel->listarTodos();
        $this->view('contratos/index', ['contratos' => $contratos]);
    }

    public function cadastrar()
    {
        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $titulo = trim($_POST['titulo'] ?? '');
            if ($titulo === '') {
                $titulo = 'Contrato Sem Titulo';
            }

            $valor_total = (float)($_POST['valor_total'] ?? 0);
            $qtd_folhas = (int)($_POST['qtd_folhas'] ?? 1);

            if ($valor_total <= 0 || $qtd_folhas <= 0) {
                $erro = "O valor total e a quantidade de folhas devem ser maiores que zero.";
            } else {
                $produtos = $this->processarProdutosPost();

                if ($this->contratoModel->salvarContratoCompleto($titulo, $valor_total, $qtd_folhas, $produtos)) {
                    registrar_log(Model::getConexao(), 'CADASTRO_CONTRATO', "Novo contrato '$titulo' cadastrado.");
                    redirect('/contrato');
                    exit;
                }
                $erro = "Erro interno ao salvar no banco de dados.";
            }
        }

        $this->view('contratos/cadastrar', ['erro' => $erro]);
    }

    public function editar($id)
    {
        $id = (int)$id;
        $contrato = $this->contratoModel->buscarPorId($id);

        if (!$contrato) {
            $this->mostrarErro404("Contrato nao encontrado.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $titulo = trim($_POST['titulo'] ?? '');
            if ($titulo === '') {
                $titulo = 'Contrato Sem Titulo';
            }

            $valor_total = (float)($_POST['valor_total'] ?? 0);
            $qtd_folhas = (int)($_POST['qtd_folhas'] ?? 1);

            if ($valor_total > 0 && $qtd_folhas > 0) {
                $produtos = $this->processarProdutosPost();

                if ($this->contratoModel->atualizarContratoCompleto($id, $titulo, $valor_total, $qtd_folhas, $produtos)) {
                    registrar_log(Model::getConexao(), 'EDITAR_CONTRATO', "Contrato #{$id} atualizado.");
                    redirect("/contrato/ver/{$id}");
                    exit;
                }
            }
        }

        $this->view('contratos/editar', [
            'contrato' => $contrato,
            'produtos' => $this->contratoModel->buscarProdutos($id)
        ]);
    }

    public function excluir($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/contrato');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        if ($_SESSION['usuario_tipo'] !== 'admin') {
            redirect('/contrato');
            exit;
        }

        $id = (int)$id;
        $this->contratoModel->excluir($id);
        redirect('/contrato');
        exit;
    }

    public function ver($id)
    {
        $id = (int)$id;
        $contrato = $this->contratoModel->buscarPorId($id);

        if (!$contrato) {
            $this->mostrarErro404("Contrato nao encontrado.");
        }

        $produtos = $this->contratoModel->buscarProdutos($id);
        $folhas = $this->contratoModel->buscarFolhas($id);
        $aba_ativa = isset($_GET['folha']) ? (int)$_GET['folha'] : 1;

        $this->view('contratos/ver', [
            'contrato' => $contrato,
            'produtos' => $produtos,
            'folhas' => $folhas,
            'aba_ativa' => $aba_ativa
        ]);
    }

    public function adicionar_folha($id_contrato)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect("/contrato/ver/" . (int)$id_contrato);
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');
        $id_contrato = (int)$id_contrato;
        $this->contratoModel->adicionarFolha($id_contrato);
        redirect("/contrato/ver/{$id_contrato}");
        exit;
    }

    public function excluir_folha($id_contrato, $numero_folha)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect("/contrato/ver/" . (int)$id_contrato);
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');
        $id_contrato = (int)$id_contrato;
        $numero_folha = (int)$numero_folha;
        $this->contratoModel->excluirFolha($id_contrato, $numero_folha);
        redirect("/contrato/ver/{$id_contrato}");
        exit;
    }

    public function adicionar_produto_inline($id_contrato)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $id_contrato = (int)$id_contrato;
            $numero_folha = (int)$_POST['numero_folha'];
            $qtd = (int)$_POST['quantidade'];
            $valor_unit = (float)$_POST['valor_unitario'];

            $this->contratoModel->adicionarProdutoUnico(
                $id_contrato,
                $numero_folha,
                trim($_POST['nome_produto']),
                trim($_POST['marca']),
                $_POST['unidade'] ?? 'UN',
                $qtd,
                $valor_unit
            );

            redirect("/contrato/ver/{$id_contrato}?folha={$numero_folha}");
            exit;
        }
    }

    public function editar_produto($id_produto)
    {
        $id_produto = (int)$id_produto;
        $produto = $this->contratoModel->buscarProdutoPorIdComContrato($id_produto);

        if (!$produto) {
            $this->mostrarErro404("Produto nao encontrado.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $quantidade = (int)($_POST['quantidade'] ?? 0);
            $valor_unitario = (float)($_POST['valor_unitario'] ?? 0);

            if ($quantidade > 0 && $valor_unitario >= 0) {
                $this->contratoModel->atualizarProduto(
                    $id_produto,
                    trim($_POST['nome_produto'] ?? ''),
                    trim($_POST['marca'] ?? ''),
                    $_POST['unidade'] ?? 'UN',
                    $quantidade,
                    $valor_unitario
                );

                $folha = (int)($produto['numero_folha'] ?? 1);
                redirect("/contrato/ver/{$produto['id_contrato']}?folha={$folha}");
                exit;
            }
        }

        $this->view('contratos/produto_form', [
            'acao' => 'editar',
            'produto' => $produto
        ]);
    }

    public function excluir_produto($id_produto)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/contrato');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        $id_produto = (int)$id_produto;
        $produto = $this->contratoModel->buscarProdutoPorId($id_produto);

        if ($produto) {
            $this->contratoModel->excluirProduto($id_produto);
            $folha = (int)($produto['numero_folha'] ?? 1);
            redirect("/contrato/ver/{$produto['id_contrato']}?folha={$folha}");
            exit;
        }

        redirect('/contrato');
        exit;
    }

    public function editar_valor_folha($id_contrato)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $id_contrato = (int)$id_contrato;
            $numero_folha = (int)$_POST['numero_folha'];
            $novo_valor = (float)$_POST['novo_valor'];

            $this->contratoModel->atualizarValorFolha($id_contrato, $numero_folha, $novo_valor);

            redirect("/contrato/ver/{$id_contrato}?folha={$numero_folha}");
            exit;
        }
    }

    private function processarProdutosPost()
    {
        $produtos = [];
        if (isset($_POST['produto_nome']) && is_array($_POST['produto_nome'])) {
            foreach ($_POST['produto_nome'] as $i => $nome) {
                if (!empty($nome)) {
                    $produtos[] = [
                        'nome' => trim($nome),
                        'marca' => trim($_POST['produto_marca'][$i] ?? ''),
                        'unidade' => $_POST['produto_unidade'][$i] ?? 'UN',
                        'quantidade' => (int)$_POST['produto_qtd'][$i],
                        'valor_unitario' => (float)$_POST['produto_valor'][$i]
                    ];
                }
            }
        }
        return $produtos;
    }

    private function mostrarErro404($msg)
    {
        header("HTTP/1.0 404 Not Found");
        die("<h1>Erro 404</h1><p>" . e($msg) . "</p><a href='/contrato'>Voltar</a>");
    }
}
