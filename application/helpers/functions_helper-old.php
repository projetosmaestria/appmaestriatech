<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('get_recursos_ws'))
{
    function get_recursos_ws($selecionados = array())
    {
        $ids_recursos = array(
            0 => "Clientes",
            1 => "Orçamento/Pedido",
            2 => "Faturamento",
            3 => "Financeiro",
            4 => "Pedagógico",
            5 => "Contatos"
        );
        $recursos_ws = array(
            0 => array(
                1=>"MaestriaTech para MaestriaTech",
                2=>"MaestriaTech para MaestriaTech"
            ),
            1 => array(
                1=>"MaestriaTech para MaestriaTech",
                2=>"MaestriaTech para MaestriaTech"
            ),
            2 => array(
                1=>"MaestriaTech para MaestriaTech",
                2=>"MaestriaTech para MaestriaTech"
            ),
            3 => array(
                1=>"MaestriaTech para MaestriaTech",
                2=>"MaestriaTech para MaestriaTech"
            ),
            4 => array(
                1=>"MaestriaTech para MaestriaTech",
                2=>"MaestriaTech para MaestriaTech"
            ),
            5 => array(
                1=>"MaestriaTech para MaestriaTech",
                2=>"MaestriaTech para MaestriaTech"
            )
        );
        $response = '';
        foreach ($recursos_ws as $cont => $recs) {
            $response .= '<ul  class="jtree_parent_node">
                <li>
                    <span class="jtree_expand jtree_node_open"> </span>
                    <label><input type="checkbox" id="'. $cont .'" parent-id="" class="jtree_parent_checkbox" name="recursos['.$cont.']" value=""';
                    if (@array_key_exists($cont,$selecionados))
                        $response .= ' checked';
                    $response .= '> '. $ids_recursos[$cont] .'</label>';

                    if(count($recs) > 0) {
                        $response .= '<ul class="jtree_child_node">';
                        foreach ($recs as $val => $rec) {
                            $response .= '
                                <li><label><input type="checkbox" name="recursos['.$cont.'][]" value="'. strtolower($val) .'" id="'. $cont .'-'. strtolower($val) .'" parent-id="'. $cont .'" class="jtree_child_checkbox"';
                            if(@in_array($val, $selecionados[$cont]))
                                $response .= ' checked';
                            $response .= '> '. $rec .'</label></li>';
                        }
                        $response .= '</ul>';
                    }
                    
            $response .= '</li>
            </ul>';
        }
        return $response;
    }   
}

if ( ! function_exists('get_tabelas_cliente_ws'))
{
    function get_tabelas_cliente_ws()
    {
        $tabelas = array(
            "cad_clie",
            "cad_vend",
            "cad_forn"
        );
        return $tabelas;
    }   
}


if ( ! function_exists('get_uf'))
{
    function get_uf()
    {
        return array(
	        "AC"=>"Acre",
	        "AL"=>"Alagoas",
	        "AP"=>"Amapá",
	        "AM"=>"Amazonas",
	        "BA"=>"Bahia",
	        "CE"=>"Ceará",
	        "DF"=>"Distrito Federal",
	        "ES"=>"Espírito Santo",
	        "GO"=>"Goiás",
	        "MA"=>"Maranhão",
	        "MT"=>"Mato Grosso",
	        "MS"=>"Mato Grosso do Sul",
	        "MG"=>"Minas Gerais",
	        "PA"=>"Pará",
	        "PB"=>"Paraíba",
	        "PR"=>"Paraná",
	        "PE"=>"Pernambuco",
	        "PI"=>"Piauí",
	        "RJ"=>"Rio de Janeiro",
	        "RN"=>"Rio Grande do Norte",
	        "RS"=>"Rio Grande do Sul",
	        "RO"=>"Rondônia",
	        "RR"=>"Roraima",
	        "SC"=>"Santa Catarina",
	        "SP"=>"São Paulo",
	        "SE"=>"Sergipe",
	        "TO"=>"Tocantins"
	    );
    }   
}

