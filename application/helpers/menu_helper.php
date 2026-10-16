<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('active_link_controller'))
{
    function active_link_controller($controller)
    {
        $CI    =& get_instance();
        $class = $CI->router->fetch_class();

        return ($class == $controller) ? 'active' : NULL;
    }
}


if ( ! function_exists('active_link_function'))
{
    function active_link_function($controller)
    {
        $CI    =& get_instance();
        $class = $CI->router->fetch_method();

        return ($class == $controller) ? 'active' : NULL;
    }
}

if ( ! function_exists('construirMenuSidebar'))
{
    function construirMenuSidebar(array $menus, array &$menuFinal, $menuSuperiorId, $nivel = 0)
    {
        foreach ($menus as $menu) {
            if ($menu['pai_id'] == $menuSuperiorId) {
                $menuFinal[] = $menu;
            }
        }

        $nivel++;
     
        for ($i = 0; $i < count($menuFinal); $i++) {
            $menuFinal[$i]['sub_menus'] = [];
            $menuFinal[$i]['nivel'] = $nivel;
     
            construirMenuSidebar($menus, $menuFinal[$i]['sub_menus'], $menuFinal[$i]['id'], $nivel);
        }
    }
    
}

if ( ! function_exists('getHtmlSidebar'))
{
    function getHtmlSidebar($menu, $pai_id = null, $permissoes = null){
        $html = "";
        if(($menu["nivel"] == 1 && hasMenu($menu, $permissoes)) || @in_array($menu['metodo'], $permissoes[$menu['controlador']])) {
            $html = '<li><a href="';
            if(empty($menu['sub_menus'])){
                $html .= site_url($menu['controlador'].'/'.$menu['metodo']);
            } else {
                $html .= '#';
            }
            $html .= '" id="'.$menu['id'].'"';
            if($pai_id == $menu['id'])
                $html .= ' class="selectedMenuPai"';
            $html .= '>';
            if(!empty($menu['icon_class'])){
                $html .= $menu['icon_class'].' ';
            }
            $html .= '<span>' . $menu['nome'] . '</span>';
            if(!empty($menu['sub_menus'])){
                $html .= '<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>';
            }
            $html .= '</a>';
            if(!empty($menu['sub_menus'])){
                $html .= '<ul class="treeview-menu">';
                foreach($menu['sub_menus'] as $sub){
                    $html .= getHtmlSidebar($sub, $pai_id, $permissoes);
                }
                $html .= '</ul>';
            }
            $html .= '</li>';

        }
        return $html;
    }
}

if ( ! function_exists('hasMenu'))
{
    function hasMenu($menu, $permissoes, $submenu = false) {
        if($submenu == false) {
            if(array_key_exists($menu['controlador'], $permissoes)) {
                return true;
            } else {
                if(!empty($menu['sub_menus'])) {
                    if(hasMenu($menu['sub_menus'], $permissoes, true))
                        return true;
                } else {
                    return false;
                }
            }
        } else {
            foreach ($menu as $submenu) {
                if(array_key_exists($submenu['controlador'], $permissoes)) {
                    return true;
                }
            }
            return false;
        }
    }
}