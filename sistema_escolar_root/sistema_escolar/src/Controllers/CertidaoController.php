<?php
require_once ROOT_PATH . '/src/Models/Certidao.php';

class CertidaoController extends Controller
{
    private $certidaoModel;
    private $erroUploadPdf = '';
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
            $renovarId = filter_var($_POST['renovar_id'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1]
            ]);

            if (!$this->validarFormularioCertidao($fornecedor, $tipo, $emissao, $vencimento)) {
                $mensagem = '<p class="error-message">Preencha fornecedor, tipo, emissão e vencimento com dados válidos.</p>';
            } elseif ($renovarId !== false && !$this->renovacaoCompativel($renovarId, $fornecedor, $tipo)) {
                $mensagem = '<p class="error-message">A certidão selecionada para renovação não corresponde ao fornecedor e ao tipo informados.</p>';
            } else {
                $nomeArquivoFinal = $this->processarUploadPdf();

                if ($nomeArquivoFinal !== false) {
                    $pdo = Model::getConexao();

                    try {
                        $pdo->beginTransaction();

                        if (!$this->certidaoModel->cadastrar($fornecedor, $tipo, $emissao, $vencimento, $obs, $nomeArquivoFinal)) {
                            throw new RuntimeException('Falha ao cadastrar a certidão.');
                        }

                        if ($renovarId !== false && !$this->certidaoModel->alternarArquivo($renovarId, 1)) {
                            throw new RuntimeException('Falha ao arquivar a certidão renovada.');
                        }

                        $pdo->commit();

                        if ($renovarId !== false) {
                            registrar_log(
                                Model::getConexao(),
                                'Certidao - Arquivar',
                                'Arquivou certidão renovada ID: ' . $renovarId
                            );
                        }

                        registrar_log(Model::getConexao(), 'Certidao - Cadastrar', "ID Tipo: $tipo | ID Fornecedor: $fornecedor");
                        definir_flash(
                            'sucesso',
                            'Certidão cadastrada com sucesso',
                            'O novo documento foi salvo e já está disponível na matriz de certidões.',
                            'Se a certidão anterior foi renovada, ela também foi arquivada automaticamente.'
                        );
                        redirect('/certidao');
                        exit;
                    } catch (Throwable $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }

                        if (!empty($nomeArquivoFinal)) {
                            $this->removerArquivoPdf($nomeArquivoFinal);
                        }

                        error_log('Erro ao concluir cadastro/renovação de certidão: ' . $e->getMessage());
                        $mensagem = '<p class="error-message">Não foi possível salvar a certidão. Nenhuma alteração foi concluída.</p>';
                    }
                } else {
                    $mensagem = '<p class="error-message">' . e($this->erroUploadPdf ?: 'Não foi possível processar o arquivo PDF.') . '</p>';
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
                    $mensagem = '<p class="error-message">' . e($this->erroUploadPdf ?: 'Não foi possível processar o arquivo PDF.') . '</p>';
                } elseif ($this->certidaoModel->atualizar($id, $fornecedor, $tipo, $emissao, $vencimento, $obs, $novoArquivo)) {
                    if ($novoArquivo !== $certidao['arquivo_pdf'] && !empty($certidao['arquivo_pdf'])) {
                        $this->removerArquivoPdf($certidao['arquivo_pdf']);
                    }

                    registrar_log(
                        Model::getConexao(),
                        'Certidao - Editar',
                        "Atualizou certidão ID: {$id} | Tipo ID: {$tipo} | Fornecedor ID: {$fornecedor}"
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
                    if ($novoArquivo !== $certidao['arquivo_pdf'] && !empty($novoArquivo)) {
                        $this->removerArquivoPdf($novoArquivo);
                    }

                    $mensagem = '<p class="error-message">Erro ao atualizar a certidão.</p>';
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

    public function visualizarPdf($id)
    {
        $id = (int)$id;
        $certidao = $this->certidaoModel->buscarPorId($id);

        if (!$certidao) {
            definir_flash(
                'erro',
                'Certidão não encontrada',
                'O registro solicitado não foi localizado no sistema.',
                'Atualize a lista e tente novamente.'
            );
            redirect('/certidao');
            exit;
        }

        if (empty($certidao['arquivo_pdf'])) {
            definir_flash(
                'aviso',
                'Esta certidão ainda não possui PDF enviado',
                'O cadastro existe, mas nenhum arquivo foi anexado para visualização.',
                'Se necessário, abra a edição da certidão para enviar o documento.'
            );
            $this->redirecionarRetornoPdf($id);
        }

        $nomeArquivo = basename((string)$certidao['arquivo_pdf']);
        $caminhoFisico = $this->localizarArquivoPdf($nomeArquivo);

        if ($caminhoFisico === null) {
            definir_flash(
                'erro',
                'Arquivo PDF não encontrado',
                'O cadastro foi localizado, mas o arquivo não está disponível no servidor.',
                'Envie o PDF novamente na edição da certidão.'
            );
            $this->redirecionarRetornoPdf($id);
        }

        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($caminhoFisico));
        header('Content-Disposition: inline; filename="' . rawurlencode($nomeArquivo) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store, max-age=0');
        header('Pragma: no-cache');
        session_write_close();
        readfile($caminhoFisico);
        exit;
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
            registrar_log(Model::getConexao(), 'Certidao - Desarquivar', "Desarquivou certidão ID: {$id}");
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
        $this->exigirAdministrador();

        $id = (int)$id;
        $certidao = $id > 0 ? $this->certidaoModel->buscarPorId($id) : false;

        if ($certidao) {
            $caminhoOriginal = !empty($certidao['arquivo_pdf'])
                ? $this->localizarArquivoPdf($certidao['arquivo_pdf'])
                : null;
            $caminhoTemporario = null;
            $pdo = Model::getConexao();

            try {
                if ($caminhoOriginal !== null) {
                    $caminhoTemporario = dirname($caminhoOriginal)
                        . DIRECTORY_SEPARATOR
                        . '.exclusao_' . bin2hex(random_bytes(8)) . '.pdf';

                    if (!rename($caminhoOriginal, $caminhoTemporario)) {
                        throw new RuntimeException('Não foi possível proteger o PDF durante a exclusão.');
                    }
                }

                $pdo->beginTransaction();
                $dadosExcluidos = $this->certidaoModel->excluir($id);

                if (!$dadosExcluidos) {
                    throw new RuntimeException('O registro não foi excluído do banco.');
                }

                $pdo->commit();

                if ($caminhoTemporario !== null && is_file($caminhoTemporario) && !unlink($caminhoTemporario)) {
                    error_log("Certidão ID {$id} excluída, mas o PDF temporário não pôde ser removido: {$caminhoTemporario}");
                }

                $detalhes = 'Apagou: ' . ($dadosExcluidos['tipo_certidao'] ?? 'N/A') . ' - ' . ($dadosExcluidos['fornecedor'] ?? 'N/A');
                registrar_log(Model::getConexao(), 'Certidao - Apagar', $detalhes);
                definir_flash(
                    'sucesso',
                    'Certidão excluída com sucesso',
                    'O registro selecionado foi removido permanentemente do sistema.',
                    'Se isso foi um engano, será necessário cadastrar a certidão novamente.'
                );
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                if ($caminhoTemporario !== null && is_file($caminhoTemporario)) {
                    if ($caminhoOriginal === null || !rename($caminhoTemporario, $caminhoOriginal)) {
                        error_log("CRÍTICO: não foi possível restaurar o PDF da certidão ID {$id} após falha de exclusão.");
                    }
                }

                error_log("Erro ao excluir certidão ID {$id}: " . $e->getMessage());
                definir_flash(
                    'erro',
                    'Não foi possível excluir a certidão',
                    'O item não foi removido do sistema.',
                    'Verifique se ele ainda existe e tente novamente.'
                );
            }
        } else {
            definir_flash(
                'erro',
                'Certidão não encontrada',
                'O registro informado não existe ou já foi removido.',
                'Atualize a lista antes de tentar novamente.'
            );
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
                    registrar_log(Model::getConexao(), 'Certidao - Editar Opcao', "Atualizou opção ID {$id} em {$tipoLista} para '{$novoNome}'");
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
                    registrar_log(Model::getConexao(), 'Certidao - Excluir Opcao', "Excluiu opção ID {$id} de {$tipoLista}");
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
            && $this->isDataValida($vencimento)
            && $vencimento > $emissao;
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

    private function renovacaoCompativel($id, $fornecedor, $tipo)
    {
        $certidao = $this->certidaoModel->buscarPorId((int)$id);

        if (!$certidao) {
            return false;
        }

        $estaAtiva = ((int)($certidao['arquivado'] ?? 0) === 0)
            && ((int)($certidao['status'] ?? 1) === 1);

        return $estaAtiva
            && (int)$certidao['id_fornecedor'] === (int)$fornecedor
            && (int)$certidao['id_tipo_certidao'] === (int)$tipo;
    }

    private function exigirAdministrador()
    {
        if (($_SESSION['usuario_tipo'] ?? '') === 'admin') {
            return;
        }

        definir_flash(
            'erro',
            'Acesso negado',
            'Somente administradores podem excluir certidões permanentemente.',
            'Você ainda pode editar ou arquivar o registro, se necessário.'
        );
        redirect('/certidao');
        exit;
    }

    private function redirecionarRetornoPdf($id)
    {
        $origem = $_GET['origem'] ?? 'lista';

        if ($origem === 'arquivo') {
            $ano = $_GET['ano'] ?? date('Y');
            redirect('/certidao/arquivadas?ano=' . urlencode($ano));
            exit;
        }

        if ($origem === 'editar') {
            redirect('/certidao/editar/' . (int)$id);
            exit;
        }

        redirect('/certidao');
        exit;
    }

    private function processarUploadPdf($arquivoAtual = null)
    {
        $this->erroUploadPdf = '';

        if (!isset($_FILES['arquivo_pdf']) || $_FILES['arquivo_pdf']['error'] === UPLOAD_ERR_NO_FILE) {
            return $arquivoAtual;
        }

        if ($_FILES['arquivo_pdf']['error'] !== UPLOAD_ERR_OK) {
            $this->erroUploadPdf = $this->mensagemErroUpload((int)$_FILES['arquivo_pdf']['error']);
            return false;
        }

        $extensao = strtolower(pathinfo($_FILES['arquivo_pdf']['name'], PATHINFO_EXTENSION));
        if ($extensao !== 'pdf') {
            $this->erroUploadPdf = 'Selecione um arquivo no formato PDF.';
            return false;
        }

        $limiteBytes = (int)($_ENV['CERTIDAO_PDF_MAX_BYTES'] ?? getenv('CERTIDAO_PDF_MAX_BYTES') ?: 0);
        $tamanhoArquivo = (int)($_FILES['arquivo_pdf']['size'] ?? 0);
        if ($limiteBytes > 0 && $tamanhoArquivo > $limiteBytes) {
            $this->erroUploadPdf = 'O PDF excede o limite de tamanho configurado para o sistema.';
            return false;
        }

        $tmpPath = $_FILES['arquivo_pdf']['tmp_name'];
        if (!is_uploaded_file($tmpPath)) {
            $this->erroUploadPdf = 'O arquivo enviado não pôde ser validado pelo servidor.';
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
            $this->erroUploadPdf = 'O arquivo selecionado não contém um PDF válido.';
            return false;
        }

        $diretorio = $this->getDiretorioUploadsCertidoes();
        if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true) && !is_dir($diretorio)) {
            $this->erroUploadPdf = 'O servidor não conseguiu preparar a pasta de documentos.';
            return false;
        }

        if (!is_writable($diretorio)) {
            $this->erroUploadPdf = 'A pasta de documentos não possui permissão de escrita.';
            return false;
        }

        try {
            $novoNome = 'cert_' . bin2hex(random_bytes(16)) . '.pdf';
        } catch (Throwable $e) {
            error_log('Falha ao gerar nome seguro para PDF de certidão: ' . $e->getMessage());
            $this->erroUploadPdf = 'Não foi possível preparar o arquivo para armazenamento.';
            return false;
        }

        $destino = $diretorio . $novoNome;

        if (!move_uploaded_file($_FILES['arquivo_pdf']['tmp_name'], $destino)) {
            $this->erroUploadPdf = 'O servidor não conseguiu salvar o PDF enviado.';
            return false;
        }

        return $novoNome;
    }

    private function getDiretorioUploadsCertidoes()
    {
        $configurado = trim((string)($_ENV['CERTIDOES_UPLOAD_PATH'] ?? getenv('CERTIDOES_UPLOAD_PATH') ?: ''));
        $diretorio = $configurado !== ''
            ? $configurado
            : ROOT_PATH . '/public/uploads/certidoes';

        return rtrim($diretorio, "\\/") . DIRECTORY_SEPARATOR;
    }

    private function localizarArquivoPdf($nomeArquivo)
    {
        $nomeSeguro = basename((string)$nomeArquivo);
        if ($nomeSeguro === '' || $nomeSeguro === '.' || $nomeSeguro === '..') {
            return null;
        }

        $diretorios = [
            $this->getDiretorioUploadsCertidoes(),
            ROOT_PATH . '/public/uploads/certidoes/'
        ];

        foreach (array_unique($diretorios) as $diretorio) {
            $caminho = rtrim($diretorio, "\\/") . DIRECTORY_SEPARATOR . $nomeSeguro;
            if (is_file($caminho)) {
                return $caminho;
            }
        }

        return null;
    }

    private function removerArquivoPdf($nomeArquivo)
    {
        $caminho = $this->localizarArquivoPdf($nomeArquivo);
        if ($caminho === null) {
            return true;
        }

        if (@unlink($caminho)) {
            return true;
        }

        error_log('Não foi possível remover o PDF de certidão: ' . $caminho);
        return false;
    }

    private function mensagemErroUpload($codigo)
    {
        $mensagens = [
            UPLOAD_ERR_INI_SIZE => 'O PDF excede o limite permitido pelo servidor.',
            UPLOAD_ERR_FORM_SIZE => 'O PDF excede o limite permitido pelo formulário.',
            UPLOAD_ERR_PARTIAL => 'O envio do PDF foi interrompido antes de terminar.',
            UPLOAD_ERR_NO_TMP_DIR => 'O servidor está sem a pasta temporária necessária para o upload.',
            UPLOAD_ERR_CANT_WRITE => 'O servidor não conseguiu gravar o PDF no disco.',
            UPLOAD_ERR_EXTENSION => 'Uma extensão do servidor interrompeu o envio do PDF.'
        ];

        return $mensagens[$codigo] ?? 'Não foi possível receber o PDF enviado.';
    }
}