if ( ! function_exists('validar_cpf'))
{
    function validar_cpf($cpf = false) {
        function calc_digitos_posicoes($digitos, $posicoes = 10, $soma_digitos = 0) {
            for ($i = 0; $i < strlen($digitos); $i++) {
                $soma_digitos = $soma_digitos + ( $digitos[$i] * $posicoes );
                $posicoes--;
            }

            $soma_digitos = $soma_digitos % 11;

            if ($soma_digitos < 2) {
                $soma_digitos = 0;
            } else {
                $soma_digitos = 11 - $soma_digitos;
            }

            $cpf = $digitos . $soma_digitos;
            return $cpf;
        }

        if (!$cpf) {
            return false;
        }

        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        if (strlen($cpf) != 11) {
            return false;
        }

        $digitos = substr($cpf, 0, 9);
        $novo_cpf = calc_digitos_posicoes($digitos);
        $novo_cpf = calc_digitos_posicoes($novo_cpf, 11);
        if ($novo_cpf === $cpf) {
        	return true;
        } else {
        	return false;
        }
    }
}

if ( ! function_exists('validar_cnpj'))
{
    function validar_cnpj($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        $cnpj = (string) $cnpj;
        $cnpj_original = $cnpj;
        $primeiros_numeros_cnpj = substr($cnpj, 0, 12);

        function multiplica_cnpj($cnpj, $posicao = 5) {
            $calculo = 0;
            for ($i = 0; $i < strlen($cnpj); $i++) {
                $calculo = $calculo + ( $cnpj[$i] * $posicao );
                $posicao--;
                if ($posicao < 2) {
                    $posicao = 9;
                }
            }
            return $calculo;
        }

        $primeiro_calculo = multiplica_cnpj($primeiros_numeros_cnpj);
        $primeiro_digito = ( $primeiro_calculo % 11 ) < 2 ? 0 : 11 - ( $primeiro_calculo % 11 );
        $primeiros_numeros_cnpj .= $primeiro_digito;

        $segundo_calculo = multiplica_cnpj($primeiros_numeros_cnpj, 6);
        $segundo_digito = ( $segundo_calculo % 11 ) < 2 ? 0 : 11 - ( $segundo_calculo % 11 );
        $cnpj = $primeiros_numeros_cnpj . $segundo_digito;
        if ($cnpj === $cnpj_original) {
            return true;
        }else{
            return false;
        }
    }
}

if ( ! function_exists('show_alert_success'))
{
    function show_alert_success($msg) {
        return '
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-check"></i> Sucesso!</h4>
                '.$msg.'
            </div>
        ';
    }
}

if ( ! function_exists('show_alert_error'))
{
    function show_alert_error($msg) {
        return '
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Erro!</h4>
                '.$msg.'
            </div>
        ';
    }
}

if ( ! function_exists('show_alert_warning'))
{
    function show_alert_warning($msg) {
        return '
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-warning"></i> Atenção!</h4>
                '.$msg.'
            </div>
        ';
    }
}

if ( ! function_exists('show_alert_info'))
{
    function show_alert_info($msg) {
        return '
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-info"></i> Informação!</h4>
                '.$msg.'
            </div>
        ';
    }
}

if ( ! function_exists('get_pagination_config'))
{
    function get_pagination_config($url, $total = 0, $offset = 1, $sort_by = null, $order = null) {
        $de = $offset+1;
        $ate = ($offset+15 > $total) ? $total : $offset+15;
        $retorno = array(
            "base_url" => $url,
            "per_page" => 15,
            "num_links" => 3,
            "uri_segment" => 3,
            "total_rows" => $total,
            "full_tag_open" => "<div class='col-sm-5'>Mostrando de ".$de." até ".$ate." de ".$total." registros</div><div class='col-sm-7 text-right'><div class='dataTables_paginate paging_simple_numbers'><ul class='pagination'>",
            "full_tag_close" => "</ul></div></div>",
            "first_link" => FALSE,
            "last_link" => FALSE,
            "first_tag_open" => "<li>",
            "first_tag_close" => "</li>",
            "prev_link" => "Anterior",
            "prev_tag_open" => "<li class='prev'>",
            "prev_tag_close" => "</li>",
            "next_link" => "Próxima",
            "next_tag_open" => "<li class='next'>",
            "next_tag_close" => "</li>",
            "last_tag_open" => "<li>",
            "last_tag_close" => "</li>",
            "cur_tag_open" => "<li class='active'><a href='#'>",
            "cur_tag_close" => "</a></li>",
            "num_tag_open" => "<li>",
            "num_tag_close" => "</li>",
            "reuse_query_string" => true,
            "enable_query_strings" => true,
            "page_query_string" => true
        );
        if($sort_by == null || $order == null) {
            $retorno['suffix'] = '&sort_by=' . $sort_by . '&order=' . $order;
        }
        return $retorno;
    }
}


