<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

        /* COMMON :: ADMIN & PUBLIC */
        /* Load */
        $this->load->database();
        $this->load->config('common/dp_config');
        $this->load->config('common/dp_language');
        $this->load->library(array('form_validation', 'ion_auth', 'template', 'common/mobile_detect'));
        //$this->load->library('voxy');
        $this->load->helper(array('array', 'language', 'url'));
        $this->load->model('common/prefs_model');

        /* Data */
        $this->data['lang']           = element($this->config->item('language'), $this->config->item('language_abbr'));
        $this->data['charset']        = $this->config->item('charset');
        $this->data['frameworks_dir'] = $this->config->item('frameworks_dir');
        $this->data['plugins_dir']    = $this->config->item('plugins_dir');
        $this->data['avatar_dir']     = $this->config->item('avatar_dir');

        /* Any mobile device (phones or tablets) */
        if ($this->mobile_detect->isMobile())
        {
            $this->data['mobile'] = TRUE;

            if ($this->mobile_detect->isiOS()){
                $this->data['ios']     = TRUE;
                $this->data['android'] = FALSE;
            }
            else if ($this->mobile_detect->isAndroidOS())
            {
                $this->data['ios']     = FALSE;
                $this->data['android'] = TRUE;
            }
            else
            {
                $this->data['ios']     = FALSE;
                $this->data['android'] = FALSE;
            }

            if ($this->mobile_detect->getBrowsers('IE')){
                $this->data['mobile_ie'] = TRUE;
            }
            else
            {
                $this->data['mobile_ie'] = FALSE;
            }
        }
        else
        {
            $this->data['mobile']    = FALSE;
            $this->data['ios']       = FALSE;
            $this->data['android']   = FALSE;
            $this->data['mobile_ie'] = FALSE;
        }
	}
}


