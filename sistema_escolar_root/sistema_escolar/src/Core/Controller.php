<?php
class Controller
{
    public function view($viewName, $data = [])
    {
        if (!empty($data)) {
            extract($data);
        }

        $viewFile = VIEW_PATH . '/' . $viewName . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            error_log("Erro de View: O arquivo de view '{$viewName}' não foi encontrado no caminho esperado: {$viewFile}");

            $this->mostrarErroView();
        }
    }

    private function mostrarErroView()
    {
        header("HTTP/1.1 500 Internal Server Error");
        echo "<div style='font-family: sans-serif; text-align: center; margin-top: 10%; color: #334155;'>";
        echo "<h1>Erro de Apresentação</h1>";
        echo "<p>Desculpe, ocorreu um problema ao tentar carregar a interface desta página. Nossa equipe já foi notificada.</p>";
        echo "<a href='/' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #0f172a; color: white; text-decoration: none; border-radius: 6px;'>Voltar para o Início</a>";
        echo "</div>";
        exit;
    }
}
