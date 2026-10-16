<?php defined('BASEPATH') OR exit('No direct script access allowed');


require_once APPPATH.'third_party/tcpdf/tcpdf.php';

class Mytcpdf extends TCPDF
{
    function __construct()
    {
        parent::__construct();
    }
}