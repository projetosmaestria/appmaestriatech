<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manuais extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('admin/manuais');
    }

    public function book1()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "book1"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('book1'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_presencial'), 'manuais_book1');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_presencial_book1'), 'manuais_book1');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/presencial/book1', $this->data);
        }
    }

    public function book2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "book2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('book2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_presencial'), 'manuais_book2');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_presencial_book2'), 'manuais_book2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/presencial/book2', $this->data);
        }
    }

    public function book3()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "book3"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('book3'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_presencial'), 'manuais_book3');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_presencial_book3'), 'manuais_book3');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/presencial/book3', $this->data);
        }
    }

    public function book4()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "book4"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('book4'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_presencial'), 'manuais_book4');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_presencial_book4'), 'manuais_book4');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/presencial/book4', $this->data);
        }
    }

    public function teens_lexical()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_lexical"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('lexical'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_lexical');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_teachers'), 'manuais_teens_lexical');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_teachers_lexical'), 'manuais_teens_lexical');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/lexical', $this->data);
        }
    }

    public function teens_anexo()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_anexo"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('anexo'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_anexo');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_teachers'), 'manuais_teens_anexo');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_teachers_anexo'), 'manuais_teens_anexo');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/anexo', $this->data);
        }
    }

    public function teens_review1()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_review1"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('review1'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_review1');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_apoio'), 'manuais_teens_review1');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_apoio_book1'), 'manuais_teens_review1');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_apoio_book1_review1'), 'manuais_teens_review1');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/review1', $this->data);
        }
    }

    public function teens_review2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_review2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('review2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_review2');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_apoio'), 'manuais_teens_review2');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_apoio_book1'), 'manuais_teens_review2');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_apoio_book1_review2'), 'manuais_teens_review2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/review2', $this->data);
        }
    }

    public function teens_t1a()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_t1a"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('t1a'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_t1a');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_apoio'), 'manuais_teens_t1a');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_apoio_book1'), 'manuais_teens_t1a');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_apoio_book1_t1a'), 'manuais_teens_t1a');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/t1a', $this->data);
        }
    }

    public function teens_t1b()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_t1b"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('t1b'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_t1b');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_apoio'), 'manuais_teens_t1b');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_apoio_book1'), 'manuais_teens_t1b');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_apoio_book1_t1b'), 'manuais_teens_t1b');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/t1b', $this->data);
        }
    }

    public function teens_t2a()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_t2a"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('t2a'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_t2a');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_apoio'), 'manuais_teens_t2a');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_apoio_book1'), 'manuais_teens_t2a');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_apoio_book1_t2a'), 'manuais_teens_t2a');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/t2a', $this->data);
        }
    }

    public function teens_t2b()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_t2b"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('t2b'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_t2b');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_apoio'), 'manuais_teens_t2b');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_apoio_book1'), 'manuais_teens_t2b');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_apoio_book1_t2b'), 'manuais_teens_t2b');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/t2b', $this->data);
        }
    }

    public function teens_aula7()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_aula7"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('aula7'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_aula7');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_apoio'), 'manuais_teens_aula7');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_apoio_book1'), 'manuais_teens_aula7');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_apoio_book1_aula7'), 'manuais_teens_aula7');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/aula7', $this->data);
        }
    }

    public function teens_aula9()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_aula9"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('aula9'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_aula9');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_apoio'), 'manuais_teens_aula9');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_apoio_book1'), 'manuais_teens_aula9');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_apoio_book1_aula9'), 'manuais_teens_aula9');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/aula9', $this->data);
        }
    }

    public function teens_aula17()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_aula17"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('aula17'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_aula17');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_apoio'), 'manuais_teens_aula17');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_apoio_book1'), 'manuais_teens_aula17');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_apoio_book1_aula17'), 'manuais_teens_aula17');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/aula17', $this->data);
        }
    }

    public function teens_aula2()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_aula2"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('aula2'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_aula2');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_apoio'), 'manuais_teens_aula2');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_apoio_book1'), 'manuais_teens_aula2');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_apoio_book1_aula2'), 'manuais_teens_aula2');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/aula2', $this->data);
        }
    }

    public function teens_aula13()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_aula13"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push(lang('aula13'));
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais_teens_aula13');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_apoio'), 'manuais_teens_aula13');
            $this->breadcrumbs->unshift(4, lang('menu_manuais_teens_apoio_book1'), 'manuais_teens_aula13');
            $this->breadcrumbs->unshift(5, lang('menu_manuais_teens_apoio_book1_aula13'), 'manuais_teens_aula13');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/aula13', $this->data);
        }
    }
	public function teens_tg_book5()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_tg_book5"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push("Book 5");
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais/teens_tg_book5');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_teachers'), 'manuais/teens_tg_book5');
            $this->breadcrumbs->unshift(4, 'Book 5', 'manuais/teens_tg_book5');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/book5', $this->data);
        }
    }
	public function teens_tg_book3()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_tg_book3"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push("Book 3");
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais/teens_tg_book3');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_teachers'), 'manuais/teens_tg_book3');
            $this->breadcrumbs->unshift(4, 'Book 3', 'manuais/teens_tg_book3');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/book3', $this->data);
        }
    }
	public function teens_tg_book4()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_tg_book4"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push("Book 4 Anexo");
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais/teens_tg_book4');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_teachers'), 'manuais/teens_tg_book4');
            $this->breadcrumbs->unshift(4, 'Book 4 Anexo', 'manuais/teens_tg_book4');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/book4', $this->data);
        }
    }
	public function teens_tg_book4online()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "teens_tg_book4online"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push("Book 4 (Online)");
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_teens'), 'manuais/teens_tg_book4online');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_teens_teachers'), 'manuais/teens_tg_book4online');
            $this->breadcrumbs->unshift(4, 'Book 4 (Online)', 'manuais/teens_tg_book4online');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/teens/book4online', $this->data);
        }
    }

    public function presencial_first_book()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "presencial_first_book"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push("Presencial First Book");
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_presencial'), 'presencial_first_book');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_presencial_first_book'), 'presencial_first_book');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/presencial/first_book', $this->data);
        }
    }

    public function remote_first_book()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->has_permissao("manuais", "remote_first_book"))
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Title Page :: Common */
            $this->page_title->push("Remote First Book");
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs :: Common */
            $this->breadcrumbs->unshift(1, lang('menu_manuais'), 'manuais');
            $this->breadcrumbs->unshift(2, lang('menu_manuais_remote'), 'remote_first_book');
            $this->breadcrumbs->unshift(3, lang('menu_manuais_remote_first_book'), 'remote_first_book');

            /* Breadcrumbs */
            $this->data['breadcrumb'] = $this->breadcrumbs->show();
            
            /* Load Template */
            $this->template->admin_render('admin/manuais/remote/first_book', $this->data);
        }
    }
}
