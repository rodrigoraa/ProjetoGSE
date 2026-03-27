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
        $registros = $this->certidaoModel->listarVigentes();

        $dados_organizados = [];
        $lista_fornecedores = [];

        foreach ($registros as $reg) {
            $fornecedor = mb_strtoupper($reg['fornecedor'], 'UTF-8');
            $tipo = mb_strtoupper($reg['tipo_certidao'], 'UTF-8');

            if (!in_array($fornecedor, $lista_fornecedores, true)) {
                $lista_fornecedores[] = $fornecedor;
            }
            $dados_organizados[$fornecedor][$tipo][] = $reg;
        }
        sort($lista_fornecedores);

        $this->view('certidoes/index', [
            'lista_fornecedores' => $lista_fornecedores,
            'dados_organizados' => $dados_organizados,
            'tipos_certidoes' => $this->certidaoModel->listarTiposCertidao(true),
            'ano_atual' => date('Y')
        ]);
    }

    public function cadastrar()
    {
        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $fornecedor = filter_input(INPUT_POST, 'fornecedor', FILTER_VALIDATE_INT);
            $tipo = filter_input(INPUT_POST, 'tipo_certidao', FILTER_VALIDATE_INT);
            $emissao = $_POST['data_emissao'];
            $vencimento = $_POST['data_vencimento'];
            $obs = strip_tags(trim($_POST['observacao']));

            $nome_arquivo_final = $this->processarUploadPdf();

            if ($nome_arquivo_final !== false) {
                if ($this->certidaoModel->cadastrar($fornecedor, $tipo, $emissao, $vencimento, $obs, $nome_arquivo_final)) {
                    if (!empty($_POST['renovar_id'])) {
                        $this->certidaoModel->alternarArquivo((int)$_POST['renovar_id'], 1);
                        registrar_log(
                            Model::getConexao(),
                            'Certidao - Arquivar',
                            'Arquivou certidão renovada ID: ' . (int)$_POST['renovar_id']
                        );
                    }

                    registrar_log(Model::getConexao(), "Certidao - Cadastrar", "ID Tipo: $tipo | ID Fornec: $fornecedor");
                    redirect('/certidao');
                    exit;
                }

                $mensagem = '<p class="error-message">Erro ao salvar no banco de dados.</p>';
            } else {
                $mensagem = '<p class="error-message">Apenas arquivos PDF sao permitidos.</p>';
            }
        }

        $this->view('certidoes/cadastrar', [
            'mensagem' => $mensagem,
            'fornecedores' => $this->certidaoModel->listarFornecedores(),
            'tipos' => $this->certidaoModel->listarTiposCertidao()
        ]);
    }

    public function editar($id)
    {
        $id = (int)$id;
        $certidao = $this->certidaoModel->buscarPorId($id);

        if (!$certidao) {
            redirect('/certidao');
            exit;
        }

        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $fornecedor = filter_input(INPUT_POST, 'fornecedor', FILTER_VALIDATE_INT);
            $tipo = filter_input(INPUT_POST, 'tipo_certidao', FILTER_VALIDATE_INT);
            $emissao = $_POST['data_emissao'];
            $vencimento = $_POST['data_vencimento'];
            $obs = strip_tags(trim($_POST['observacao']));

            $novoArquivo = $this->processarUploadPdf($certidao['arquivo_pdf']);
            if ($novoArquivo === false) {
                $mensagem = '<p class="error-message">Apenas arquivos PDF sao permitidos.</p>';
            } elseif ($this->certidaoModel->atualizar($id, $fornecedor, $tipo, $emissao, $vencimento, $obs, $novoArquivo)) {
                registrar_log(
                    Model::getConexao(),
                    'Certidao - Editar',
                    "Atualizou certidão ID: {$id} | Tipo ID: {$tipo} | Fornecedor ID: {$fornecedor}"
                );
                redirect('/certidao');
                exit;
            } else {
                $mensagem = '<p class="error-message">Erro ao atualizar a certidao.</p>';
            }

            $certidao = $this->certidaoModel->buscarPorId($id);
        }

        $this->view('certidoes/editar', [
            'mensagem' => $mensagem,
            'certidao' => $certidao,
            'fornecedores' => $this->certidaoModel->listarFornecedores(),
            'tipos' => $this->certidaoModel->listarTiposCertidao()
        ]);
    }

    public function arquivar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/certidao');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');
        if ($this->certidaoModel->alternarArquivo((int)$id, 1)) {
            registrar_log(Model::getConexao(), 'Certidao - Arquivar', "Arquivou certidão ID: {$id}");
        }
        redirect('/certidao');
        exit;
    }

    public function desarquivar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/certidao/arquivadas');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');
        if ($this->certidaoModel->alternarArquivo((int)$id, 0)) {
            registrar_log(Model::getConexao(), 'Certidao - Desarquivar', "Desarquivou certidão ID: {$id}");
        }
        redirect('/certidao/arquivadas');
        exit;
    }

    public function excluir($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/certidao');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        if ($id) {
            $dadosExcluidos = $this->certidaoModel->excluir($id);

            if ($dadosExcluidos) {
                if (!empty($dadosExcluidos['arquivo_pdf'])) {
                    $caminhoFisico = ROOT_PATH . '/public/uploads/certidoes/' . $dadosExcluidos['arquivo_pdf'];
                    if (file_exists($caminhoFisico)) {
                        unlink($caminhoFisico);
                    }
                }

                $detalhes = "Apagou: " . ($dadosExcluidos['tipo_certidao'] ?? 'N/A') . " - " . ($dadosExcluidos['fornecedor'] ?? 'N/A');
                registrar_log(Model::getConexao(), "Certidao - Apagar", $detalhes);
            }
        }

        $origem = $_POST['origem'] ?? 'lista';
        if ($origem === 'arquivo') {
            $ano = $_POST['ano'] ?? date('Y');
            redirect("/certidao/arquivadas?ano=" . urlencode($ano));
        } else {
            redirect('/certidao');
        }
        exit;
    }

    public function arquivadas()
    {
        $ano_filtro = $_GET['ano'] ?? 'todos';

        $this->view('certidoes/arquivadas', [
            'certidoes' => $this->certidaoModel->listarPorAno($ano_filtro),
            'ano_filtro' => $ano_filtro,
            'anos_disponiveis' => $this->certidaoModel->getAnosDisponiveis()
        ]);
    }

    public function configurar()
    {
        $this->view('certidoes/configurar', [
            'fornecedores' => $this->certidaoModel->listarFornecedores(),
            'tipos' => $this->certidaoModel->listarTiposCertidao()
        ]);
    }

    public function adicionarOpcao()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');
            $tipoLista = $_POST['tipo_lista'] ?? '';
            $nome = trim($_POST['nome'] ?? '');

            if ($nome !== '') {
                if ($this->certidaoModel->adicionarOpcaoLista($tipoLista, $nome)) {
                    registrar_log(
                        Model::getConexao(),
                        'Certidao - Cadastrar Opcao',
                        "Adicionou '{$nome}' em {$tipoLista}"
                    );
                }
            }
        }

        redirect('/certidao/configurar');
    }

    public function editarOpcao()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');
            $id = (int)($_POST['id'] ?? 0);
            $tipoLista = $_POST['tipo'] ?? '';
            $novoNome = trim($_POST['novo_nome'] ?? '');

            if ($id > 0 && $novoNome !== '') {
                if ($this->certidaoModel->atualizarOpcaoLista($tipoLista, $id, $novoNome)) {
                    registrar_log(
                        Model::getConexao(),
                        'Certidao - Editar Opcao',
                        "Atualizou opção ID {$id} em {$tipoLista} para '{$novoNome}'"
                    );
                }
            }
        }

        redirect('/certidao/configurar');
    }

    public function excluirOpcao()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');
            $id = (int)($_POST['id'] ?? 0);
            $tipoLista = $_POST['tipo'] ?? '';

            if ($id > 0) {
                if ($this->certidaoModel->excluirOpcaoLista($tipoLista, $id)) {
                    registrar_log(
                        Model::getConexao(),
                        'Certidao - Excluir Opcao',
                        "Excluiu opção ID {$id} de {$tipoLista}"
                    );
                }
            }
        }

        redirect('/certidao/configurar');
    }

    private function processarUploadPdf($arquivoAtual = null)
    {
        if (!isset($_FILES['arquivo_pdf']) || $_FILES['arquivo_pdf']['error'] === UPLOAD_ERR_NO_FILE) {
            return $arquivoAtual;
        }

        if ($_FILES['arquivo_pdf']['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $extensao = strtolower(pathinfo($_FILES['arquivo_pdf']['name'], PATHINFO_EXTENSION));
        if ($extensao !== 'pdf') {
            return false;
        }

        $tmpPath = $_FILES['arquivo_pdf']['tmp_name'];
        if (!is_uploaded_file($tmpPath)) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $tmpPath) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        $mimePermitido = in_array($mimeType, ['application/pdf', 'application/x-pdf'], true);
        $cabecalho = file_get_contents($tmpPath, false, null, 0, 4);
        if (!$mimePermitido || $cabecalho !== '%PDF') {
            return false;
        }

        $diretorio = ROOT_PATH . '/public/uploads/certidoes/';
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $novoNome = uniqid('cert_', true) . '.pdf';
        $destino = $diretorio . $novoNome;

        if (!move_uploaded_file($_FILES['arquivo_pdf']['tmp_name'], $destino)) {
            return false;
        }

        if (!empty($arquivoAtual)) {
            $caminhoAtual = $diretorio . basename($arquivoAtual);
            if (is_file($caminhoAtual)) {
                unlink($caminhoAtual);
            }
        }

        return $novoNome;
    }
}
