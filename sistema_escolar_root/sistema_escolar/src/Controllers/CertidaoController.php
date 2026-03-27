<?php
require_once ROOT_PATH . '/src/Models/Certidao.php';

class CertidaoController extends Controller
{
    private $certidaoModel;
    private const TIPOS_CONFIG = [
        'lista_fornecedores' => 'fornecedor',
        'lista_tipos_certidao' => 'tipo de certidão'
    ];

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
            $emissao = trim($_POST['data_emissao'] ?? '');
            $vencimento = trim($_POST['data_vencimento'] ?? '');
            $obs = strip_tags(trim($_POST['observacao'] ?? ''));

            if (!$this->validarFormularioCertidao($fornecedor, $tipo, $emissao, $vencimento)) {
                $mensagem = '<p class="error-message">Preencha fornecedor, tipo, emissão e vencimento com dados válidos.</p>';
            } else {
                $nomeArquivoFinal = $this->processarUploadPdf();

                if ($nomeArquivoFinal !== false) {
                    if ($this->certidaoModel->cadastrar($fornecedor, $tipo, $emissao, $vencimento, $obs, $nomeArquivoFinal)) {
                        if (!empty($_POST['renovar_id'])) {
                            $this->certidaoModel->alternarArquivo((int)$_POST['renovar_id'], 1);
                            registrar_log(
                                Model::getConexao(),
                                'Certidao - Arquivar',
                                'Arquivou certidÃ£o renovada ID: ' . (int)$_POST['renovar_id']
                            );
                        }

                        registrar_log(Model::getConexao(), 'Certidao - Cadastrar', "ID Tipo: $tipo | ID Fornec: $fornecedor");
                        definir_flash(
                            'sucesso',
                            'Certidão cadastrada com sucesso',
                            'O novo documento foi salvo e já está disponível na matriz de certidões.',
                            'Se a certidão anterior foi renovada, ela também foi arquivada automaticamente.'
                        );
                        redirect('/certidao');
                        exit;
                    }

                    $mensagem = '<p class="error-message">Erro ao salvar no banco de dados.</p>';
                } else {
                    $mensagem = '<p class="error-message">Apenas arquivos PDF sao permitidos.</p>';
                }
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
            $emissao = trim($_POST['data_emissao'] ?? '');
            $vencimento = trim($_POST['data_vencimento'] ?? '');
            $obs = strip_tags(trim($_POST['observacao'] ?? ''));

            if (!$this->validarFormularioCertidao($fornecedor, $tipo, $emissao, $vencimento)) {
                $mensagem = '<p class="error-message">Preencha fornecedor, tipo, emissão e vencimento com dados válidos.</p>';
            } else {
                $novoArquivo = $this->processarUploadPdf($certidao['arquivo_pdf']);
                if ($novoArquivo === false) {
                    $mensagem = '<p class="error-message">Apenas arquivos PDF sao permitidos.</p>';
                } elseif ($this->certidaoModel->atualizar($id, $fornecedor, $tipo, $emissao, $vencimento, $obs, $novoArquivo)) {
                    registrar_log(
                        Model::getConexao(),
                        'Certidao - Editar',
                        "Atualizou certidÃ£o ID: {$id} | Tipo ID: {$tipo} | Fornecedor ID: {$fornecedor}"
                    );
                    definir_flash(
                        'sucesso',
                        'Certidão atualizada com sucesso',
                        'Os dados informados foram salvos no cadastro da certidão.',
                        'Você retornou para a matriz principal para continuar o acompanhamento.'
                    );
                    redirect('/certidao');
                    exit;
                } else {
                    $mensagem = '<p class="error-message">Erro ao atualizar a certidao.</p>';
                }
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
            registrar_log(Model::getConexao(), 'Certidao - Arquivar', "Arquivou certidÃ£o ID: {$id}");
            definir_flash(
                'sucesso',
                'Certidão arquivada',
                'O registro foi movido para o histórico de certidões arquivadas.',
                'Você pode desarquivar depois, se precisar trazer esse item de volta.'
            );
        } else {
            definir_flash(
                'erro',
                'Não foi possível arquivar a certidão',
                'O sistema não conseguiu mover esse registro para o arquivo.',
                'Tente novamente em instantes.'
            );
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
            registrar_log(Model::getConexao(), 'Certidao - Desarquivar', "Desarquivou certidÃ£o ID: {$id}");
            definir_flash(
                'sucesso',
                'Certidão desarquivada',
                'O documento voltou para a lista principal de certidões vigentes.',
                'Confira a matriz para validar o novo posicionamento do item.'
            );
        } else {
            definir_flash(
                'erro',
                'Não foi possível desarquivar a certidão',
                'O sistema não conseguiu devolver esse item para a lista principal.',
                'Tente novamente.'
            );
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

                $detalhes = 'Apagou: ' . ($dadosExcluidos['tipo_certidao'] ?? 'N/A') . ' - ' . ($dadosExcluidos['fornecedor'] ?? 'N/A');
                registrar_log(Model::getConexao(), 'Certidao - Apagar', $detalhes);
                definir_flash(
                    'sucesso',
                    'Certidão excluída com sucesso',
                    'O registro selecionado foi removido permanentemente do sistema.',
                    'Se isso foi um engano, será necessário cadastrar a certidão novamente.'
                );
            } else {
                definir_flash(
                    'erro',
                    'Não foi possível excluir a certidão',
                    'O item não foi removido do sistema.',
                    'Verifique se ele ainda existe e tente novamente.'
                );
            }
        }

