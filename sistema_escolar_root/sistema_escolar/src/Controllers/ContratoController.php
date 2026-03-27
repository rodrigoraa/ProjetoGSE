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
                $erro = 'O valor total e a quantidade de folhas devem ser maiores que zero.';
            } else {
                $produtos = $this->processarProdutosPost();

                if (empty($produtos)) {
                    $erro = 'Informe pelo menos um produto valido para salvar o pedido.';
                } elseif ($this->contratoModel->salvarContratoCompleto($titulo, $valor_total, $qtd_folhas, $produtos)) {
                    definir_flash('sucesso', 'Pedido cadastrado', 'O novo pedido foi salvo com sucesso.');
                    registrar_log(Model::getConexao(), 'Contrato - Cadastrar', "Novo contrato '{$titulo}' cadastrado.");
                    redirect('/contrato');
                    exit;
                } else {
                    $erro = 'Erro interno ao salvar no banco de dados.';
                }
            }
        }

        $this->view('contratos/cadastrar', ['erro' => $erro]);
    }

    public function editar($id)
    {
        $id = (int)$id;
        $contrato = $this->contratoModel->buscarPorId($id);
        $erro = '';

        if (!$contrato) {
            $this->mostrarErro404('Contrato nao encontrado.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $titulo = trim($_POST['titulo'] ?? '');
            if ($titulo === '') {
                $titulo = 'Contrato Sem Titulo';
            }

            $valor_total = (float)($_POST['valor_total'] ?? 0);

            if ($valor_total > 0) {
                if ($this->contratoModel->atualizarDadosGerais($id, $titulo, $valor_total)) {
                    definir_flash('sucesso', 'Dados gerais atualizados', 'As informacoes principais do pedido foram salvas.');
                    registrar_log(Model::getConexao(), 'Contrato - Editar', "Contrato #{$id} atualizado para '{$titulo}'.");
                    redirect("/contrato/ver/{$id}");
                    exit;
                }

                $erro = 'Nao foi possivel salvar os dados gerais do pedido.';
            } else {
                $erro = 'O valor total do pedido deve ser maior que zero.';
            }

            $contrato = $this->contratoModel->buscarPorId($id);
        }

        $this->view('contratos/editar', [
            'contrato' => $contrato,
            'erro' => $erro
        ]);
    }

    public function excluir($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/contrato');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
            definir_flash('erro', 'Acesso negado', 'Somente administradores podem excluir pedidos.');
            redirect('/contrato');
            exit;
        }

        $id = (int)$id;
        $contrato = $this->contratoModel->buscarPorId($id);

        if ($this->contratoModel->excluir($id)) {
            $titulo = $contrato['titulo'] ?? 'Sem titulo';
            definir_flash('sucesso', 'Pedido excluido', 'O pedido selecionado foi removido com sucesso.');
            registrar_log(Model::getConexao(), 'Contrato - Excluir', "Contrato #{$id} ('{$titulo}') excluido.");
        } else {
            definir_flash('erro', 'Nao foi possivel excluir', 'O pedido nao pode ser removido neste momento.');
        }

        redirect('/contrato');
        exit;
    }

    public function ver($id)
    {
        $id = (int)$id;
        $contrato = $this->contratoModel->buscarPorId($id);

        if (!$contrato) {
            $this->mostrarErro404('Contrato nao encontrado.');
        }

        $produtos = $this->contratoModel->buscarProdutos($id);
        $folhas = $this->contratoModel->buscarFolhas($id);
        $aba_ativa = isset($_GET['folha']) ? (int)$_GET['folha'] : 1;

        if (!empty($folhas)) {
            $folhasDisponiveis = array_map(static function ($folha) {
                return (int)$folha['numero_folha'];
            }, $folhas);

            if (!in_array($aba_ativa, $folhasDisponiveis, true)) {
                $aba_ativa = (int)$folhasDisponiveis[0];
            }
        }

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
            redirect('/contrato/ver/' . (int)$id_contrato);
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');
        $id_contrato = (int)$id_contrato;

        if (!$this->contratoModel->buscarPorId($id_contrato)) {
            definir_flash('erro', 'Pedido nao encontrado', 'O pedido informado nao existe mais.');
            redirect('/contrato');
            exit;
        }

        if ($this->contratoModel->adicionarFolha($id_contrato)) {
            definir_flash('sucesso', 'Nova folha adicionada', 'Uma nova nota foi criada para este pedido.');
            registrar_log(Model::getConexao(), 'Contrato - Cadastrar Folha', "Adicionou nova folha ao contrato #{$id_contrato}.");
        } else {
            definir_flash('erro', 'Nao foi possivel adicionar a folha', 'Tente novamente em alguns instantes.');
        }

        redirect("/contrato/ver/{$id_contrato}");
        exit;
    }

    public function salvar_observacao_folha($id_contrato, $numero_folha)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/contrato/ver/' . (int)$id_contrato);
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        $id_contrato = (int)$id_contrato;
        $numero_folha = (int)$numero_folha;
        $observacao = trim((string)($_POST['observacao'] ?? ''));

        if (!$this->contratoModel->folhaExiste($id_contrato, $numero_folha)) {
            definir_flash('erro', 'Folha nao encontrada', 'A nota informada nao existe neste pedido.');
            redirect("/contrato/ver/{$id_contrato}");
            exit;
        }

        if ($this->contratoModel->atualizarObservacaoFolha($id_contrato, $numero_folha, $observacao)) {
            definir_flash('sucesso', 'Observacao salva', "As observacoes da nota {$numero_folha} foram atualizadas.");
            registrar_log(
                Model::getConexao(),
                'Contrato - Editar Folha',
                "Atualizou observacoes da folha {$numero_folha} do contrato #{$id_contrato}."
            );
        } else {
            definir_flash('erro', 'Nao foi possivel salvar', 'As observacoes da nota nao puderam ser atualizadas.');
        }

        redirect("/contrato/ver/{$id_contrato}?folha={$numero_folha}");
        exit;
    }

    public function excluir_folha($id_contrato, $numero_folha)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/contrato/ver/' . (int)$id_contrato);
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');
        $id_contrato = (int)$id_contrato;
        $numero_folha = (int)$numero_folha;

        if (!$this->contratoModel->folhaExiste($id_contrato, $numero_folha)) {
            definir_flash('erro', 'Folha nao encontrada', 'A nota informada nao existe neste pedido.');
        } elseif ($this->contratoModel->contarFolhas($id_contrato) <= 1) {
            definir_flash('erro', 'Acao bloqueada', 'O pedido precisa manter pelo menos uma folha.');
        } elseif ($this->contratoModel->excluirFolha($id_contrato, $numero_folha)) {
            definir_flash('sucesso', 'Folha excluida', "A folha {$numero_folha} foi removida do pedido.");
            registrar_log(
                Model::getConexao(),
                'Contrato - Excluir Folha',
                "Excluiu a folha {$numero_folha} do contrato #{$id_contrato}."
            );
        } else {
            definir_flash('erro', 'Nao foi possivel excluir a folha', 'Tente novamente em alguns instantes.');
        }

        redirect("/contrato/ver/{$id_contrato}");
        exit;
    }

    public function adicionar_produto_inline($id_contrato)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/contrato/ver/' . (int)$id_contrato);
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        $id_contrato = (int)$id_contrato;
        $numero_folha = (int)($_POST['numero_folha'] ?? 0);
        $qtd = (int)($_POST['quantidade'] ?? 0);
        $valor_unit = (float)($_POST['valor_unitario'] ?? 0);
        $nome_produto = trim($_POST['nome_produto'] ?? '');

        if (!$this->contratoModel->folhaExiste($id_contrato, $numero_folha)) {
            definir_flash('erro', 'Folha invalida', 'A folha escolhida para este produto nao existe.');
            redirect("/contrato/ver/{$id_contrato}");
            exit;
        }

        if ($nome_produto === '' || $qtd <= 0 || $valor_unit < 0) {
            definir_flash('erro', 'Dados invalidos', 'Preencha nome, quantidade e valor unitario corretamente.');
            redirect("/contrato/ver/{$id_contrato}?folha={$numero_folha}");
            exit;
        }

        if ($this->contratoModel->adicionarProdutoUnico(
            $id_contrato,
            $numero_folha,
            $nome_produto,
            trim($_POST['marca'] ?? ''),
            $_POST['unidade'] ?? 'UN',
            $qtd,
            $valor_unit
        )) {
            definir_flash('sucesso', 'Produto adicionado', 'O produto foi incluido na nota com sucesso.');
            registrar_log(
                Model::getConexao(),
                'Contrato - Cadastrar Produto',
                "Adicionou produto '{$nome_produto}' na folha {$numero_folha} do contrato #{$id_contrato}."
            );
        } else {
            definir_flash('erro', 'Nao foi possivel adicionar o produto', 'Revise os dados e tente novamente.');
        }

        redirect("/contrato/ver/{$id_contrato}?folha={$numero_folha}");
        exit;
    }

    public function editar_produto($id_produto)
    {
        $id_produto = (int)$id_produto;
        $produto = $this->contratoModel->buscarProdutoPorIdComContrato($id_produto);

        if (!$produto) {
            $this->mostrarErro404('Produto nao encontrado.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $nome_produto = trim($_POST['nome_produto'] ?? '');
            $quantidade = (int)($_POST['quantidade'] ?? 0);
            $valor_unitario = (float)($_POST['valor_unitario'] ?? 0);

            if ($nome_produto !== '' && $quantidade > 0 && $valor_unitario >= 0) {
                if ($this->contratoModel->atualizarProduto(
                    $id_produto,
                    $nome_produto,
                    trim($_POST['marca'] ?? ''),
                    $_POST['unidade'] ?? 'UN',
                    $quantidade,
                    $valor_unitario
                )) {
                    definir_flash('sucesso', 'Produto atualizado', 'As informacoes do item foram salvas.');
                    registrar_log(
                        Model::getConexao(),
                        'Contrato - Editar Produto',
                        "Atualizou produto ID {$id_produto} no contrato #{$produto['id_contrato']}."
                    );
                } else {
                    definir_flash('erro', 'Nao foi possivel atualizar', 'O item nao pode ser salvo neste momento.');
                }

                $folha = (int)($produto['numero_folha'] ?? 1);
                redirect("/contrato/ver/{$produto['id_contrato']}?folha={$folha}");
                exit;
            }

            $this->view('contratos/produto_form', [
                'acao' => 'editar',
                'produto' => array_merge($produto, [
                    'nome_produto' => $nome_produto,
                    'marca' => trim($_POST['marca'] ?? ''),
                    'unidade' => $_POST['unidade'] ?? 'UN',
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valor_unitario
                ]),
                'erro' => 'Preencha nome, quantidade e valor unitario corretamente.'
            ]);
            return;
        }

        $this->view('contratos/produto_form', [
            'acao' => 'editar',
            'produto' => $produto,
            'erro' => ''
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
            if ($this->contratoModel->excluirProduto($id_produto)) {
                definir_flash('sucesso', 'Produto excluido', 'O item foi removido da nota.');
                registrar_log(
                    Model::getConexao(),
                    'Contrato - Excluir Produto',
                    "Excluiu produto '{$produto['nome_produto']}' (ID {$id_produto}) do contrato #{$produto['id_contrato']}."
                );
            } else {
                definir_flash('erro', 'Nao foi possivel excluir', 'O item nao pode ser removido.');
            }

            $folha = (int)($produto['numero_folha'] ?? 1);
            redirect("/contrato/ver/{$produto['id_contrato']}?folha={$folha}");
            exit;
        }

        definir_flash('erro', 'Produto nao encontrado', 'O item informado nao existe mais.');
        redirect('/contrato');
        exit;
    }

    public function duplicar_folha($id_contrato, $numero_folha)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/contrato/ver/' . (int)$id_contrato);
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        $id_contrato = (int)$id_contrato;
        $numero_folha = (int)$numero_folha;

        if (!$this->contratoModel->folhaExiste($id_contrato, $numero_folha)) {
            definir_flash('erro', 'Folha nao encontrada', 'A nota escolhida para duplicacao nao existe.');
            redirect("/contrato/ver/{$id_contrato}");
            exit;
        }

        $nova_folha = $this->contratoModel->duplicarFolha($id_contrato, $numero_folha);
        if ($nova_folha) {
            definir_flash('sucesso', 'Folha duplicada', "A folha {$numero_folha} foi duplicada com sucesso.");
            registrar_log(
                Model::getConexao(),
                'Contrato - Cadastrar Folha',
                "Duplicou a folha {$numero_folha} para a folha {$nova_folha} no contrato #{$id_contrato}."
            );
        } else {
            definir_flash('erro', 'Nao foi possivel duplicar', 'A nota nao pode ser duplicada neste momento.');
        }

        $aba = $nova_folha ? $nova_folha : $numero_folha;
        redirect("/contrato/ver/{$id_contrato}?folha={$aba}");
        exit;
    }

    public function imprimir($id)
    {
        $id = (int)$id;
        $contrato = $this->contratoModel->buscarPorId($id);

        if (!$contrato) {
            $this->mostrarErro404('Contrato nao encontrado.');
        }

        $produtos = $this->contratoModel->buscarProdutos($id);
        $folhas = $this->contratoModel->buscarFolhas($id);
        $folhaSelecionada = isset($_GET['folha']) ? (int)$_GET['folha'] : 0;

        if ($folhaSelecionada > 0) {
            $folhas = array_values(array_filter($folhas, static function ($folha) use ($folhaSelecionada) {
                return (int)$folha['numero_folha'] === $folhaSelecionada;
            }));

            $produtos = array_values(array_filter($produtos, static function ($produto) use ($folhaSelecionada) {
                return (int)$produto['numero_folha'] === $folhaSelecionada;
            }));
        }

        $this->view('contratos/imprimir', [
            'contrato' => $contrato,
            'produtos' => $produtos,
            'folhas' => $folhas,
            'folhaSelecionada' => $folhaSelecionada
        ]);
    }

    private function processarProdutosPost()
    {
        $produtos = [];
        if (isset($_POST['produto_nome']) && is_array($_POST['produto_nome'])) {
            foreach ($_POST['produto_nome'] as $i => $nome) {
                $nome = trim((string)$nome);
                $quantidade = (int)($_POST['produto_qtd'][$i] ?? 0);
                $valor_unitario = (float)($_POST['produto_valor'][$i] ?? 0);

                if ($nome !== '' && $quantidade > 0 && $valor_unitario >= 0) {
                    $produtos[] = [
                        'nome' => $nome,
                        'marca' => trim($_POST['produto_marca'][$i] ?? ''),
                        'unidade' => $_POST['produto_unidade'][$i] ?? 'UN',
                        'quantidade' => $quantidade,
                        'valor_unitario' => $valor_unitario
                    ];
                }
            }
        }
        return $produtos;
    }

    private function mostrarErro404($msg)
    {
        definir_flash('erro', 'Registro nao encontrado', $msg);
        redirect('/contrato');
        exit;
    }
}