if ( ! function_exists('get_controladores'))
{
    function get_controladores() {
        foreach(glob(APPPATH . 'controllers/*' . EXT) as $controller)
        {
            if(basename($controller, EXT) != "Auth" && basename($controller, EXT) != "Auth_original" && basename($controller, EXT) != "Crons") {
                include_once APPPATH.'controllers/'.basename($controller);
                $controllers[] = basename($controller, EXT);
            }
        }
        return $controllers;
    }
}

if ( ! function_exists('get_metodos'))
{
    function get_metodos($controlador) {
        $aMethods = get_class_methods($controlador);
        $aUserMethods = array();
        foreach($aMethods as $method) {
            if(
                $method != '__construct' && 
                $method != 'get_instance' && 
                $method != $controlador && 
                $method != 'buscar_endereco' && 
                $method != 'buscar_metodos' && 
                $method != 'buscar_detalhes_contato' && 
                $method != 'buscar_detalhes_sms' && 
                $method != 'get_produtos_habilitados' &&
                $method != 'get_modulo_categorias' &&
                $method != 'get_modulos_habilitados' &&
                $method != 'get_servicos' &&
                $method != 'get_users_unidade' &&
                $method != 'buscar_seguimentos' &&
                $method != 'mostrar_resolvidos' &&
                $method != 'mostrar_corrigidos' &&
                $method != 'q' &&
                $method != 'q_geral' &&
                $method != 'get_professores' &&
                $method != 'remover_dispositivo' &&
                $method != 'get_unidades_livro' &&
                $method != 'server_side' &&
                $method != 'selecao_curso' &&
                $method != 'get_alunos_map' &&
                $method != 'has_fb_access_token' &&
                $method != 'update_fb_access_token' &&
                $method != 'get_audio_base64' &&
                $method != 'get_eventos' &&
                $method != 'pagar_entrada_ead' &&
                $method != 'pagar_contrato_ead' &&
                $method != 'buscar_dados_user_voxy' &&
                $method != 'carregar_graficos_supervisos' &&
                $method != 'add_vinculo' &&
                $method != 'remove_vinculo' &&
                $method != 'get_pacotesub' &&
                $method != 'calculaPrazo' &&
                $method != 'buscar_prod_compra' &&
                $method != 'buscar_prod_venda' &&
                $method != 'carregar_historico' &&
                $method != 'carregar_produtos' &&
                $method != 'carregar_unidades' &&
                $method != 'carregar_condicoes' &&
                $method != 'carregar_vencimentos' &&
                $method != 'carregar_cursos' &&
                $method != 'cadastrar_habilitacao' &&
                $method != 'atualizar_habilitacao' &&
                $method != 'desabilitar_habilitacao' &&
                $method != 'get_plano_acao' &&
                $method != 'busca_sidebar' &&
                $method != 'server_side_ativos' &&
                $method != 'check_can_apontar' &&
                $method != 'alterar_id_assinatura_ajax' &&
                $method != 'verificar_anulacao' &&
                $method != 'oauth'
            ) {
                $aUserMethods[] = $method;
            }
        }
        return $aUserMethods;
    }
}

if ( ! function_exists('get_permissoes'))
{
    function get_permissoes($menus_empresa, $selecionados = array()){
        $menus = array();
        foreach ($menus_empresa as $menu) {
            $menus[] = $menu->controlador;
        }

        $controladores = get_controladores();
        $lista_controladores_metodos = array();
        foreach ($controladores as $control) {
            if(in_array(strtolower($control), $menus) || strtolower($control) == "contatos_interno") {
                $lista_controladores_metodos[$control] = get_metodos($control);
            }
        }

        $response = '';
        foreach ($lista_controladores_metodos as $cont => $mets) {
            $response .= '<ul  class="jtree_parent_node">
                <li>
                    <span class="jtree_expand jtree_node_open"> </span>
                    <label><input type="checkbox" id="'. strtolower($cont) .'" parent-id="" class="jtree_parent_checkbox" name="permissoes['.strtolower($cont).']" value=""';
                    if (@array_key_exists(strtolower($cont),$selecionados))
                        $response .= ' checked';
                    $response .= '> '. $cont .'</label>';

                    if(count($mets) > 0) {
                        $response .= '<ul class="jtree_child_node">';
                        foreach ($mets as $met) {
                            $response .= '
                                <li><label><input type="checkbox" name="permissoes['.strtolower($cont).'][]" value="'. strtolower($met) .'" id="'. strtolower($met) .'" parent-id="'. strtolower($cont) .'" class="jtree_child_checkbox"';
                            if(@in_array($met, $selecionados[strtolower($cont)]))
                                $response .= ' checked';
                            $response .= '> '. $met .'</label></li>';
                        }
                        $response .= '</ul>';
                    }
                    
            $response .= '</li>
            </ul>';
        }
        return $response;
    }
}

