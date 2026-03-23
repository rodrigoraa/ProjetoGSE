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

            $controller = new $controllerName();

            if (method_exists($controller, $actionName)) {

                call_user_func_array([$controller, $actionName], $params);
            } else {

                echo "<h1>Erro 404</h1><p>Ação '$actionName' não encontrada.</p>";
            }
        } else {

            echo "<h1>Erro 404</h1><p>Controller '$controllerName' não encontrado.</p>";
        }
    }
}
