<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Api para roteamento interno CodeIgniter
 */
class Api extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        require FCPATH . 'api/index.php';
    }

    public function verificar_habilitacao()
    {
        require FCPATH . 'api/index.php';
    }
}
