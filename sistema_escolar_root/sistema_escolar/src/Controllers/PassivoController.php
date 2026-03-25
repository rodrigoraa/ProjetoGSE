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
        $caixa_atual = $_GET['filtro_caixa'] ?? '';
        $lista_caixas = $this->passivoModel->getListaCaixas();
        $resumo_caixas = $this->passivoModel->getResumoCaixas();

        if (empty($termo) && empty($caixa_atual) && !empty($lista_caixas)) {
            $caixa_atual = $lista_caixas[0];
        }

        $resultados = [];
        $nav_caixas = [];
        $modo_exibicao = 'dashboard';

        if (!empty($caixa_atual) && !empty($lista_caixas)) {
            $indice = array_search($caixa_atual, $lista_caixas, true);

            if ($indice !== false) {
                $total = count($lista_caixas);
                $nav_caixas['prev'] = ($indice > 0) ? $lista_caixas[$indice - 1] : null;
                $nav_caixas['next'] = ($indice < $total - 1) ? $lista_caixas[$indice + 1] : null;

                $range = 3;
                $inicio = max(0, $indice - $range);
                $fim = min($total - 1, $indice + $range);
                $nav_caixas['lista_visual'] = array_slice($lista_caixas, $inicio, ($fim - $inicio) + 1);
            }
        }

        if (!empty($termo)) {
            $modo_exibicao = 'busca_global';
            $resultados = $this->passivoModel->buscar($termo);
        } elseif (!empty($caixa_atual)) {
            $modo_exibicao = 'conteudo_caixa';
            $resultados = $this->passivoModel->buscar('', $caixa_atual);
        }

        $this->view('passivo/index', [
            'resultados' => $resultados,
            'termo' => $termo,
            'caixa_atual' => $caixa_atual,
            'lista_caixas' => $lista_caixas,
            'resumo_caixas' => $resumo_caixas,
            'modo_exibicao' => $modo_exibicao,
            'nav_caixas' => $nav_caixas
        ]);
    }

    public function cadastrar()
    {
        $mensagem = '';
        if (isset($_GET['status']) && $_GET['status'] == 'sucesso') {
            $mensagem = '<div class="alert success">Registro salvo com sucesso!</div>';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $nome = trim($_POST['nome']);
            $caixa = trim($_POST['caixa']);

            if (empty($nome) || empty($caixa)) {
                $mensagem = '<p class="error-message">Nome e Caixa sao obrigatorios.</p>';
            } else {
                $id = $this->passivoModel->cadastrar($nome, $_POST['data_nascimento'], $_POST['numero'], $caixa);
                if ($id) {
                    registrar_log(Model::getConexao(), "Passivo - Cadastrar", "Adicionou: $nome na Caixa $caixa");
                    redirect('/passivo/cadastrar?status=sucesso');
                    exit;
                }
                $mensagem = '<p class="error-message">Erro tecnico ao salvar no banco.</p>';
            }
        }
        $this->view('passivo/cadastrar', ['mensagem' => $mensagem]);
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

            if ($this->passivoModel->atualizar($id, $_POST['nome'], $_POST['data_nascimento'], $_POST['numero'], $_POST['caixa'])) {
                registrar_log(Model::getConexao(), "Passivo - Editar", "ID: $id editado.");
                redirect('/passivo?filtro_caixa=' . urlencode($_POST['caixa']));
                exit;
            }
            $mensagem = '<p class="error-message">Erro ao atualizar dados.</p>';
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
            redirect('/passivo');
            exit;
        }

        $reg = $this->passivoModel->buscarPorId($id);
        if ($this->passivoModel->excluir($id)) {
            registrar_log(Model::getConexao(), "Passivo - Excluir", "Removido ID: $id (" . ($reg['nome_completo'] ?? 'N/A') . ")");
        }

        $url_retorno = !empty($reg['caixa']) ? '/passivo?filtro_caixa=' . urlencode($reg['caixa']) : '/passivo';
        redirect($url_retorno);
    }

    public function ferramentas()
    {
        $mensagem = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $caixa = trim($_POST['caixa']);
            $acao = $_POST['acao'];

            if ($acao == 'enumerar') {
                $qtd = $this->passivoModel->enumerarCaixa($caixa);
                $mensagem = ($qtd !== false)
                    ? "<p class='success-message'>Sucesso: $qtd alunos ordenados na caixa $caixa.</p>"
                    : "<p class='error-message'>Erro: Verifique se ha alunos sem numero nesta caixa.</p>";
            } elseif ($acao == 'baixar_txt') {
                $this->gerarArquivoTexto($caixa);
            }
        }
        $this->view('passivo/ferramentas', ['mensagem' => $mensagem]);
    }

    private function gerarArquivoTexto($caixa)
    {
        $lista = $this->passivoModel->listarParaTxt($caixa);
        if (!$lista) {
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="listagem_caixa_' . $caixa . '.txt"');

        foreach ($lista as $l) {
            echo str_pad($l['numero'], 3, '0', STR_PAD_LEFT) . " - " . $l['nome_completo'] . "\r\n";
        }
        exit;
    }
}
