<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este teste deve ser executado pela linha de comando.\n");
}

$rootPath = dirname(__DIR__);
$dbPath = tempnam(sys_get_temp_dir(), 'certidoes_regression_');

if ($dbPath === false) {
    fwrite(STDERR, "Não foi possível criar o banco temporário.\n");
    exit(1);
}

$_ENV['DB_PATH'] = $dbPath;
putenv('DB_PATH=' . $dbPath);

define('ROOT_PATH', $rootPath);
define('VIEW_PATH', ROOT_PATH . '/src/Views');
define('BASE_URL', 'http://localhost');

session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nome'] = 'Teste de regressão';
$_SESSION['usuario_tipo'] = 'admin';
$_SERVER['REQUEST_URI'] = '/certidao/configurar';

require_once ROOT_PATH . '/src/Core/Helpers.php';
require_once ROOT_PATH . '/src/Core/Controller.php';
require_once ROOT_PATH . '/src/Core/Model.php';
require_once ROOT_PATH . '/src/Controllers/CertidaoController.php';

$falhas = [];
$totalVerificacoes = 0;

function verificarCertidao($condicao, $descricao)
{
    global $falhas, $totalVerificacoes;

    $totalVerificacoes++;

    if (!$condicao) {
        $falhas[] = $descricao;
    }
}

try {
    $pdo = Model::getConexao();
    $pdo->exec('CREATE TABLE lista_fornecedores (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE lista_tipos_certidao (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE certidoes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        id_fornecedor INTEGER NOT NULL REFERENCES lista_fornecedores(id),
        id_tipo_certidao INTEGER NOT NULL REFERENCES lista_tipos_certidao(id),
        data_emissao TEXT NOT NULL,
        data_vencimento TEXT NOT NULL,
        observacao TEXT,
        arquivo_pdf TEXT,
        arquivado INTEGER,
        status INTEGER
    )');

    $pdo->exec("INSERT INTO lista_fornecedores(nome) VALUES ('Fornecedor A'), ('Fornecedor B')");
    $pdo->exec("INSERT INTO lista_tipos_certidao(nome) VALUES ('Tipo Misto'), ('Outro Tipo')");

    $model = new Certidao();
    verificarCertidao(
        $model->cadastrar(1, 1, '2026-08-01', '2026-09-01', '', null),
        'cadastro básico'
    );
    verificarCertidao($model->alternarArquivo(1, 1), 'arquivamento');
    verificarCertidao($model->alternarArquivo(1, 1), 'arquivamento idempotente');
    verificarCertidao(!$model->alternarArquivo(999, 1), 'rejeição de ID inexistente');
    verificarCertidao(
        !$model->adicionarOpcaoLista('lista_fornecedores', ' fornecedor a '),
        'rejeição de opção duplicada'
    );

    $controller = new CertidaoController();
    $validarFormulario = new ReflectionMethod(CertidaoController::class, 'validarFormularioCertidao');
    $validarRenovacao = new ReflectionMethod(CertidaoController::class, 'renovacaoCompativel');

    verificarCertidao(
        $validarFormulario->invoke($controller, 1, 1, '2026-08-01', '2026-09-01'),
        'datas válidas'
    );
    verificarCertidao(
        !$validarFormulario->invoke($controller, 1, 1, '2026-08-01', '2026-08-01'),
        'rejeição de emissão e vencimento iguais'
    );
    verificarCertidao(
        !$validarFormulario->invoke($controller, 1, 1, '2026-09-01', '2026-08-01'),
        'rejeição de vencimento anterior à emissão'
    );

    $model->alternarArquivo(1, 0);
    verificarCertidao(
        $validarRenovacao->invoke($controller, 1, 1, 1),
        'renovação do mesmo fornecedor e tipo'
    );
    verificarCertidao(
        !$validarRenovacao->invoke($controller, 1, 2, 1),
        'rejeição de renovação incompatível'
    );

    $fornecedores = [[
        'id' => 1,
        'nome' => 'Fornecedor "A" \'teste\' <script>'
    ]];
    $tipos = [[
        'id' => 1,
        'nome' => 'Tipo "B"'
    ]];

    ob_start();
    include ROOT_PATH . '/src/Views/certidoes/configurar.php';
    $htmlConfiguracao = ob_get_clean();

    verificarCertidao(
        strpos($htmlConfiguracao, 'onclick="abrirModalRenomear') === false,
        'ausência de JavaScript inline com nome configurável'
    );
    verificarCertidao(
        strpos($htmlConfiguracao, 'data-nome="Fornecedor &quot;A&quot; &#039;teste&#039; &lt;script&gt;"') !== false,
        'escape de caracteres especiais na configuração'
    );
    verificarCertidao(
        strpos($htmlConfiguracao, 'data-rename-option') !== false,
        'gatilho seguro para renomear opções'
    );
} catch (Throwable $e) {
    $falhas[] = 'exceção inesperada: ' . $e->getMessage();
}

if ($falhas) {
    fwrite(STDERR, "Falhas no teste de Certidões:\n- " . implode("\n- ", $falhas) . "\n");
    @unlink($dbPath);
    exit(1);
}

@unlink($dbPath);
echo "Certidões: {$totalVerificacoes} verificações de regressão concluídas com sucesso.\n";
