<?php
require_once ROOT_PATH . '/src/Lib/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require_once ROOT_PATH . '/src/Core/Controller.php';
require_once ROOT_PATH . '/src/Models/Relatorio.php';

class RelatorioController extends Controller
{
    private $model;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }
        $this->model = new Relatorio();
    }

    public function index()
    {
        $turmas = $this->model->getTurmas();
        $this->view('relatorios/index', ['turmas' => $turmas]);
    }

    public function gerar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/relatorios');
            exit;
        }

        $id_turma = $_POST['turma'] ?? '';
        $status   = $_POST['status'] ?? 'todos';
        $formato  = $_POST['formato'] ?? 'pdf';

        $dados = $this->model->buscarDados($id_turma, $status);

        if ($formato === 'excel') {
            $this->gerarCSV($dados);
        } else {
            $this->gerarPDF($dados, $status);
        }
    }

    private function gerarCSV($dados)
    {
        if (ob_get_level()) ob_end_clean();

        $filename = "relatorio_alunos_" . date('Y-m-d_Hi') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Nome do Aluno', 'Data Nasc.', 'Turma', 'Vencimento DVA', 'Situação'], ';');

        foreach ($dados as $linha) {
            $nascimento = $linha['data_nascimento'] ? date('d/m/Y', strtotime($linha['data_nascimento'])) : '-';
            $vencimento = $linha['data_vencimento'] ? date('d/m/Y', strtotime($linha['data_vencimento'])) : 'Pendente';

            $situacao = $this->calcularSituacao($linha['data_vencimento']);

            fputcsv($output, [
                $linha['nome_completo'],
                $nascimento,
                $linha['nome_turma'] ?? 'Sem Turma',
                $vencimento,
                $situacao
            ], ';');
        }

        fclose($output);
        exit;
    }

    private function gerarPDF($dados, $statusFiltro)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $titulos = [
            'todos'   => 'Geral (Todos os Alunos)',
            'sem_dva' => 'Pendentes (Sem DVA Cadastrada)',
            'vencida' => 'Documentação Vencida',
            'avencer' => 'A Vencer (Próximos 30 dias)',
            'vigente' => 'Documentação Vigente'
        ];

        $tituloRelatorio = $titulos[$statusFiltro] ?? 'Relatório de Alunos';

        ob_start();
?>
        <html>

        <head>
            <style>
                body {
                    font-family: 'Helvetica', sans-serif;
                    font-size: 11px;
                    margin: 30px;
                }

                .header-table {
                    width: 100%;
                    border-bottom: 2px solid #004a91;
                    margin-bottom: 20px;
                }

                .title {
                    font-size: 18px;
                    color: #004a91;
                    font-weight: bold;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                }

                th {
                    background-color: #f2f2f2;
                    color: #333;
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #000;
                }

                td {
                    padding: 8px;
                    border-bottom: 0.5px solid #eee;
                }

                .text-center {
                    text-align: center;
                }

                .status-vencida {
                    color: #d9534f;
                    font-weight: bold;
                }

                .status-avencer {
                    color: #f0ad4e;
                    font-weight: bold;
                }

                .footer {
                    position: fixed;
                    bottom: 0;
                    width: 100%;
                    font-size: 9px;
                    text-align: center;
                    border-top: 1px solid #ccc;
                    padding: 5px;
                }
            </style>
        </head>

        <body>
            <table class="header-table">
                <tr>
                    <td><span class="title">GSE - EE SÃO JOSÉ</span></td>
                    <td style="text-align: right;">Gerado em: <?php echo date('d/m/Y H:i'); ?></td>
                </tr>
            </table>

            <h2 style="text-align: center;"><?php echo mb_strtoupper($tituloRelatorio); ?></h2>

            <table>
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="35%">Nome do Aluno</th>
                        <th width="15%">Data Nasc.</th>
                        <th width="10%">Turma</th>
                        <th width="18%">Vencimento DVA</th>
                        <th width="17%">Situação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dados)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Nenhum registro encontrado.</td>
                        </tr>
                        <?php else: $i = 1;
                        foreach ($dados as $d):
                            $situacao = $this->calcularSituacao($d['data_vencimento']);
                            $class = ($situacao == 'Vencida') ? 'status-vencida' : (($situacao == 'A Vencer') ? 'status-avencer' : '');

                            // Criamos a variável para formatar a data que vem do banco
                            $nascimento = $d['data_nascimento'] ? date('d/m/Y', strtotime($d['data_nascimento'])) : '-';
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($d['nome_completo']); ?></td>

                                <td class="text-center"><?php echo $nascimento; ?></td>

                                <td><?php echo htmlspecialchars($d['nome_turma'] ?? '-'); ?></td>
                                <td><?php echo $d['data_vencimento'] ? date('d/m/Y', strtotime($d['data_vencimento'])) : '-'; ?></td>
                                <td class="<?php echo $class; ?>"><?php echo $situacao; ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
            <div class="footer">Este relatório é um documento interno e contém dados sensíveis.</div>
        </body>

        </html>
<?php
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("relatorio_escolar.pdf", ["Attachment" => false]);
        exit;
    }

    private function calcularSituacao($data_vencimento)
    {
        if (!$data_vencimento) return 'Sem DVA';

        $hoje = date('Y-m-d');
        $alerta = date('Y-m-d', strtotime('+30 days'));

        if ($data_vencimento < $hoje) return 'Vencida';
        if ($data_vencimento <= $alerta) return 'A Vencer';
        return 'Vigente';
    }
}
