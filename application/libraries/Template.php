<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Template {

    protected $CI;
    public $template = array();

    public function __construct()
    {	
		$this->CI =& get_instance();
    }


    public function admin_render($content, $data = NULL)
    {
        if ( ! $content)
        {
            return NULL;
        }
        else
        {
            $this->template['header']          = $this->CI->load->view('admin/_templates/header', $data, TRUE);
            $this->template['main_header']     = $this->CI->load->view('admin/_templates/main_header', $data, TRUE);
            $this->template['main_sidebar']    = $this->CI->load->view('admin/_templates/main_sidebar', $data, TRUE);
            $this->template['content']         = $this->CI->load->view($content, $data, TRUE);
            $this->template['control_sidebar'] = $this->CI->load->view('admin/_templates/control_sidebar', $data, TRUE);
            $this->template['footer']          = $this->CI->load->view('admin/_templates/footer', $data, TRUE);
            
            return $this->CI->load->view('admin/_templates/template', $this->template);
        }
	}

    public function admin_multiple_render($content = array(), $data = NULL)
    {
        $this->template['header']          = $this->CI->load->view('admin/_templates/header', $data, TRUE);
        $this->template['main_header']     = $this->CI->load->view('admin/_templates/main_header', $data, TRUE);
        $this->template['main_sidebar']    = $this->CI->load->view('admin/_templates/main_sidebar', $data, TRUE);

        $this->template['content'] = '<div class="content-wrapper"><section class="content">';
        if(isset($data['message']))
            $this->template['content'] .= $data['message'];
        $this->template['content'] .= '<div class="row">';
        foreach ($content as $page) {
            $this->template['content'] .= $this->CI->load->view($page[0], $page[1], TRUE);
        }
        $this->template['content'] .= '</div></section></div>';

        $this->template['control_sidebar'] = $this->CI->load->view('admin/_templates/control_sidebar', $data, TRUE);
        $this->template['footer']          = $this->CI->load->view('admin/_templates/footer', $data, TRUE);
        
        return $this->CI->load->view('admin/_templates/template', $this->template);
    }

    public function auth_render($content, $data = NULL)
    {
        if ( ! $content)
        {
            return NULL;
        }
        else
        {
            $this->template['header']  = $this->CI->load->view('auth/_templates/header', $data, TRUE);
            $this->template['content'] = $this->CI->load->view($content, $data, TRUE);
            $this->template['footer']  = $this->CI->load->view('auth/_templates/footer', $data, TRUE);

            return $this->CI->load->view('auth/_templates/template', $this->template);
        }
	}

}