if ( ! function_exists('get_all_permissoes'))
{
    function get_all_permissoes(){
        $controladores = get_controladores();
        $lista_controladores_metodos = array();
        foreach ($controladores as $control) {
            $lista_controladores_metodos[strtolower($control)] = get_metodos($control);
        }
        return $lista_controladores_metodos;
    }
}

if ( ! function_exists('construirMenu'))
{
    function construirMenu(array $menus, array &$menuFinal, $menuSuperiorId, $nivel = 0)
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
     
            construirMenu($menus, $menuFinal[$i]['sub_menus'], $menuFinal[$i]['id'], $nivel);
        }
    }
    
}

if ( ! function_exists('getHtml'))
{
    function getHtml($menu, $pai_id = null){
        $html = '<li><a href="#" id="'.$menu['id'].'"';
        if($pai_id == $menu['id'])
            $html .= ' class="selectedMenuPai"';
        $html .= '>' . $menu['nome'] . '</a>';
        if(!empty($menu['sub_menus'])){
            $html .= '<ul>';
            foreach($menu['sub_menus'] as $sub){
                $html .= getHtml($sub, $pai_id);
            }
            $html .= '</ul>';
        }
        $html .= '</li>';
        
        return $html;
    }
}

if ( ! function_exists('validateDate'))
{
    function validateDate($date)
    {
        $d = DateTime::createFromFormat('d/m/Y', $date);
        return $d && $d->format('d/m/Y') === $date;
    }
}

if ( ! function_exists('convertDateBRToEU'))
{
    function convertDateBRToEU($date)
    {
        $date = str_replace('/', '-', $date);
        return date('Y-m-d', strtotime($date));
    }
}

if ( ! function_exists('convertDateEUToBR'))
{
    function convertDateEUToBR($date)
    {
        return date('d/m/Y', strtotime($date));
    }
}

if ( ! function_exists('get_upload_config'))
{
    function get_upload_config()
    {
        $max_size = 2; //10MB
        $config['upload_path']          = './upload/anexo/';
        $config['allowed_types']        = 'jpg|png|gif|jpeg|bmp|xls|xlsx|xlsm|csv|doc|docx|pdf|zip|rar|txt';
        $config['max_size']             = 1024*$max_size;
        return $config;
    }   
}

if ( ! function_exists('calcula_horas_uteis'))
{
    function calcula_horas_uteis($prazo, $inicioExpediente = null, $fimExpediente = null, $dataInicio = null) {
        $date = new DateTime();
        
        if($dataInicio != null){
            $date = $dataInicio;
        }

        if($inicioExpediente == null)
            $inicioExpediente = 8 * 60;
        else
            $inicioExpediente = ((int)$inicioExpediente) * 60;
        if($fimExpediente == null)
            $fimExpediente = 18 * 60;
        else
            $fimExpediente = ((int)$fimExpediente) * 60;
        
        $feriados = (get_feriados_nacionais(date("Y")));
        
        // prazo em minutos
        $prazoMinutos = $prazo * 60;
        while( $prazoMinutos > 0 ){
            $hora = ($date->format('H') * 60) + $date->format('i');
            
            // se for menor que a hora do inicio do expediente
            if($hora < $inicioExpediente){
                // igual a hora do expediente
                $date->setTime(0, $inicioExpediente, 0);
                continue;
            }
            // data calculada
            $data = $date->format('d/m/Y');

            // vai para o dia seguinte se ñ for hora ou dia util.
            if(in_array($data,$feriados) || $hora >= $fimExpediente || $date->format('w') == 0){ 
                $date->modify('+1 day');
                $date->setTime(0, $inicioExpediente, 0);
                continue;
            }
            
            
            // minutos para o fim do expediente de hoje
            $minutos = $fimExpediente - $hora;
            // tira do prazo os já calculado.
            $prazoMinutos -= $minutos;
            
            if($prazoMinutos < 0){
                // tira o que estourou
                $minutos += $prazoMinutos;
            }
            
            // adiciona os minutos do calculo na data de prazo final
            $date->modify('+' . $minutos . ' minute');
        }
        
        return $date;
    }
}

