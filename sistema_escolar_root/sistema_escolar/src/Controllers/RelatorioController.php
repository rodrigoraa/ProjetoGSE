<?php
require_once ROOT_PATH . '/src/Lib/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require_once ROOT_PATH . '/src/Core/Controller.php';
require_once ROOT_PATH . '/src/Models/Relatorio.php';

class RelatorioController extends Controller
{

    public function index()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        $model = new Relatorio();
        $turmas = $model->getTurmas();
        $this->view('relatorios/index', ['turmas' => $turmas]);
    }

    public function gerar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        $id_turma = $_POST['turma'] ?? '';
        $status   = $_POST['status'] ?? 'todos';
        $formato  = $_POST['formato'] ?? 'pdf';

        $model = new Relatorio();
        $dados = $model->buscarDados($id_turma, $status);

        if ($formato === 'excel') {
            $this->gerarExcel($dados);
        } else {
            $this->gerarPDF($dados, $status);
        }
    }

    private function gerarExcel($dados)
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        if (ob_get_level()) ob_end_clean();

        $filename = "relatorio_alunos_" . date('Y-m-d') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Nome do Aluno', 'Data Nasc.', 'Turma', 'Vencimento DVA', 'Status'], ';', '"', "");

        foreach ($dados as $linha) {
            $nascimento = $linha['data_nascimento'] ? date('d/m/Y', strtotime($linha['data_nascimento'])) : '-';
            $venc = $linha['data_vencimento'] ? date('d/m/Y', strtotime($linha['data_vencimento'])) : '-';

            $st = 'Sem DVA';
            if ($linha['data_vencimento']) {
                $hoje = date('Y-m-d');
                if ($linha['data_vencimento'] < $hoje) {
                    $st = 'Vencida';
                } elseif ($linha['data_vencimento'] <= date('Y-m-d', strtotime('+30 days'))) {
                    $st = 'A Vencer';
                } else {
                    $st = 'Vigente';
                }
            }

            fputcsv($output, [
                $linha['nome_completo'],
                $nascimento,
                $linha['nome_turma'] ?? 'Sem Turma',
                $venc,
                $st
            ], ';', '"', "");
        }

        fclose($output);
        exit;
    }

    private function gerarPDF($dados, $statusFiltro)
    {
        $dompdf = new Dompdf();

        $titulos = [
            'todos' => 'Geral (Todos)',
            'sem_dva' => 'Pendentes (Sem DVA)',
            'vencida' => 'Vencidas',
            'avencer' => 'A Vencer (30 dias)',
            'vigente' => 'Vigentes'
        ];
        $tituloStatus = $titulos[$statusFiltro] ?? 'Relatório';

        $html = '
        <html>
        <head>
            <style>
                body { font-family: sans-serif; font-size: 11px; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th { background-color: #004a91; color: white; padding: 8px; text-align: left; }
                td { border-bottom: 1px solid #ddd; padding: 8px; color: #333; }
                h2 { color: #333; text-align: center; margin-bottom: 5px; }
                .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px;}
                .status-danger { color: #dc3545; font-weight: bold; }
                .status-warning { color: #d39e00; font-weight: bold; }
                .text-center { text-align: center; }
            </style>
        </head>
        <body>
            <h2>Relatório de Alunos: ' . mb_strtoupper($tituloStatus) . '</h2>
            <p style="text-align:center; font-size:10px; color:#666;">Gerado em: ' . date('d/m/Y H:i') . '</p>
            
            <table>
                <thead>
                    <tr>
                        <th width="5%" class="text-center">Nº</th>
                        <th width="35%">Aluno</th>
                        <th width="15%" class="text-center">Nascimento</th>
                        <th width="25%">Turma</th>
                        <th width="20%">Vencimento</th>
                    </tr>
                </thead>
                <tbody>';

        if (empty($dados)) {
            $html .= '<tr><td colspan="5" style="text-align:center; padding:20px;">Nenhum registro encontrado para este filtro.</td></tr>';
        } else {
            $i = 1;
            foreach ($dados as $d) {
                $nasc = $d['data_nascimento'] ? date('d/m/Y', strtotime($d['data_nascimento'])) : '-';
                $venc = $d['data_vencimento'] ? date('d/m/Y', strtotime($d['data_vencimento'])) : 'Sem DVA';

                $class = '';
                if ($d['data_vencimento'] && $d['data_vencimento'] < date('Y-m-d')) {
                    $class = 'class="status-danger"';
                } elseif ($d['data_vencimento'] && $d['data_vencimento'] <= date('Y-m-d', strtotime('+30 days'))) {
                    $class = 'class="status-warning"';
                }

                $html .= '<tr>
                            <td class="text-center">' . $i++ . '</td>
                            <td>' . htmlspecialchars($d['nome_completo']) . '</td>
                            <td class="text-center">' . $nasc . '</td>
                            <td>' . htmlspecialchars($d['nome_turma'] ?? 'Sem Turma') . '</td>
                            <td ' . $class . '>' . $venc . '</td>
                          </tr>';
            }
        }

        $html .= '</tbody></table>
                  <div class="footer">Sistema de Gestão Escolar • Documento Interno</div>
                  </body></html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("relatorio_$statusFiltro.pdf", ["Attachment" => false]);
        exit;
    }
}