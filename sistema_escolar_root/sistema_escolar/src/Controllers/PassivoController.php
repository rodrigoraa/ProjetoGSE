<?php
require_once ROOT_PATH . '/src/Models/Passivo.php';

class PassivoController extends Controller
{
    private $passivoModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }
        $this->passivoModel = new Passivo();
    }

    public function index()
    {
        $termo = $_GET['busca'] ?? '';
        $caixaAtual = $_GET['filtro_caixa'] ?? '';
        $listaCaixas = $this->passivoModel->getListaCaixas();
        $resumoCaixas = $this->passivoModel->getResumoCaixas();

        if (empty($termo) && empty($caixaAtual) && !empty($listaCaixas)) {
            $caixaAtual = $listaCaixas[0];
        }

        $resultados = [];
        $navCaixas = [];
        $modoExibicao = 'dashboard';

        if (!empty($caixaAtual) && !empty($listaCaixas)) {
            $indice = array_search($caixaAtual, $listaCaixas, true);

            if ($indice !== false) {
                $total = count($listaCaixas);
                $navCaixas['prev'] = ($indice > 0) ? $listaCaixas[$indice - 1] : null;
                $navCaixas['next'] = ($indice < $total - 1) ? $listaCaixas[$indice + 1] : null;

                $range = 3;
                $inicio = max(0, $indice - $range);
                $fim = min($total - 1, $indice + $range);
                $navCaixas['lista_visual'] = array_slice($listaCaixas, $inicio, ($fim - $inicio) + 1);
            }
        }

        if (!empty($termo)) {
            $modoExibicao = 'busca_global';
            $resultados = $this->passivoModel->buscar($termo);
        } elseif (!empty($caixaAtual)) {
            $modoExibicao = 'conteudo_caixa';
            $resultados = $this->passivoModel->buscar('', $caixaAtual);
        }

        $this->view('passivo/index', [
            'resultados' => $resultados,
            'termo' => $termo,
            'caixa_atual' => $caixaAtual,
            'lista_caixas' => $listaCaixas,
            'resumo_caixas' => $resumoCaixas,
            'modo_exibicao' => $modoExibicao,
            'nav_caixas' => $navCaixas
        ]);
    }

    public function cadastrar()
    {
        $mensagem = '';
        $dados = [
            'nome' => '',
            'data_nascimento' => '',
            'numero' => '',
            'caixa' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $nome = trim($_POST['nome'] ?? '');
            $caixa = trim($_POST['caixa'] ?? '');
            $dataNascimento = trim($_POST['data_nascimento'] ?? '');
            $numero = trim($_POST['numero'] ?? '');
            $dados = $_POST;

            if ($nome === '' || $caixa === '') {
                $mensagem = alerta_html(
                    'erro',
                    'Faltam informaÃ§Ãµes obrigatÃ³rias',
                    'Para salvar o registro, informe pelo menos o nome completo e a caixa de arquivamento.',
                    'Preencha os campos destacados e tente novamente.'
                );
            } elseif (!$this->isDataValida($dataNascimento)) {
                $mensagem = alerta_html(
                    'erro',
                    'Data de nascimento inválida',
                    'A data informada não está em um formato válido para cadastro.',
                    'Revise o campo de data ou deixe-o em branco.'
                );
            } else {
                $id = $this->passivoModel->cadastrar($nome, $dataNascimento, $numero, $caixa);
                if ($id) {
                    registrar_log(Model::getConexao(), 'Passivo - Cadastrar', "Adicionou: $nome na Caixa $caixa");
                    definir_flash(
                        'sucesso',
                        'Registro salvo com sucesso',
                        'O ex-aluno foi adicionado ao arquivo passivo.',
                        'Se desejar, você pode cadastrar outro registro ou voltar para localizar a caixa.'
                    );
                    redirect('/passivo/cadastrar');
                    exit;
                }

                $mensagem = alerta_html(
                    'erro',
                    'NÃ£o foi possÃ­vel salvar o registro',
                    'O sistema encontrou um problema ao gravar este ex-aluno no arquivo passivo.',
                    'Tente novamente. Se continuar falhando, confira se jÃ¡ existe um cadastro parecido ou avise o suporte.'
                );
            }
        }

        $this->view('passivo/cadastrar', ['mensagem' => $mensagem, 'd' => $dados]);
    }

    public function editar($id)
    {
        $reg = $this->passivoModel->buscarPorId($id);
        if (!$reg) {
            redirect('/passivo');
            exit;
        }

        $mensagem = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $nome = trim($_POST['nome'] ?? '');
            $caixa = trim($_POST['caixa'] ?? '');
            $dataNascimento = trim($_POST['data_nascimento'] ?? '');
            $numero = trim($_POST['numero'] ?? '');

            if ($nome === '' || $caixa === '') {
                $mensagem = alerta_html(
                    'erro',
                    'Faltam informações obrigatórias',
                    'Para atualizar o registro, informe pelo menos o nome completo e a caixa de arquivamento.',
                    'Preencha os campos destacados e tente novamente.'
                );
            } elseif (!$this->isDataValida($dataNascimento)) {
                $mensagem = alerta_html(
                    'erro',
                    'Data de nascimento inválida',
                    'A data informada não está em um formato válido para atualização.',
                    'Revise o campo de data ou deixe-o em branco.'
                );
            } elseif ($this->passivoModel->atualizar($id, $nome, $dataNascimento, $numero, $caixa)) {
                registrar_log(Model::getConexao(), 'Passivo - Editar', "ID: $id editado.");
                definir_flash(
                    'sucesso',
                    'Registro atualizado com sucesso',
                    'As informaÃ§Ãµes do arquivo passivo foram salvas.',
                    'VocÃª foi levado de volta para a caixa selecionada para continuar a consulta.'
                );
                redirect('/passivo?filtro_caixa=' . urlencode($caixa));
                exit;
            } else {
                $mensagem = alerta_html(
                    'erro',
                    'NÃ£o foi possÃ­vel atualizar o registro',
                    'As alteraÃ§Ãµes nÃ£o foram salvas no arquivo passivo.',
                    'Revise os campos preenchidos e tente novamente.'
                );
            }

            $reg = array_merge($reg, [
                'nome_completo' => $nome,
                'data_nascimento' => $dataNascimento,
                'numero' => $numero,
                'caixa' => $caixa
            ]);
        }

        $this->view('passivo/editar', ['reg' => $reg, 'mensagem' => $mensagem]);
    }

    public function excluir($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/passivo');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        if ($_SESSION['usuario_tipo'] !== 'admin') {
            definir_flash(
                'erro',
                'VocÃª nÃ£o tem permissÃ£o para apagar registros',
                'Somente administradores podem excluir itens do arquivo passivo.',
                'Se a exclusÃ£o for realmente necessÃ¡ria, solicite apoio a um administrador.'
            );
            redirect('/passivo');
            exit;
        }

        $reg = $this->passivoModel->buscarPorId($id);
        if ($this->passivoModel->excluir($id)) {
            registrar_log(Model::getConexao(), 'Passivo - Excluir', 'Removido ID: ' . $id . ' (' . ($reg['nome_completo'] ?? 'N/A') . ')');
            definir_flash(
                'sucesso',
                'Registro removido com sucesso',
                'O item selecionado foi apagado do arquivo passivo.',
                'Se isso foi um engano, serÃ¡ preciso cadastrar o registro novamente.'
            );
        }

        $urlRetorno = !empty($reg['caixa']) ? '/passivo?filtro_caixa=' . urlencode($reg['caixa']) : '/passivo';
        redirect($urlRetorno);
    }

    public function ferramentas()
    {
        $mensagem = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $caixa = trim($_POST['caixa'] ?? '');
            $acao = $_POST['acao'] ?? '';

            if ($caixa === '') {
                $mensagem = alerta_html(
                    'erro',
                    'Informe uma caixa válida',
                    'A ferramenta precisa do nome da caixa para continuar.',
                    'Digite a identificação da caixa e tente novamente.'
                );
            } elseif ($acao == 'enumerar') {
                $qtd = $this->passivoModel->enumerarCaixa($caixa);
                $mensagem = ($qtd !== false)
                    ? alerta_html(
                        'sucesso',
                        'Caixa organizada com sucesso',
                        "$qtd alunos foram numerados na caixa $caixa.",
                        'Revise a listagem antes de imprimir ou exportar o conteÃºdo.'
                    )
                    : alerta_html(
                        'erro',
                        'NÃ£o foi possÃ­vel numerar a caixa',
                        "Existem registros na caixa $caixa sem nÃºmero vÃ¡lido ou fora da sequÃªncia esperada.",
                        'Confira os cadastros dessa caixa, preencha os nÃºmeros faltantes e tente novamente.'
                    );
            } elseif ($acao == 'baixar_txt') {
                $this->gerarArquivoTexto($caixa);
            } else {
                $mensagem = alerta_html(
                    'erro',
                    'Ação inválida',
                    'A ferramenta solicitada não é reconhecida pelo sistema.',
                    'Atualize a página e tente novamente.'
                );
            }
        }

        $this->view('passivo/ferramentas', ['mensagem' => $mensagem]);
    }

    public function importar()
    {
        if ($_SESSION['usuario_tipo'] !== 'admin') {
            definir_flash(
                'erro',
                'Acesso negado',
                'Somente administradores podem importar um novo arquivo passivo.',
                'Se vocÃª precisa realizar essa importaÃ§Ã£o, entre com uma conta administradora.'
            );
            redirect('/passivo');
            exit;
        }

        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            if (
                !isset($_FILES['arquivo_csv'])
                || !is_array($_FILES['arquivo_csv'])
                || ($_FILES['arquivo_csv']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            ) {
                $mensagem = alerta_html(
                    'erro',
                    'NÃ£o foi possÃ­vel enviar o arquivo',
                    'Selecione um arquivo CSV vÃ¡lido antes de iniciar a importaÃ§Ã£o.',
                    'Confira se o arquivo foi escolhido corretamente e tente novamente.'
                );
            } else {
                $arquivo = $_FILES['arquivo_csv'];
                $nomeArquivo = $arquivo['name'] ?? '';
                $tmpPath = $arquivo['tmp_name'] ?? '';
                $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

                if ($extensao !== 'csv') {
                    $mensagem = alerta_html(
                        'erro',
                        'Formato de arquivo invÃ¡lido',
                        'A importaÃ§Ã£o do arquivo passivo aceita apenas arquivos com extensÃ£o .csv.',
                        'Exporte ou salve a planilha novamente em CSV e tente outra vez.'
                    );
                } elseif (empty($tmpPath) || !is_uploaded_file($tmpPath)) {
                    $mensagem = alerta_html(
                        'erro',
                        'Arquivo temporÃ¡rio indisponÃ­vel',
                        'O upload nÃ£o ficou disponÃ­vel para leitura no servidor.',
                        'Envie o arquivo novamente. Se o problema persistir, verifique o tamanho do arquivo.'
                    );
                } elseif ($this->passivoModel->importarCSV($tmpPath)) {
                    registrar_log(Model::getConexao(), 'Passivo - Importar CSV', "Importou arquivo: {$nomeArquivo}");
                    definir_flash(
                        'sucesso',
                        'ImportaÃ§Ã£o concluÃ­da com sucesso',
                        'O arquivo passivo foi substituÃ­do pelos dados do CSV enviado.',
                        'Revise a listagem das caixas para confirmar se os registros foram importados corretamente.'
                    );
                    redirect('/passivo');
                    exit;
                } else {
                    $mensagem = alerta_html(
                        'erro',
                        'NÃ£o foi possÃ­vel importar o CSV',
                        'O sistema encontrou um problema ao processar o arquivo enviado.',
                        'Confirme se o arquivo usa ponto e vÃ­rgula, possui as colunas Nome; Data; NÃºmero; Caixa e tente novamente.'
                    );
                }
            }
        }

        $this->view('passivo/importar', ['mensagem' => $mensagem]);
    }

    private function gerarArquivoTexto($caixa)
    {
        $lista = $this->passivoModel->listarParaTxt($caixa);
        if (!$lista) {
            definir_flash(
                'erro',
                'Nenhum registro encontrado para exportação',
                "A caixa {$caixa} não possui itens disponíveis para gerar o arquivo TXT.",
                'Confira o nome informado e tente novamente.'
            );
            redirect('/passivo/ferramentas');
            exit;
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="listagem_caixa_' . $caixa . '.txt"');

        foreach ($lista as $l) {
            echo str_pad($l['numero'], 3, '0', STR_PAD_LEFT) . ' - ' . $l['nome_completo'] . "\r\n";
        }
        exit;
    }

    private function isDataValida($data)
    {
        if ($data === '') {
            return true;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $data);
        return $dt && $dt->format('Y-m-d') === $data;
    }
}
