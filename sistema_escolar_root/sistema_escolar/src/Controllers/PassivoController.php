<?php
require_once ROOT_PATH . '/src/Models/Passivo.php';

class PassivoController extends Controller {

    public function index() {
        if (!isset($_SESSION['usuario_id'])) { redirect('/login'); exit; }

        $passivoModel = new Passivo();
        
        $termo = $_GET['busca'] ?? '';
        $caixa_atual = $_GET['filtro_caixa'] ?? '';
        
        $lista_caixas = $passivoModel->getListaCaixas(); 
        
        if (empty($termo) && empty($caixa_atual)) {
            if (!empty($lista_caixas)) {
                $caixa_atual = $lista_caixas[0];
            }
        }

        $resultados = [];
        $resumo_caixas = [];
        $nav_caixas = [];
        $modo_exibicao = 'dashboard'; 

        if (!empty($caixa_atual) && !empty($lista_caixas)) {
            $indice = array_search($caixa_atual, $lista_caixas);
            
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
            $resultados = $passivoModel->buscar($termo);
        } 
        elseif (!empty($caixa_atual)) {
            $modo_exibicao = 'conteudo_caixa';
            $resultados = $passivoModel->buscar('', $caixa_atual);
        }
        else {
            $modo_exibicao = 'dashboard';
            $resumo_caixas = [];
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

    
    public function cadastrar() {
        if (!isset($_SESSION['usuario_id'])) { redirect('/login'); exit; }
        $mensagem = ''; 
        $dados = [];

        if (isset($_GET['status']) && $_GET['status'] == 'sucesso') {
            $mensagem = '<div style="background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">✅ Registro salvo com sucesso! Pronto para o próximo.</div>';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');
            $dados = $_POST;
            $passivoModel = new Passivo();
            
            if (empty($_POST['nome']) || empty($_POST['caixa'])) {
                $mensagem = '<p class="error-message">Nome e Caixa são obrigatórios.</p>';
            } else {
                $id = $passivoModel->cadastrar($_POST['nome'], $_POST['data_nascimento'], $_POST['numero'], $_POST['caixa']);
                
                if ($id) {
                    registrar_log(Model::getConexao(), "Passivo - Cadastrar", "Adicionou: " . $_POST['nome']);
                    
                    redirect('/passivo/cadastrar?status=sucesso');
                    exit;
                } else {
                    $mensagem = '<p class="error-message">Erro ao salvar.</p>';
                }
            }
        }
        $this->view('passivo/cadastrar', ['mensagem' => $mensagem, 'd' => $dados]);
    }

    public function editar($id) {
        if (!isset($_SESSION['usuario_id'])) { redirect('/login'); exit; }
        $passivoModel = new Passivo();
        $reg = $passivoModel->buscarPorId($id);
        if (!$reg) { redirect('/passivo'); exit; }
        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');
            if ($passivoModel->atualizar($id, $_POST['nome'], $_POST['data_nascimento'], $_POST['numero'], $_POST['caixa'])) {
                registrar_log(Model::getConexao(), "Passivo - Editar", "ID: $id");
                redirect('/passivo?filtro_caixa=' . urlencode($_POST['caixa']));
                exit;
            } else {
                $mensagem = '<p class="error-message">Erro ao salvar.</p>';
            }
        }
        $this->view('passivo/editar', ['reg' => $reg, 'mensagem' => $mensagem]);
    }

    public function excluir($id) {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') { redirect('/passivo'); exit; }
        $passivoModel = new Passivo();
        
        $reg = $passivoModel->buscarPorId($id);
        $caixa_retorno = $reg['caixa'] ?? '';

        if ($passivoModel->excluir($id)) {
            registrar_log(Model::getConexao(), "Passivo - Excluir", "ID: $id");
        }
        
        if ($caixa_retorno) {
            redirect('/passivo?filtro_caixa=' . urlencode($caixa_retorno));
        } else {
            redirect('/passivo');
        }
    }

    public function ferramentas() {
        if (!isset($_SESSION['usuario_id'])) { redirect('/login'); exit; }
        $mensagem = '';
        $passivoModel = new Passivo();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $caixa = trim($_POST['caixa']);
            $acao = $_POST['acao'];

            if ($acao == 'enumerar') {
                $qtd = $passivoModel->enumerarCaixa($caixa);
                $mensagem = ($qtd !== false) 
                    ? "<p class='success-message'>$qtd alunos numerados na caixa $caixa.</p>" 
                    : "<p class='error-message'>Erro ou nenhum aluno pendente.</p>";
            } elseif ($acao == 'baixar_txt') {
                $lista = $passivoModel->listarParaTxt($caixa);
                if ($lista) {
                    header('Content-Type: text/plain');
                    header('Content-Disposition: attachment; filename="caixa_'.$caixa.'.txt"');
                    foreach ($lista as $l) { echo $l['numero'] . " - " . $l['nome_completo'] . "\r\n"; }
                    exit;
                } else {
                    $mensagem = "<p class='error-message'>Caixa vazia ou sem numeração.</p>";
                }
            }
        }
        $this->view('passivo/ferramentas', ['mensagem' => $mensagem]);
    }
    
    public function importar() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') { redirect('/passivo'); exit; }
        $mensagem = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['arquivo_csv']) && $_FILES['arquivo_csv']['error'] == 0) {
                $passivoModel = new Passivo();
                if ($passivoModel->importarCSV($_FILES['arquivo_csv']['tmp_name'])) {
                    $mensagem = '<p class="success-message">Importação concluída!</p>';
                } else {
                    $mensagem = '<p class="error-message">Erro na importação.</p>';
                }
            }
        }
        $this->view('passivo/importar', ['mensagem' => $mensagem]);
    }
}