        $origem = $_POST['origem'] ?? 'lista';
        if ($origem === 'arquivo') {
            $ano = $_POST['ano'] ?? date('Y');
            redirect('/certidao/arquivadas?ano=' . urlencode($ano));
        } else {
            redirect('/certidao');
        }
        exit;
    }

    public function arquivadas()
    {
        $anoFiltro = $_GET['ano'] ?? 'todos';

        $this->view('certidoes/arquivadas', [
            'certidoes' => $this->certidaoModel->listarPorAno($anoFiltro),
            'ano_filtro' => $anoFiltro,
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
            $tipoLabel = $this->getTipoConfiguracaoLabel($tipoLista);

            if ($tipoLabel === null) {
                definir_flash('erro', 'Tipo de configuração inválido', 'O sistema recebeu uma lista que não é permitida para edição.', 'Atualize a página e tente novamente.');
            } elseif ($nome !== '') {
                if ($this->certidaoModel->adicionarOpcaoLista($tipoLista, $nome)) {
                    registrar_log(Model::getConexao(), 'Certidao - Cadastrar Opcao', "Adicionou '{$nome}' em {$tipoLista}");
                    definir_flash('sucesso', ucfirst($tipoLabel) . ' adicionado com sucesso', "A opção '{$nome}' foi incluída na lista de configuração.", 'Ela já pode ser usada nos próximos cadastros.');
                } else {
                    definir_flash('erro', 'Não foi possível adicionar a opção', "O sistema não conseguiu salvar o novo {$tipoLabel}.", 'Tente novamente.');
                }
            } else {
                definir_flash('erro', 'Nome inválido', "Informe um nome válido para adicionar um novo {$tipoLabel}.", 'Evite deixar o campo em branco.');
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
            $tipoLabel = $this->getTipoConfiguracaoLabel($tipoLista);

            if ($tipoLabel === null) {
                definir_flash('erro', 'Tipo de configuração inválido', 'O sistema recebeu uma lista que não é permitida para edição.', 'Atualize a página e tente novamente.');
            } elseif ($id > 0 && $novoNome !== '') {
                if ($this->certidaoModel->atualizarOpcaoLista($tipoLista, $id, $novoNome)) {
                    registrar_log(Model::getConexao(), 'Certidao - Editar Opcao', "Atualizou opÃ§Ã£o ID {$id} em {$tipoLista} para '{$novoNome}'");
                    definir_flash('sucesso', ucfirst($tipoLabel) . ' atualizado com sucesso', "A opção foi renomeada para '{$novoNome}'.", 'Os formulários já passarão a exibir o novo texto.');
                } else {
                    definir_flash('erro', 'Não foi possível atualizar a opção', "O sistema não conseguiu renomear esse {$tipoLabel}.", 'Tente novamente.');
                }
            } else {
                definir_flash('erro', 'Dados inválidos para edição', "Selecione um {$tipoLabel} válido e informe o novo nome.", 'Evite enviar o campo em branco.');
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
            $tipoLabel = $this->getTipoConfiguracaoLabel($tipoLista);

            if ($tipoLabel === null) {
                definir_flash('erro', 'Tipo de configuração inválido', 'O sistema recebeu uma lista que não é permitida para edição.', 'Atualize a página e tente novamente.');
            } elseif ($id > 0) {
                if ($this->certidaoModel->excluirOpcaoLista($tipoLista, $id)) {
                    registrar_log(Model::getConexao(), 'Certidao - Excluir Opcao', "Excluiu opÃ§Ã£o ID {$id} de {$tipoLista}");
                    definir_flash('sucesso', ucfirst($tipoLabel) . ' excluído com sucesso', 'A opção foi removida da configuração do sistema.', 'Verifique se ela não está sendo usada em novos cadastros.');
                } else {
                    definir_flash('erro', 'Não foi possível excluir a opção', "O sistema não conseguiu remover esse {$tipoLabel}.", 'Ele pode estar vinculado a registros existentes.');
                }
            } else {
                definir_flash('erro', 'Opção inválida para exclusão', "Selecione um {$tipoLabel} válido antes de tentar excluir.", 'Atualize a página e tente novamente.');
            }
        }

        redirect('/certidao/configurar');
    }

    private function validarFormularioCertidao($fornecedor, $tipo, $emissao, $vencimento)
    {
        return $fornecedor !== false
            && $fornecedor > 0
            && $tipo !== false
            && $tipo > 0
            && $this->isDataValida($emissao)
            && $this->isDataValida($vencimento);
    }

    private function isDataValida($data)
    {
        if (!is_string($data) || $data === '') {
            return false;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $data);
        return $dt && $dt->format('Y-m-d') === $data;
    }

    private function getTipoConfiguracaoLabel($tipoLista)
    {
        return self::TIPOS_CONFIG[$tipoLista] ?? null;
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
