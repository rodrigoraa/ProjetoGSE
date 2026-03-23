<?php
require_once ROOT_PATH . '/src/Models/Certidao.php';

class CertidaoController extends Controller
{
    private $certidaoModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }
        $this->certidaoModel = new Certidao();
    }

    public function index()
    {
        $ano_atual = date('Y');
        $registros = $this->certidaoModel->listarVigentes($ano_atual);

        $dados_organizados = [];
        $lista_fornecedores = [];

        foreach ($registros as $reg) {
            $fornecedor = strtoupper($reg['fornecedor']);
            $tipo = strtoupper($reg['tipo_certidao']);

            if (!in_array($fornecedor, $lista_fornecedores)) {
                $lista_fornecedores[] = $fornecedor;
            }
            $dados_organizados[$fornecedor][$tipo][] = $reg;
        }
        sort($lista_fornecedores);

        $tipos_certidoes = $this->certidaoModel->listarTiposCertidao(true);

        $this->view('certidoes/index', [
            'ano_atual' => $ano_atual,
            'lista_fornecedores' => $lista_fornecedores,
            'dados_organizados' => $dados_organizados,
            'tipos_certidoes' => $tipos_certidoes
        ]);
    }

    public function arquivadas()
    {
        $anos_disponiveis = $this->certidaoModel->getAnosDisponiveis();

        $ano_filtro = $_GET['ano'] ?? 'todos';

        $certidoes = $this->certidaoModel->listarPorAno($ano_filtro);

        $this->view('certidoes/arquivadas', [
            'anos_disponiveis' => $anos_disponiveis,
            'ano_filtro' => $ano_filtro,
            'certidoes' => $certidoes
        ]);
    }

    public function cadastrar()
    {
        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $fornecedor = $_POST['fornecedor'];
            $tipo = $_POST['tipo_certidao'];
            $emissao = $_POST['data_emissao'];
            $vencimento = $_POST['data_vencimento'];
            $obs = $_POST['observacao'];

            $nome_arquivo_final = null;

            if (isset($_FILES['arquivo_pdf']) && $_FILES['arquivo_pdf']['error'] == 0) {
                $extensao = strtolower(pathinfo($_FILES['arquivo_pdf']['name'], PATHINFO_EXTENSION));
                if ($extensao != "pdf") {
                    $mensagem = '<p class="error-message">Erro: Apenas arquivos PDF são permitidos.</p>';
                } else {
                    $nome_arquivo_final = uniqid('cert_') . '.' . $extensao;
                    $destino = ROOT_PATH . '/public/uploads/certidoes/' . $nome_arquivo_final;
                    if (!move_uploaded_file($_FILES['arquivo_pdf']['tmp_name'], $destino)) {
                        $mensagem = '<p class="error-message">Erro ao salvar o arquivo na pasta.</p>';
                        $nome_arquivo_final = null;
                    }
                }
            }

            if (empty($mensagem)) {
                if ($this->certidaoModel->cadastrar($fornecedor, $tipo, $emissao, $vencimento, $obs, $nome_arquivo_final)) {

                    if (!empty($_POST['renovar_id'])) {
                        $this->certidaoModel->alternarArquivo($_POST['renovar_id'], 1);
                    }

                    $fornecedores = $this->certidaoModel->listarFornecedores();
                    $tipos = $this->certidaoModel->listarTiposCertidao();

                    $nomeFornecedor = '';
                    $nomeTipo = '';

                    foreach ($fornecedores as $f) {
                        if ($f['id'] == $fornecedor) {
                            $nomeFornecedor = $f['nome'];
                            break;
                        }
                    }

                    foreach ($tipos as $t) {
                        if ($t['id'] == $tipo) {
                            $nomeTipo = $t['nome'];
                            break;
                        }
                    }

                    registrar_log(Model::getConexao(), "Certidão - Cadastrar", "$nomeTipo - $nomeFornecedor");
                    redirect('/certidao');
                    exit;
                } else {
                    $mensagem = '<p class="error-message">Erro ao salvar no banco de dados.</p>';
                }
            }
        }

        // MELHORIA: Usando o Model em vez de SQL direto
        $fornecedores = $this->certidaoModel->listarFornecedores();
        $tipos = $this->certidaoModel->listarTiposCertidao();

        $this->view('certidoes/cadastrar', [
            'mensagem' => $mensagem,
            'fornecedores' => $fornecedores,
            'tipos' => $tipos
        ]);
    }

    public function editar($id)
    {
        if (!$id) {
            redirect('/certidao');
            exit;
        }

        $mensagem = '';
        $dados_atuais = $this->certidaoModel->buscarPorId($id);

        if (!$dados_atuais) {
            die("Certidão não encontrada.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $fornecedor = $_POST['fornecedor'];
            $tipo = $_POST['tipo_certidao'];
            $emissao = $_POST['data_emissao'];
            $vencimento = $_POST['data_vencimento'];
            $obs = $_POST['observacao'];

            $nome_arquivo_final = $dados_atuais['arquivo_pdf'];

            if (isset($_FILES['arquivo_pdf']) && $_FILES['arquivo_pdf']['error'] == 0) {
                $extensao = strtolower(pathinfo($_FILES['arquivo_pdf']['name'], PATHINFO_EXTENSION));

                if ($extensao != "pdf") {
                    $mensagem = '<p class="error-message">Erro: Apenas PDF permitido.</p>';
                } else {
                    if ($dados_atuais['arquivo_pdf']) {
                        $caminhoAntigo = ROOT_PATH . '/public/uploads/certidoes/' . $dados_atuais['arquivo_pdf'];
                        if (file_exists($caminhoAntigo)) unlink($caminhoAntigo);
                    }

                    $novoNome = uniqid('cert_') . '.' . $extensao;
                    $destino = ROOT_PATH . '/public/uploads/certidoes/' . $novoNome;

                    if (move_uploaded_file($_FILES['arquivo_pdf']['tmp_name'], $destino)) {
                        $nome_arquivo_final = $novoNome;
                    } else {
                        $mensagem = '<p class="error-message">Erro ao salvar arquivo.</p>';
                    }
                }
            }

            if (empty($mensagem)) {
                if ($this->certidaoModel->atualizar($id, $fornecedor, $tipo, $emissao, $vencimento, $obs, $nome_arquivo_final)) {
                    registrar_log(Model::getConexao(), "Certidão - Editar", "Editou: $tipo - $fornecedor");
                    redirect('/certidao');
                    exit;
                } else {
                    $mensagem = '<p class="error-message">Erro ao atualizar no banco.</p>';
                }
            }
        }

        $fornecedores = $this->certidaoModel->listarFornecedores();
        $tipos = $this->certidaoModel->listarTiposCertidao();

        $this->view('certidoes/editar', [
            'certidao' => $dados_atuais,
            'mensagem' => $mensagem,
            'fornecedores' => $fornecedores,
            'tipos' => $tipos
        ]);
    }

    public function excluir($id)
    {
        if ($id) {
            $dadosExcluidos = $this->certidaoModel->excluir($id);

            if ($dadosExcluidos) {
                if (!empty($dadosExcluidos['arquivo_pdf'])) {
                    $caminhoFisico = ROOT_PATH . '/public/uploads/certidoes/' . $dadosExcluidos['arquivo_pdf'];
                    if (file_exists($caminhoFisico)) {
                        unlink($caminhoFisico);
                    }
                }

                $detalhes = "Apagou: " . $dadosExcluidos['tipo_certidao'] . " - " . $dadosExcluidos['fornecedor'];
                registrar_log(Model::getConexao(), "Certidão - Apagar", $detalhes);
            }
        }

        if (isset($_GET['origem']) && $_GET['origem'] == 'arquivo') {
            $ano = $_GET['ano'] ?? date('Y');
            redirect("/certidao/arquivadas?ano=$ano");
        } else {
            redirect('/certidao');
        }
        exit;
    }

    public function arquivar($id)
    {
        $this->certidaoModel->alternarArquivo($id, 1);
        redirect('/certidao');
        exit;
    }

    public function desarquivar($id)
    {
        $this->certidaoModel->alternarArquivo($id, 0);
        redirect('/certidao/arquivadas');
        exit;
    }

    public function configurar()
    {
        $data['fornecedores'] = $this->certidaoModel->listarFornecedores();
        $data['tipos'] = $this->certidaoModel->listarTiposCertidao();

        $this->view('certidoes/configurar', $data);
    }

    public function adicionarOpcao()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = strtoupper(trim($_POST['nome']));
            $tabela = $_POST['tipo_lista'];

            if (!empty($nome)) {
                $db = Model::getConexao();
                $stmt = $db->prepare("INSERT OR IGNORE INTO $tabela (nome) VALUES (?)");
                $stmt->execute([$nome]);
            }
        }
        redirect('/certidao/configurar');
    }

    public function excluirOpcao()
    {
        $id = $_GET['id'] ?? null;
        $tabela = $_GET['tipo'] ?? null;

        if ($id && in_array($tabela, ['lista_fornecedores', 'lista_tipos_certidao'])) {
            $db = Model::getConexao();
            $stmt = $db->prepare("DELETE FROM $tabela WHERE id = ?");
            $stmt->execute([$id]);

            registrar_log($db, "Configuração", "Excluiu item ID $id da tabela $tabela");
        }

        redirect('/certidao/configurar');
    }

    public function editarOpcao()
    {
        $id = $_GET['id'] ?? null;
        $tabela = $_GET['tipo'] ?? null;
        $nome_antigo = $_GET['nome_antigo'] ?? null;
        $novo_nome = strtoupper(trim($_GET['novo_nome'] ?? ''));

        if ($id && $tabela && $nome_antigo && $novo_nome && $novo_nome !== strtoupper($nome_antigo) && in_array($tabela, ['lista_fornecedores', 'lista_tipos_certidao'])) {
            $db = Model::getConexao();

            try {
                $db->beginTransaction();

                $stmtLista = $db->prepare("UPDATE $tabela SET nome = ? WHERE id = ?");
                $stmtLista->execute([$novo_nome, $id]);

                $db->commit();
                registrar_log($db, "Configurações", "Renomeou em $tabela: de $nome_antigo para $novo_nome");
            } catch (Exception $e) {
                $db->rollBack();
                error_log("Erro ao renomear opção: " . $e->getMessage());
            }
        }

        redirect('/certidao/configurar');
    }
}