if ( ! function_exists('get_feriados_nacionais'))
{
    function get_feriados_nacionais($ano){
        $dia = 86400;
        $datas = array();
        $datas['pascoa'] = easter_date($ano);
        $datas['sexta_santa'] = $datas['pascoa'] - (2 * $dia);
        $datas['carnaval'] = $datas['pascoa'] - (47 * $dia);
        $datas['corpus_cristi'] = $datas['pascoa'] + (60 * $dia);
        $feriados = array (
             '01/01/'.$ano,
             '02/02/'.$ano, // Navegantes
             date('d/m',$datas['carnaval']).'/'.$ano,
             date('d/m',$datas['sexta_santa']).'/'.$ano,
             date('d/m',$datas['pascoa']).'/'.$ano,
             '21/04/'.$ano,
             '01/05/'.$ano,
             date('d/m',$datas['corpus_cristi']).'/'.$ano,
             '12/10/'.$ano,
             '02/11/'.$ano,
             '15/11/'.$ano,
             '25/12/'.$ano,
          );
        return $feriados;
    }
}

if ( ! function_exists('get_diff_dates'))
{
    function get_diff_dates($data1, $data2){
        $one = new DateTime($data1);
        $two = new DateTime($data2);
         
        $dateInterval = $one->diff($two);
        return $dateInterval;
    }
}

if ( ! function_exists('user_agent'))
{
    function user_agent($device_info){
        $iPod = strpos($device_info,"iPod");
        $iPhone = strpos($device_info,"iPhone");
        $iPad = strpos($device_info,"iPad");
        $android = strpos($device_info,"Android");
        $java = strpos($device_info,"java");

        if($iPad||$iPhone||$iPod):
            return 'ios';
        elseif($android || $java):
            return 'android';
        else:
            return 'pc';
        endif;
    }
}

if ( ! function_exists('formatCelApi'))
{
    function formatCelApi($cel) {
        $cel = str_replace("(", "", $cel);
        $cel = str_replace(")", "", $cel);
        $cel = str_replace(" ", "", $cel);
        $cel = str_replace("-", "", $cel);
        return "55".$cel;
    }
}

if ( ! function_exists('getCodesDescription'))
{
    function getCodesDescription($code, $array){
        $status = array(
            0 => 'Ok',
            1 => 'Agendada',
            2 => 'Enviada',
            3 => 'Entregue',
            4 => 'Não recebida',
            5 => 'Bloqueada - Sem corbetura',
            6 => 'Bloqueada - Lista negra',
            7 => 'Bloqueada - Número inválido',
            8 => 'Bloqueada - Conteúdo não permitido',
            8 => 'Bloqueada - Mensagem expirada',
            9 => 'Bloqueada',
            10 => 'Erro'
        );
        $detail = array(
            0 => 'Mensagem enviada',
            2 => 'Mensagem cancelada com sucesso',
            10 => 'Empty message content',
            11 => 'Message body invalid',
            12 => 'Message content overflow',
            13 => 'Incorrect or incomplete ‘to’ mobile number',
            14 => 'Empty ‘to’ mobile number',
            15 => 'Scheduling date invalid or incorrect',
            16 => 'ID overflow',
            17 => 'Parameter ‘url’ is invalid or incorrect',
            18 => 'Field ‘from’ invalid',
            21 => '‘id’ fieldismandatory',
            80 => 'Message with same ID already sent',
            100 => 'Mensagem em fila',
            110 => 'Message sent to operator',
            111 => 'Message confirmation unavailable',
            120 => 'Mensagem recebida por celular',
            130 => 'Mensagem bloqueada',
            131 => 'Message blocked by predictive cleansing',
            132 => 'Mensagem já cancelada',
            133 => 'Message content in analysis',
            134 => 'Mensagem bloqueada por conteúdo indevido',
            135 => 'Aggregate is Invalid or Inactive',
            136 => 'Message expired',
            140 => 'Mobile number not covered',
            141 => 'International sending not allowed',
            145 => 'Número de celular inativo',
            150 => 'Mensagem expirada na operadora',
            160 => 'Erro na rede da operadora',
            161 => 'Mensagem rejeitada pela operadora',
            162 => 'Mensagem cancelada ou bloqueada pela operadora',
            170 => 'Bad message',
            171 => 'Bad number',
            172 => 'Missing parameter',
            180 => 'Message ID notfound',
            190 => 'Unknown error',
            200 => 'Mensagens enviadas',
            210 => 'Messages scheduled but Account Limit Reached',
            240 => 'File empty or not sent',
            241 => 'File too large',
            242 => 'File readerror',
            300 => 'Received messages found',
            301 => 'No received messages found',
            400 => 'Entity saved',
            900 => 'Authentication error',
            901 => 'Account type not support this operation.',
            990 => 'Account Limit Reached – Please contact support',
            998 => 'Wrong operation requested',
            999 => 'Erro desconhecido'
        );

        if($array == 'status'):
            foreach($status as $key => $value):
                if($key == $code):
                    $description = $value;
                endif;                
            endforeach;
        elseif($array == 'detail'):
            foreach($detail as $key => $value):
                if($key == $code):
                    $description = $value;
                endif;
            endforeach;
        endif;
        return $description;
    }
}

