<?php
class Controller {
    public function view($viewName, $data = []) {
        extract($data);

        $viewFile = VIEW_PATH . '/' . $viewName . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo "Erro: View '$viewName' não encontrada.";
        }
    }
}