class Admin_Controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();

        if ( ! $this->ion_auth->logged_in())
        {   
            ob_clean();
            redirect('auth/login');
        }
        else
        {
            /* Load */
            $this->load->config('admin/dp_config');
            $this->load->library('admin/page_title');
            $this->load->library('admin/breadcrumbs');
            $this->load->model('core_model');
            $this->load->model('menus_model');
            $this->load->helper('menu');
            $this->load->helper('functions');
            $this->lang->load(array('admin/main_header', 'admin/main_sidebar', 'admin/footer', 'admin/actions'));

            /* Load library function  */
            $this->breadcrumbs->unshift(0, $this->lang->line('menu_dashboard'), 'admin/dashboard');

            $menuFinal = [];
            construirMenuSidebar($this->menus_model->get(array('select'=>'m.*', 'join'=>array(array('menus_empresas me','me.menus_id = m.id','join')), 'where'=>'m.ativo=1 AND me.empresas_id='.$this->session->userdata('empresa_id'), 'order_by'=>'m.tipo asc, m.order asc'))->result_array(), $menuFinal, null);

            $user_groups = $this->ion_auth->get_users_groups();
            $user_groups_array = array();
            foreach ($user_groups->result() as $group) {
                if(is_array(unserialize($group->permissoes)))
                    $user_groups_array = array_merge(unserialize($group->permissoes), $user_groups_array);
            }
            $html_menu = '<ul class="sidebar-menu">';
            $last_tipo = "";
            $teste = array();
              // echo "<pre>".print_r($menuFinal,true)."</pre>";
              // echo "<pre>".print_r($user_groups_array,true)."</pre>";
              // exit;
            foreach($menuFinal as $menu){
                // if(@hasMenu($menu, $user_groups_array)) {
                    $teste[] = $menu;

                    if($menu['tipo'] != $last_tipo && $menu['tipo'] == 0)
                        $html_menu .= '<li class="header text-uppercase">'.lang('menu_main_navigation').'</li>';
                    elseif($menu['tipo'] != $last_tipo && $menu['tipo'] == 1)
                        $html_menu .= '<li class="header text-uppercase">'.lang('menu_administration').'</li>';

                    $html_menu .= getHtmlSidebar($menu, null, $user_groups_array);
                    $last_tipo = $menu['tipo'];
                // }

            }
            $html_menu .= '</ul>';
                    // exit("<pre>".print_r($teste,true)."</pre>");

            //mensagens
            $messages = array();
            $messages_count = 0;
            $this->load->model('contatos_interno_model');
            $mensagens = $this->contatos_interno_model->get(null, "visualizada = 'N'");

            if($mensagens->num_rows() > 0) {
                foreach ($mensagens->result() as $msg) {
                    $diff = get_diff_dates($msg->created_at, date('Y-m-d H:i:s'));
                    $txt = "<li><a href='".base_url('contatos_interno/ver/'.$msg->id)."'>
                        <div class='pull-left'>
                            <i class='fa fa-commenting-o fa-lg'></i>
                        </div>
                        <h4>".$msg->first_name."<small><i class='fa fa-clock-o'></i>";
                    if($diff->y > 0)
                        $txt .= " ".$diff->y."a";
                    if($diff->m > 0)
                        $txt .= " ".$diff->m."m";
                    if($diff->d > 0)
                        $txt .= " ".$diff->d."d";
                    if($diff->h > 0)
                        $txt .= " ".$diff->h."h";
                    if($diff->i > 0)
                        $txt .= " ".$diff->i."m";
                    $txt .= "</small></h4>
                        <p>".$msg->assunto."</p>
                        </a></li>";
                    $messages[] = $txt;
                }
                $messages_count = $mensagens->num_rows();
            }
            $this->data["messages"] = $messages;
            $this->data["messages_count"] = $messages_count;

            //notificações
            $notifications_ead = array();
            $notifications_ead_count = 0;
            if(!$this->session->has_userdata('notifications_ead')) {
                $notifications_ead_count = 0;
                if ($this->ion_auth->has_permissao("ead_users", "index"))
                {
                    $this->load->model('ead_users_model');
                    $users_expirados = $this->ead_users_model->get_count(null, "eu.expiration_date < '".date('Y-m-d', strtotime("+30 days"))."' AND un.empresa_id = '".$this->session->userdata('empresa_id')."'", array(array("cad_orca c", "c.user_id = u.id"), array("unidades un", "un.id = c.filial")));
                    if($users_expirados->num_rows() > 0) {
                        $notifications_ead[] = "<li><a href='".base_url('ead_users')."' alt='Há ".$users_expirados->num_rows()." alunos EAD expirando' title='Há ".$users_expirados->num_rows()." alunos EAD expirando'><i class='fa fa-users text-aqua'></i> Há <b>".$users_expirados->num_rows()."</b> alunos EAD expirando</a></li>";
                        $notifications_ead_count++;
                        $this->session->set_userdata('notifications_ead', $notifications_ead);
                        $this->session->set_userdata('notifications_ead_count', $notifications_ead_count);
                    }
                }
            } else {
                $notifications_ead = $this->session->userdata('notifications_ead');
                $notifications_ead_count = $this->session->userdata('notifications_ead_count');
            }

            $notifications_iugu = array();
            $notifications_iugu_count = 0;
            if(!$this->session->has_userdata('notifications_iugu')) {
                $notifications_iugu_count = 0;
                if ($this->ion_auth->has_permissao("iugu", "cartao_recorrencia"))
                {
                    $this->load->model('iugu_model');
                    $users_pendent = $this->iugu_model->get_iugu_users_pendent();
                    if($users_pendent->num_rows() > 0) {
                        foreach ($users_pendent->result() as $value) {
                            $notifications_iugu[] = "<li><a href='".base_url('iugu/cartao_recorrencia/'.$value->cad_orca_id.'/'.$value->user_id)."' alt='Adicionar cartão recorrente contrato ".$value->cad_orca_id."' title='Adicionar cartão recorrente contrato ".$value->cad_orca_id."'><i class='fa fa-credit-card text-aqua'></i> Adicionar cartão recorrente contrato <b>".$value->cad_orca_id."</b></a></li>";
                            $notifications_iugu_count++;
                        }
                        $this->session->set_userdata('notifications_iugu', $notifications_iugu);
                        $this->session->set_userdata('notifications_iugu_count', $notifications_iugu_count);
                    }
                }
            } else {
                $notifications_iugu = $this->session->userdata('notifications_iugu');
                $notifications_iugu_count = $this->session->userdata('notifications_iugu_count');
            }

            $this->data["notifications"] = array_merge($notifications_ead, $notifications_iugu);
            $this->data["notifications_count"] = $notifications_ead_count + $notifications_iugu_count;

            /* Data */
            $this->data['session']     = $this->session;
            $this->data['title']       = $this->config->item('title');
            $this->data['title_lg']    = $this->config->item('title_lg');
            $this->data['title_mini']  = $this->config->item('title_mini');
            $this->data['admin_prefs'] = $this->prefs_model->admin_prefs();
            $this->data['menu_sidebar'] = $html_menu;
            if($this->data['admin_prefs']['empresa_sidebar'] == true) {
                $this->load->model('empresas_model');
                $this->data['empresas_sidebar'] = $this->empresas_model->get(array('select'=>'empresas.*', 'join'=>array(array('empresas_users','empresas_users.empresa_id=empresas.id')), 'where'=>'empresas_users.user_id = '.$this->session->userdata('user_id'), 'order_by'=>array('empresas.id','des')))->result();
                //$this->data['empresas_sidebar'] = $this->ion_auth->get_empresas_user_to_sidebar($this->session->userdata('user_id'))->result();
            }
            $this->data['user_login']  = $this->prefs_model->user_info_login($this->ion_auth->user()->row()->id);

            if ($this->router->fetch_class() == 'dashboard')
            {
                $this->data['dashboard_alert_file_install'] = $this->core_model->get_file_install();
                $this->data['header_alert_file_install']    = NULL;
            }
            else
            {
                $this->data['dashboard_alert_file_install'] = NULL;
                $this->data['header_alert_file_install']    = NULL; /* << A MODIFIER !!! */
            }
        }
    }
}