if ( ! function_exists('rowToConfigEmail'))
{
    function rowToConfigEmail($row){
        $email_config = array(
            'protocol' => $row->protocol,
            'smtp_host' => $row->host,
            'smtp_port' => $row->port,
            'smtp_user' => $row->user,
            'smtp_pass' => $row->pass,
            'mailtype'  => 'html', 
            'charset'   => 'iso-8859-1'
        );
        return $email_config;
    }
}

if ( ! function_exists('get_padrao_email'))
{
    function get_padrao_email()
    {
        return array(
            "1" => array("id" => "activate", "desc" => "Cadastro de Usuário"),
            "2" => array("id" => "activate_aluno", "desc" => "Cadastro de Aluno"),
            "3" => array("id" => "forgot_password", "desc" => "Esqueci a Senha"),
            "4" => array("id" => "novo_historico", "desc" => "Novo Histórico Usuário"),
            "5" => array("id" => "novo_chamado", "desc" => "Chamado #{chamado_id} - criado"),
            "6" => array("id" => "novo_dialogo", "desc" => "Chamado #{chamado_id} - novo diálogo"),
            "7" => array("id" => "novo_anexo", "desc" => "Chamado #{chamado_id} - novo anexo"),
            "8" => array("id" => "reagendar", "desc" => "Chamado #{chamado_id} - reagendado"),
            "9" => array("id" => "escalar", "desc" => "Chamado #{chamado_id} - escalado à você"),
            "10" => array("id" => "att_status", "desc" => "Chamado #{chamado_id} - status atualizado"),
            "11" => array("id" => "agenda_compartilhada", "desc" => "Agenda {agenda_id} compartilhada à você"),
            "12" => array("id" => "novo_cupom", "desc" => "Cupom {cupom} compartilhado com você"),
            "13" => array("id" => "cancelar_contrato", "desc" => "Cancelamento do contrato #{contrato_id}")
        );
    }   
}

