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
        $CI =& get_instance();
        $is_admin = (isset($CI->ion_auth) && method_exists($CI->ion_auth, 'is_admin')) ? $CI->ion_auth->is_admin() : false;

        $has_access = false;
        if ($is_admin) {
            $has_access = true;
        } else {
            if (isset($menu["nivel"]) && $menu["nivel"] == 1) {
                $has_access = hasMenu($menu, $permissoes);
            } else {
                $ctrl = isset($menu['controlador']) ? $menu['controlador'] : '';
                $ctrl_lower = strtolower($ctrl);
                $metodo = isset($menu['metodo']) ? $menu['metodo'] : '';

                $perm_controlador = null;
                if (is_array($permissoes)) {
                    if (isset($permissoes[$ctrl]) && is_array($permissoes[$ctrl])) {
                        $perm_controlador = $permissoes[$ctrl];
                    } elseif (isset($permissoes[$ctrl_lower]) && is_array($permissoes[$ctrl_lower])) {
                        $perm_controlador = $permissoes[$ctrl_lower];
                    }
                }

                if ($perm_controlador !== null && !empty($metodo) && in_array($metodo, $perm_controlador)) {
                    $has_access = true;
                } elseif (!empty($menu['sub_menus'])) {
                    $has_access = hasMenu($menu['sub_menus'], $permissoes, true);
                }
            }
        }

        $html = "";
        if ($has_access) {
            $tem_submenus = !empty($menu['sub_menus']) && is_array($menu['sub_menus']);
            $html_sub = '';

            if ($tem_submenus) {
                foreach ($menu['sub_menus'] as $sub) {
                    $html_sub .= getHtmlSidebar($sub, $pai_id, $permissoes);
                }

                // Se for menu pai (nivel 1) e nenhum submenu gerou HTML e o pai não tiver rota direta própria, oculta o container
                if (isset($menu["nivel"]) && $menu["nivel"] == 1 && empty($html_sub) && (empty($menu['controlador']) || $menu['controlador'] == '#' || empty($menu['metodo']) || $menu['metodo'] == '#')) {
                    return '';
                }
            }

            $html = '<li><a href="';
            if (!$tem_submenus) {
                $html .= site_url($menu['controlador'] . '/' . $menu['metodo']);
            } else {
                $html .= '#';
            }
            $html .= '" id="' . $menu['id'] . '"';
            if ($pai_id == $menu['id']) {
                $html .= ' class="selectedMenuPai"';
            }
            $html .= '>';
            if (!empty($menu['icon_class'])) {
                $html .= $menu['icon_class'] . ' ';
            }
            $html .= '<span>' . $menu['nome'] . '</span>';
            if ($tem_submenus) {
                $html .= '<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>';
            }
            $html .= '</a>';
            if ($tem_submenus && !empty($html_sub)) {
                $html .= '<ul class="treeview-menu">';
                $html .= $html_sub;
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
        $CI =& get_instance();
        if (isset($CI->ion_auth) && method_exists($CI->ion_auth, 'is_admin') && $CI->ion_auth->is_admin()) {
            return true;
        }

        if (!is_array($permissoes)) {
            return false;
        }

        if ($submenu == false) {
            $ctrl = isset($menu['controlador']) ? $menu['controlador'] : '';
            $ctrl_lower = strtolower($ctrl);

            if (!empty($ctrl) && (array_key_exists($ctrl, $permissoes) || array_key_exists($ctrl_lower, $permissoes))) {
                return true;
            } else {
                if (!empty($menu['sub_menus']) && is_array($menu['sub_menus'])) {
                    if (hasMenu($menu['sub_menus'], $permissoes, true)) {
                        return true;
                    }
                }
                return false;
            }
        } else {
            if (is_array($menu)) {
                foreach ($menu as $item) {
                    $item_ctrl = isset($item['controlador']) ? $item['controlador'] : '';
                    $item_ctrl_lower = strtolower($item_ctrl);

                    if (!empty($item_ctrl) && (array_key_exists($item_ctrl, $permissoes) || array_key_exists($item_ctrl_lower, $permissoes))) {
                        return true;
                    }
                    if (!empty($item['sub_menus']) && is_array($item['sub_menus'])) {
                        if (hasMenu($item['sub_menus'], $permissoes, true)) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }
    }
}