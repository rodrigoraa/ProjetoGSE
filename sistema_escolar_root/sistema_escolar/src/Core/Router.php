<?php

class Router
{
    public function dispatch($url)
    {
        $urlParts = explode('/', rtrim($url, '/'));

        $controller = $urlParts[0] ?? 'painel';

        $controller = preg_replace('/oes$/', 'ao', $controller); // certidoes → certidao
        $controller = preg_replace('/ais$/', 'al', $controller); // opcionais casos
        $controller = rtrim($controller, 's'); // alunos → aluno, usuarios → usuario

        $controllerName = ucfirst($controller) . 'Controller';

        $actionName = $urlParts[1] ?? 'index';

        $params = array_slice($urlParts, 2);

        $controllerFile = ROOT_PATH . "/src/Controllers/" . $controllerName . ".php";

        if (file_exists($controllerFile)) {

            require_once $controllerFile;
            
            if (class_exists($controllerName)) {
                $controllerInstance = new $controllerName();

                if (method_exists($controllerInstance, $actionName)) {
                    call_user_func_array([$controllerInstance, $actionName], $params);
                    return; 
                } else {
                    error_log("Roteamento: Ação '{$actionName}' não encontrada em '{$controllerName}'. URL tentada: {$url}");
                }
            } else {
                error_log("Roteamento: Arquivo '{$controllerFile}' existe, mas a classe '{$controllerName}' não foi encontrada dentro dele.");
            }
        } else {
            error_log("Roteamento: Controller '{$controllerName}' (Arquivo: {$controllerFile}) não encontrado. URL tentada: {$url}");
        }

        $this->mostrarErro404();
    }

    private function mostrarErro404()
    {
        header("HTTP/1.0 404 Not Found");
        
        echo "<div style='font-family: sans-serif; text-align: center; margin-top: 10%; color: #334155;'>";
        echo "<h1>Página não encontrada (404)</h1>";
        echo "<p>Desculpe, a página que você está procurando não existe, foi removida ou você não tem permissão para acessá-la.</p>";
        echo "<a href='/' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #0f172a; color: white; text-decoration: none; border-radius: 6px;'>Voltar para a página inicial</a>";
        echo "</div>";
        exit;
    }
}