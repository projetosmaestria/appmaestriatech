<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Videos extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/videos');
    }

    public function apresentacao()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "apresentacao"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('apresentacao'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_aspectos_gerenciais'), 'videos_aspectos_gerenciais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/apresentacao', $this->data);
        }
    }

    public function lancamento_cuidador()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "lancamento_cuidador"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('lancamento'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_aspectos_gerenciais'), 'videos_aspectos_gerenciais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/lancamento_cuidador', $this->data);
        }
    }

    public function portifolio_publico_alvo()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "portifolio_publico_alvo"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('portifolio_publico_alvo'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_dicas_comerciais'), 'videos_curso_dicas_comerciais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/portifolio_publico_alvo', $this->data);
        }
    }

    public function objetivos_oportunidades()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "objetivos_oportunidades"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('objetivos_oportunidades'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_dicas_comerciais'), 'videos_curso_dicas_comerciais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/objetivos_oportunidades', $this->data);
        }
    }

    public function estrutura_curso()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "estrutura_curso"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('estrutura_curso'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_dicas_comerciais'), 'videos_curso_dicas_comerciais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/estrutura_curso', $this->data);
        }
    }

    public function grandes_diferenciais()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "grandes_diferenciais"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('grandes_diferenciais'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_dicas_comerciais'), 'videos_curso_dicas_comerciais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/grandes_diferenciais', $this->data);
        }
    }

    public function material_valor_agregado()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "material_valor_agregado"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('material_valor_agregado'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_dicas_comerciais'), 'videos_curso_dicas_comerciais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/material_valor_agregado', $this->data);
        }
    }

    public function sintetizando()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "sintetizando"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('sintetizando'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_dicas_comerciais'), 'videos_curso_dicas_comerciais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/sintetizando', $this->data);
        }
    }

    public function estrategias_comerciais()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "estrategias_comerciais"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('estrategias_comerciais'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_dicas_comerciais'), 'videos_curso_dicas_comerciais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/estrategias_comerciais', $this->data);
        }
    }

    public function diferenciais_pedagogicos()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "diferenciais_pedagogicos"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('diferenciais_pedagogicos'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_dicas_implantar_diferenciais_pedagogicos'), 'videos_dicas_implantar_diferenciais_pedagogicos');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/diferenciais_pedagogicos', $this->data);
        }
    }

    public function dicas_implantacao_curso()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "dicas_implantacao_curso"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('dicas_implantacao_curso'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_dicas_implantar_diferenciais_pedagogicos'), 'videos_dicas_implantar_diferenciais_pedagogicos');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/dicas_implantacao_curso', $this->data);
        }
    }

    public function dicas_implantacao()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "dicas_implantacao"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('dicas_implantacao'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_dicas_implantar_diferenciais_pedagogicos'), 'videos_dicas_implantar_diferenciais_pedagogicos');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/dicas_implantacao', $this->data);
        }
    }

    public function estrutura_geral()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "estrutura_geral"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('estrutura_geral'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_dicas_implantar_diferenciais_pedagogicos'), 'videos_dicas_implantar_diferenciais_pedagogicos');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/estrutura_geral', $this->data);
        }
    }

    public function indicadores_performance()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "indicadores_performance"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('indicadores_performance'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_cuidador'), 'videos_cuidador');
            $this->breadcrumbs->unshift(2, lang('menu_videos_dicas_implantar_diferenciais_pedagogicos'), 'videos_dicas_implantar_diferenciais_pedagogicos');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/cuidador/indicadores_performance', $this->data);
        }
    }

    public function ferias()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "ferias"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('ferias'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_gestao'), 'videos_gestao');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/gestao/ferias', $this->data);
        }
    }

    public function comunicacao()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "comunicacao"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('comunicacao'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_gestao'), 'videos_gestao');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/gestao/comunicacao', $this->data);
        }
    }

    public function indicadores_agosto_setembro()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "indicadores_agosto_setembro"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('indicadores_agosto_setembro'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_gestao'), 'videos_gestao');
            $this->breadcrumbs->unshift(2, lang('menu_videos_gestao_indicadores'), 'videos_gestao_indicadores');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/gestao/indicadores/agosto_setembro', $this->data);
        }
    }

    public function indicadores_outubro()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "indicadores_outubro"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('indicadores_outubro'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_gestao'), 'videos_gestao');
            $this->breadcrumbs->unshift(2, lang('menu_videos_gestao_indicadores'), 'videos_gestao_indicadores');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/gestao/indicadores/outubro', $this->data);
        }
    }

    public function estrategias_oportunidades_1()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "estrategias_oportunidades_1"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('estrategias_oportunidades_1'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_estrategias_oportunidades_1'), 'videos_curso_teens_estrategias_oportunidades_1');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/estrategias_oportunidades/estrategias_oportunidades_1', $this->data);
        }
    }

    public function estrategias_oportunidades_2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "estrategias_oportunidades_2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('estrategias_oportunidades_2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_estrategias_oportunidades_2'), 'videos_curso_teens_estrategias_oportunidades_2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/estrategias_oportunidades/estrategias_oportunidades_2', $this->data);
        }
    }

    public function estrategias_oportunidades_3()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "estrategias_oportunidades_3"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('estrategias_oportunidades_3'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_estrategias_oportunidades_3'), 'videos_curso_teens_estrategias_oportunidades_3');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/estrategias_oportunidades/estrategias_oportunidades_3', $this->data);
        }
    }

    public function estrategias_oportunidades_4()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "estrategias_oportunidades_4"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('estrategias_oportunidades_4'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_estrategias_oportunidades_4'), 'videos_curso_teens_estrategias_oportunidades_4');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/estrategias_oportunidades/estrategias_oportunidades_4', $this->data);
        }
    }

    public function estrategias_comerciais_1()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "estrategias_comerciais_1"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('estrategias_comerciais_1'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_estrategias_comerciais_1'), 'videos_curso_teens_estrategias_comerciais_1');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/estrategias_comerciais/estrategias_comerciais_1', $this->data);
        }
    }

    public function estrategias_comerciais_2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "estrategias_comerciais_2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('estrategias_comerciais_2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_estrategias_comerciais_2'), 'videos_curso_teens_estrategias_comerciais_2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/estrategias_comerciais/estrategias_comerciais_2', $this->data);
        }
    }

    public function estrategias_comerciais_3()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "estrategias_comerciais_3"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('estrategias_comerciais_3'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_estrategias_comerciais_3'), 'videos_curso_teens_estrategias_comerciais_3');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/estrategias_comerciais/estrategias_comerciais_3', $this->data);
        }
    }

    public function estrategias_comerciais_4()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "estrategias_comerciais_4"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('estrategias_comerciais_4'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_estrategias_comerciais_4'), 'videos_curso_teens_estrategias_comerciais_4');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/estrategias_comerciais/estrategias_comerciais_4', $this->data);
        }
    }

    public function metodologia_diferenciais_1()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "metodologia_diferenciais_1"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('metodologia_diferenciais_1'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_metodologia_diferenciais_1'), 'videos_curso_teens_metodologia_diferenciais_1');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/metodologia_diferenciais/metodologia_diferenciais_1', $this->data);
        }
    }

    public function metodologia_diferenciais_2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "metodologia_diferenciais_2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('metodologia_diferenciais_2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_metodologia_diferenciais_2'), 'videos_curso_teens_metodologia_diferenciais_2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/metodologia_diferenciais/metodologia_diferenciais_2', $this->data);
        }
    }

    public function metodologia_diferenciais_3()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "metodologia_diferenciais_3"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('metodologia_diferenciais_3'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_metodologia_diferenciais_3'), 'videos_curso_teens_metodologia_diferenciais_3');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/metodologia_diferenciais/metodologia_diferenciais_3', $this->data);
        }
    }

    public function videos_comerciais_1()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "videos_comerciais_1"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('videos_comerciais_1'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_videos_comerciais_1'), 'videos_curso_teens_videos_comerciais_1');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/videos_comerciais/videos_comerciais_1', $this->data);
        }
    }

    public function videos_comerciais_2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "videos_comerciais_2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('videos_comerciais_2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_videos_comerciais_2'), 'videos_curso_teens_videos_comerciais_2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/videos_comerciais/videos_comerciais_2', $this->data);
        }
    }

    public function videos_comerciais_3()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "videos_comerciais_3"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('videos_comerciais_3'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_videos_comerciais_3'), 'videos_curso_teens_videos_comerciais_3');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/videos_comerciais/videos_comerciais_3', $this->data);
        }
    }

    public function videos_comerciais_4()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "videos_comerciais_4"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('videos_comerciais_4'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_videos_comerciais_4'), 'videos_curso_teens_videos_comerciais_4');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/videos_comerciais/videos_comerciais_4', $this->data);
        }
    }

    public function videos_comerciais_5()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "videos_comerciais_5"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('videos_comerciais_5'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_videos_comerciais_5'), 'videos_curso_teens_videos_comerciais_5');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/videos_comerciais/videos_comerciais_5', $this->data);
        }
    }

    public function videos_comerciais_6()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "videos_comerciais_6"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('videos_comerciais_6'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_videos_comerciais_6'), 'videos_curso_teens_videos_comerciais_6');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/videos_comerciais/videos_comerciais_6', $this->data);
        }
    }

    public function etapas_ligacao()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "etapas_ligacao"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('etapas_ligacao'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_etapas_ligacao'), 'videos_curso_teens_etapas_ligacao');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/etapas_ligacao', $this->data);
        }
    }

    public function formulario_indicacao()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "formulario_indicacao"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('formulario_indicacao'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_formulario_indicacao'), 'videos_curso_teens_formulario_indicacao');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/formulario_indicacao', $this->data);
        }
    }

    public function atendimento_presencial()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "atendimento_presencial"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('atendimento_presencial'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_atendimento_presencial'), 'videos_curso_teens_atendimento_presencial');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/atendimento_presencial', $this->data);
        }
    }

    public function script_agendamento_indicacao_1()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "script_agendamento_indicacao_1"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('script_agendamento_indicacao_1'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_script_agendamento_indicacao_1'), 'videos_curso_teens_script_agendamento_indicacao_1');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/script_agendamento_indicacao_1', $this->data);
        }
    }

    public function script_agendamento_indicacao_2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "script_agendamento_indicacao_2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('script_agendamento_indicacao_2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_script_agendamento_indicacao_2'), 'videos_curso_teens_script_agendamento_indicacao_2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/script_agendamento_indicacao_2', $this->data);
        }
    }

    public function script_agendamento_indicacao_pais()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "script_agendamento_indicacao_pais"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('script_agendamento_indicacao_pais'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_script_agendamento_indicacao_pais'), 'videos_curso_teens_script_agendamento_indicacao_pais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/script_agendamento_indicacao_pais', $this->data);
        }
    }

    public function script_agendamento_colegios()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "script_agendamento_colegios"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('script_agendamento_colegios'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_script_agendamento_colegios'), 'videos_curso_teens_script_agendamento_colegios');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/script_agendamento_colegios', $this->data);
        }
    }

    public function script_agendamento_colegios_pais()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "script_agendamento_colegios_pais"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('script_agendamento_colegios_pais'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_script_agendamento_colegios_pais'), 'videos_curso_teens_script_agendamento_colegios_pais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/script_agendamento_colegios_pais', $this->data);
        }
    }

    public function ficha_atendimento_1_8()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "ficha_atendimento_1_8"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('ficha_atendimento_1_8'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_english'), 'videos_english');
            $this->breadcrumbs->unshift(2, lang('menu_videos_english_ficha_atendimento_1_8'), 'videos_english_ficha_atendimento_1_8');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/english/ficha_atendimento_1_8', $this->data);
        }
    }

    public function ficha_atendimento_2_4()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "ficha_atendimento_2_4"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('ficha_atendimento_2_4'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_english'), 'videos_english');
            $this->breadcrumbs->unshift(2, lang('menu_videos_english_ficha_atendimento_2_4'), 'videos_english_ficha_atendimento_2_4');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/english/ficha_atendimento_2_4', $this->data);
        }
    }

    public function dados()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "dados"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('dados'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_english'), 'videos_english');
            $this->breadcrumbs->unshift(2, lang('menu_videos_english_dados'), 'videos_english_dados');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/english/dados', $this->data);
        }
    }

    public function equipe_comercial_1()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "equipe_comercial_1"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('equipe_comercial_1'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_equipe_comercial_1'), 'videos_curso_teens_equipe_comercial_1');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/logistica/equipe_comercial_1', $this->data);
        }
    }

    public function equipe_comercial_2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "equipe_comercial_2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('equipe_comercial_2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_equipe_comercial_2'), 'videos_curso_teens_equipe_comercial_2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/logistica/equipe_comercial_2', $this->data);
        }
    }

    public function equipe_comercial_3()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "equipe_comercial_3"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('equipe_comercial_3'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_equipe_comercial_3'), 'videos_curso_teens_equipe_comercial_3');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/logistica/equipe_comercial_3', $this->data);
        }
    }

    public function gestores_1()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "gestores_1"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('gestores_1'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_gestores_1'), 'videos_curso_teens_gestores_1');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/logistica/gestores_1', $this->data);
        }
    }

    public function gestores_2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "gestores_2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('gestores_2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_gestores_2'), 'videos_curso_teens_gestores_2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/logistica/gestores_2', $this->data);
        }
    }

    public function gestores_3()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "gestores_3"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('gestores_3'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_gestores_3'), 'videos_curso_teens_gestores_3');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/logistica/gestores_3', $this->data);
        }
    }

    public function abertura()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "abertura"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('abertura'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_endireitando'), 'videos_endireitando');
            $this->breadcrumbs->unshift(2, lang('menu_videos_endireitando_abertura'), 'videos_endireitando_abertura');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/endireitando/abertura', $this->data);
        }
    }

    public function negativacao_1()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "negativacao_1"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('negativacao_1'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_endireitando'), 'videos_endireitando');
            $this->breadcrumbs->unshift(2, lang('menu_videos_endireitando_negativacao_1'), 'videos_endireitando_negativacao_1');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/endireitando/negativacao_1', $this->data);
        }
    }

    public function negativacao_2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "negativacao_2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('negativacao_2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_endireitando'), 'videos_endireitando');
            $this->breadcrumbs->unshift(2, lang('menu_videos_endireitando_negativacao_2'), 'videos_endireitando_negativacao_2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/endireitando/negativacao_2', $this->data);
        }
    }

    public function negativacao_3()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "negativacao_3"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('negativacao_3'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_endireitando'), 'videos_endireitando');
            $this->breadcrumbs->unshift(2, lang('menu_videos_endireitando_negativacao_3'), 'videos_endireitando_negativacao_3');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/endireitando/negativacao_3', $this->data);
        }
    }

    public function argumentos_comerciais()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "argumentos_comerciais"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('argumentos_comerciais'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_argumentos_comerciais'), 'videos_curso_teens_argumentos_comerciais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/argumentos_comerciais', $this->data);
        }
    }

    public function calendario_vendas()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "calendario_vendas"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('calendario_vendas'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_calendario_vendas'), 'videos_curso_teens_calendario_vendas');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/calendario_vendas', $this->data);
        }
    }

    public function cursos_idade()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "cursos_idade"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('cursos_idade'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_cursos_idade'), 'videos_curso_teens_cursos_idade');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/cursos_idade', $this->data);
        }
    }

    public function numero_vendas()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "numero_vendas"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('numero_vendas'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_numero_vendas'), 'videos_curso_teens_numero_vendas');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/numero_vendas', $this->data);
        }
    }

    public function regua_atendimento()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "regua_atendimento"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('regua_atendimento'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_regua_atendimento'), 'videos_curso_teens_regua_atendimento');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/regua_atendimento', $this->data);
        }
    }

    public function script_ligacao_redes_sociais()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "script_ligacao_redes_sociais"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('script_ligacao_redes_sociais'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_script_ligacao_redes_sociais'), 'videos_curso_teens_script_ligacao_redes_sociais');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/script_ligacao_redes_sociais', $this->data);
        }
    }

    public function script_ligacao_indicacao()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "script_ligacao_indicacao"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('script_ligacao_indicacao'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_script_ligacao_indicacao'), 'videos_curso_teens_script_ligacao_indicacao');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/script_ligacao_indicacao', $this->data);
        }
    }

    public function script_ligacao_colegios()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "script_ligacao_colegios"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('script_ligacao_colegios'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_script_ligacao_colegios'), 'videos_curso_teens_script_ligacao_colegios');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/script_ligacao_colegios', $this->data);
        }
    }

    public function script_ligacao_pesquisa()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "script_ligacao_pesquisa"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('script_ligacao_pesquisa'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_script_ligacao_pesquisa'), 'videos_curso_teens_script_ligacao_pesquisa');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/script_ligacao_pesquisa', $this->data);
        }
    }

    public function tabela_politica()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "tabela_politica"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('tabela_politica'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_tabela_politica'), 'videos_curso_teens_tabela_politica');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/tabela_politica', $this->data);
        }
    }

    public function tmk_1()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "tmk_1"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('tmk_1'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_tmk_1'), 'videos_curso_teens_tmk_1');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/tmk_1', $this->data);
        }
    }

    public function tmk_2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "tmk_2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('tmk_2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_curso_teens'), 'videos_curso_teens');
            $this->breadcrumbs->unshift(2, lang('menu_videos_curso_teens_tmk_2'), 'videos_curso_teens_tmk_2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/curso_teens/extras/tmk_2', $this->data);
        }
    }

    public function etapa_atendimento()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "etapa_atendimento"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('etapa_atendimento'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_etapa_atendimento'), 'videos_etapa_atendimento');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/etapa_atendimento', $this->data);
        }
    }

    public function teacher_guide()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "teacher_guide"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('teacher_guide'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_english'), 'videos_english');
            $this->breadcrumbs->unshift(2, lang('menu_videos_english_teacher_guide'), 'videos_english_teacher_guide');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/english/teacher_guide', $this->data);
        }
    }

    public function teacher_guide2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("videos", "teacher_guide2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('teacher_guide2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_videos_english_teacher_guide2'), 'videos_english_teacher_guide2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/videos/english/teacher_guide2', $this->data);
        }
    }
}
