<?php

class EtiquetaController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }
    }

    public function index()
    {
        require_once VIEW_PATH . '/etiquetas/index.php';
    }

    public function pasta()
    {
        require_once VIEW_PATH . '/etiquetas/modelo_pasta_aluno.php';
    }

    public function caixa()
    {
        require_once VIEW_PATH . '/etiquetas/modelo_caixa_passivo.php';
    }
}