if ( ! function_exists('generate_timeline_html'))
{
    function generate_timeline_html($timeline)
    {
        $html = '<div class="box-body">
            <div class="col-md-12">
                <ul class="timeline">';
                    
                        $last_time_dialog = "";
                        foreach($timeline as $dialog): 
                            $created_dialog = explode(" ", $dialog['created']);
                            $created_hr_dialog = explode(":", $created_dialog[1]);
                            if($last_time_dialog != $created_dialog[0]) {
                                $html .= '
                                    <li class="time-label">
                                          <span class="bg-blue">
                                            '.date("d M Y", strtotime($created_dialog[0])).'
                                          </span>
                                    </li>
                                ';
                                $last_time_dialog = $created_dialog[0];
                            }
                    $html .= '<li>';
                    if(@$dialog['anexo']): 
                                $html .= '<i class="fa fa-file-text bg-purple"></i>';
                    else:
                                $html .= '<i class="fa fa-comments bg-aqua"></i>';
                    endif;
                    $html .= '
                                  <div class="timeline-item">
                                    <span class="time"><i class="fa fa-clock-o"></i> '.$created_hr_dialog[0].":".$created_hr_dialog[1].'</span>

                                    <h3 class="timeline-header"><a href="#">'.$dialog['first_name']." ".$dialog['last_name'].'</a> respondeu</h3>

                                    <div class="timeline-body">'.$dialog['descricao'].'</div>';

                    if(@$dialog['anexo']):
                                        $html .= '<div class="timeline-footer">
                                        <a href="'.base_url("upload/anexo/".$dialog['anexo']).'" target="_blank" class="btn btn-primary btn-xs" rel="tooltip" data-placement="top" title="Download do anexo"><i class="fa fa-floppy-o"></i> Download</a>
                                        </div>';
                    endif;
                                  $html .= '</div>
                                </li>';
                    endforeach;
                    $html .= '<li>
                      <i class="fa fa-clock-o bg-gray"></i>
                    </li>
                </ul>
            </div>
        </div>';
        $html .= "
            <style>
                html,body{height:100%}.layout-boxed html,.layout-boxed body{height:100%}body{font-family:'Source Sans Pro','Helvetica Neue',Helvetica,Arial,sans-serif;font-weight:400;overflow-x:hidden;overflow-y:auto}
                .timeline{position:relative;margin:0 0 30px 0;padding:0;list-style:none}.timeline:before{content:'';position:absolute;top:0;bottom:0;width:4px;background:#ddd;left:31px;margin:0;border-radius:2px}.timeline>li{position:relative;margin-right:10px;margin-bottom:15px}.timeline>li:before,.timeline>li:after{content:' ';display:table}.timeline>li:after{clear:both}.timeline>li>.timeline-item{-webkit-box-shadow:0 1px 1px rgba(0,0,0,0.1);box-shadow:0 1px 1px rgba(0,0,0,0.1);border-radius:3px;margin-top:0;background:#fff;color:#444;margin-left:60px;margin-right:15px;padding:0;position:relative}.timeline>li>.timeline-item>.time{color:#999;float:right;padding:10px;font-size:12px}.timeline>li>.timeline-item>.timeline-header{margin:0;color:#555;border-bottom:1px solid #f4f4f4;padding:10px;font-size:16px;line-height:1.1}.timeline>li>.timeline-item>.timeline-header>a{font-weight:600}.timeline>li>.timeline-item>.timeline-body,.timeline>li>.timeline-item>.timeline-footer{padding:10px}.timeline>li>.fa,.timeline>li>.glyphicon,.timeline>li>.ion{width:30px;height:30px;font-size:15px;line-height:30px;position:absolute;color:#666;background:#d2d6de;border-radius:50%;text-align:center;left:18px;top:0}.timeline>.time-label>span{font-weight:600;padding:5px;display:inline-block;background-color:#fff;border-radius:4px}.timeline-inverse>li>.timeline-item{background:#f0f0f0;border:1px solid #ddd;-webkit-box-shadow:none;box-shadow:none}.timeline-inverse>li>.timeline-item>.timeline-header{border-bottom-color:#ddd}
                .bg-red,.bg-yellow,.bg-aqua,.bg-blue,.bg-light-blue,.bg-green,.bg-navy,.bg-teal,.bg-olive,.bg-lime,.bg-orange,.bg-fuchsia,.bg-purple,.bg-maroon,.bg-black,.bg-red-active,.bg-yellow-active,.bg-aqua-active,.bg-blue-active,.bg-light-blue-active,.bg-green-active,.bg-navy-active,.bg-teal-active,.bg-olive-active,.bg-lime-active,.bg-orange-active,.bg-fuchsia-active,.bg-purple-active,.bg-maroon-active,.bg-black-active,.callout.callout-danger,.callout.callout-warning,.callout.callout-info,.callout.callout-success,.alert-success,.alert-danger,.alert-error,.alert-warning,.alert-info,.label-danger,.label-info,.label-warning,.label-primary,.label-success,.modal-primary .modal-body,.modal-primary .modal-header,.modal-primary .modal-footer,.modal-warning .modal-body,.modal-warning .modal-header,.modal-warning .modal-footer,.modal-info .modal-body,.modal-info .modal-header,.modal-info .modal-footer,.modal-success .modal-body,.modal-success .modal-header,.modal-success .modal-footer,.modal-danger .modal-body,.modal-danger .modal-header,.modal-danger .modal-footer{color:#fff !important}.bg-gray{color:#000;background-color:#d2d6de !important}.bg-aqua{background-color:#00c0ef !important}.bg-blue{background-color:#0073b7 !important}.bg-purple{background-color:#605ca8 !important}
            </style>";
        return $html;
    }   
}

if ( ! function_exists('geraCupons'))
{
    function geraCupons($limit)
    {
        $str ="ABCDEFGHIJKLMNOPQRSTUVXWYZ1234567890";
        $maximo = strlen($str)-1;
        $retorno = null;
 
        //Gera o cupom
        for($i = 0; $i< $limit; $i++):
 
            //separa o cupom de 4 em 4
            /*if(($i % 4 == 0) && ($i != 0))
            {
                $retorno .= "-";
            }*/
 
            //incrementa gerando um cupom aleatório.
            $retorno .= substr($str,mt_rand(0, $maximo),1);
            //$retorno .= $str{mt_rand(0, $maximo)};
 
        endfor;
 
        //retorna o valor gerado.
        return $retorno;   
 
    }
}

if ( ! function_exists('getImgSizes'))
{
    function getImgSizes() {
        $arr_size = array(
            'small' => array('width' =>25, 'height' =>27 ),
            'normal' => array('width' =>35, 'height' =>37 ),
            'large' => array('width' =>52, 'height' =>55 ),
            'xlarge' => array('width' =>70, 'height' =>74 ), 
            'xxlarge' => array('width' =>106, 'height' =>111 )
        );
        return $arr_size;
    }
}


if ( ! function_exists('get_modulos'))
{
    function get_modulos($id = null)
    {
        $m = array(
            "8" => "Venda e Faturamento",
            "10" => "Compra e Estoque",
            "9" => "Financeiro",
            "11" => "Contábil e Escrita",
            "12" => "PPCP",
            "7" => "Pedagógico",
            "13" => "CRM e BI",
            "14" => "Gerenciamento de Projeto",
            "16" => "Imobiliária",
            "20" => "Editora",
            "25" => "Parâmetro e Configuração",
        );

        if($id != null) {
            return $m[$id]; 
        }
        return $m;
    }   
}


if ( ! function_exists('AddPlayTime'))
{
    function AddPlayTime($times) {
        $minutes = 0;
        foreach ($times as $time) {
            list($hour, $minute) = explode(':', $time);
            $minutes += $hour * 60;
            $minutes += $minute;
        }

        $hours = floor($minutes / 60);
        $minutes -= $hours * 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}


if ( ! function_exists('quantoTempoPassou'))
{
    function quantoTempoPassou($dataMenor, $dataMaior) {
        $date_time = new DateTime( $dataMaior );
        $diff = $date_time->diff( new DateTime( $dataMenor ) );

        $time_to_show = "";

        if($diff->format('%y') > 0)
            $time_to_show = $diff->format('%y ano(s)');
        elseif($diff->format('%m') > 0)
            $time_to_show = $diff->format('%m mês(es)');
        elseif($diff->format('%d') > 0)
            $time_to_show = $diff->format('%d dia(s)');
        elseif($diff->format('%H') > 0)
            $time_to_show = $diff->format('%H hora(s)');
        elseif($diff->format('%i') > 0)
            $time_to_show = $diff->format('%i minuto(s)');
        elseif($diff->format('%s') > 0)
            $time_to_show = $diff->format('%s segundo(s)');
    }
}

if ( ! function_exists('get_mes_extenso'))
{
    function get_mes_extenso($mes) {
        switch ($mes){
            case 1: $mes = "Janeiro"; break;
            case 2: $mes = "Fevereiro"; break;
            case 3: $mes = "Março"; break;
            case 4: $mes = "Abril"; break;
            case 5: $mes = "Maio"; break;
            case 6: $mes = "Junho"; break;
            case 7: $mes = "Julho"; break;
            case 8: $mes = "Agosto"; break;
            case 9: $mes = "Setembro"; break;
            case 10: $mes = "Outubro"; break;
            case 11: $mes = "Novembro"; break;
            case 12: $mes = "Dezembro"; break;
        }
        return $mes;
    }
}

if ( ! function_exists('removerAcentos') )
{
    function removerAcentos($string)
    {
        $caracteres_sem_acento = array(
            'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Â'=>'Z', 'Â'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
            'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
            'Ï'=>'I', 'Ñ'=>'N', 'Å'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
            'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
            'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
            'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'Å'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
            'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
            'Ä'=>'a', 'î'=>'i', 'â'=>'a', 'ã'=>'a', 'È'=>'s', 'È'=>'t', 'Ä'=>'A', 'Î'=>'I', 'Â'=>'A', 'È'=>'S', 'È'=>'T', 'ç'=>'c',
        );

        return strtr($string, $caracteres_sem_acento);
    }
}