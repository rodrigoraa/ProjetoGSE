<?php
require_once ROOT_PATH . '/src/Models/Painel.php';
require_once ROOT_PATH . '/src/Models/Aluno.php';
require_once ROOT_PATH . '/src/Core/Automacao.php';
require_once ROOT_PATH . '/src/Models/Certidao.php';

class PainelController extends Controller
{

    public function index()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        $msg_backup = $this->executarBackupAutomatico();

        $painelModel = new Painel();
        $alunoModel = new Aluno();

        $certidaoModel = new Certidao();
        $certidoes_alerta = $certidaoModel->buscarVencendoProximosDias(30);

        $mesAtual = date('m');
        $diaHoje = date('d');
        $anoAtual = date('Y');

        $lista_aniversariantes_mes = $alunoModel->getAniversariantesDoMes($mesAtual);

        $lista_hoje = $alunoModel->getAniversariantesHoje($diaHoje, $mesAtual);
        $aniversariantes_hoje = [];

        foreach ($lista_hoje as $aluno) {
            $ano_nasc = date('Y', strtotime($aluno['data_nascimento']));
            $aluno['idade_nova'] = $anoAtual - $ano_nasc;
            $aniversariantes_hoje[] = $aluno;
        }

        $dados = [
            'nome_usuario' => $_SESSION['usuario_nome'],
            'tipo_usuario' => $_SESSION['usuario_tipo'],
            'msg_backup'   => $msg_backup,

            'total_alunos'   => $painelModel->getTotalAlunos(),
            'total_sem_dva'  => $painelModel->getTotalSemDva(),

            'lista_sem_dva'  => $painelModel->getListaAlunosSemDva(),

            'lista_vencidas' => $painelModel->getDvasVencidas(),
            'lista_avencer'  => $painelModel->getDvasAVencer(),
            'lista_vigentes' => $painelModel->getDvasVigentes(),

            'aniversariantes_mes'  => $lista_aniversariantes_mes,
            'aniversariantes_hoje' => $aniversariantes_hoje,

            'certidoes_alerta' => $certidoes_alerta
        ];

        $dados['total_vencidas'] = count($dados['lista_vencidas']);
        $dados['total_avencer']  = count($dados['lista_avencer']);

        $this->view('painel', $dados);
    }

    private function executarBackupAutomatico()
    {
        $msg = '';
        $pasta_backup = ROOT_PATH . '/database/backups/';
        $arquivo_banco = ROOT_PATH . '/database/secretaria.db';
        $nome_hoje = 'escola_backup_' . date('Y-m-d');
        $limite_manter = 5;

        $backups_hoje = glob($pasta_backup . $nome_hoje . '*.db');

        if (empty($backups_hoje)) {
            if (!is_dir($pasta_backup)) {
                mkdir($pasta_backup, 0755, true);
            }
            $novo_nome = $nome_hoje . '_' . date('H-i-s') . '.db';

            if (copy($arquivo_banco, $pasta_backup . $novo_nome)) {
                $msg = "Backup diário realizado com sucesso!";

                $nuvem = 'G:/Meu Drive/Backups_Escola/';
                if (is_dir($nuvem)) {
                    copy($arquivo_banco, $nuvem . $novo_nome);
                }

                $todos = glob($pasta_backup . '*.db');
                if (count($todos) > $limite_manter) {
                    usort($todos, function ($a, $b) {
                        return filemtime($b) - filemtime($a);
                    });
                    $excedentes = array_slice($todos, $limite_manter);
                    foreach ($excedentes as $arq) {
                        if (file_exists($arq)) unlink($arq);
                    }
                }
            }
        }
        return $msg;
    }
}
