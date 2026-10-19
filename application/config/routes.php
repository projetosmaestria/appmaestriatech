<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['admin'] = 'admin/dashboard';
$route['admin/prefs/interfaces/(:any)'] = 'admin/prefs/interfaces/$1';

/* Modulo de Treinamentos - Academy */
$route['treinamentos']                          = 'treinamentos/index';
$route['treinamentos/modulo/(:num)']            = 'treinamentos/modulo/$1';
$route['treinamentos/ver/(:num)']               = 'treinamentos/ver/$1';
$route['treinamentos/detalhes/(:num)']          = 'treinamentos/detalhes/$1';
$route['treinamentos/etapa/(:num)/(:num)']      = 'treinamentos/etapa/$1/$2';
$route['treinamentos/etapa/(:num)']             = 'treinamentos/etapa/$1';
$route['treinamentos/meu_progresso']            = 'treinamentos/meu_progresso';
$route['treinamentos/salvar_progresso_video']   = 'treinamentos/salvar_progresso_video';
$route['treinamentos/toggle_atividade']         = 'treinamentos/toggle_atividade';
$route['treinamentos/concluir_etapa']           = 'treinamentos/concluir_etapa';
$route['treinamentos/avaliar']                  = 'treinamentos/avaliar';
$route['treinamentos/download_arquivo/(:num)']  = 'treinamentos/download_arquivo/$1';

$route['treinamentos_admin']                    = 'treinamentos_admin/index';
$route['treinamentos_admin/etapas/(:num)']      = 'treinamentos_admin/etapas/$1';
$route['treinamentos_admin/etapa_create/(:num)']= 'treinamentos_admin/etapa_create/$1';
$route['treinamentos_admin/etapa_edit/(:num)']  = 'treinamentos_admin/etapa_edit/$1';
$route['treinamentos_admin/etapa_delete/(:num)']= 'treinamentos_admin/etapa_delete/$1';
$route['treinamentos_admin/permissoes/(:num)']  = 'treinamentos_admin/permissoes/$1';
$route['treinamentos_admin/edit/(:num)']        = 'treinamentos_admin/edit/$1';
$route['treinamentos_admin/delete/(:num)']      = 'treinamentos_admin/delete/$1';
$route['treinamentos_admin/banners']            = 'treinamentos_admin/banners';
$route['treinamentos_admin/banner_create']      = 'treinamentos_admin/banner_create';
$route['treinamentos_admin/banner_edit/(:num)'] = 'treinamentos_admin/banner_edit/$1';
$route['treinamentos_admin/banner_delete/(:num)']= 'treinamentos_admin/banner_delete/$1';
$route['treinamentos_admin/relatorios']         = 'treinamentos_admin/relatorios';

