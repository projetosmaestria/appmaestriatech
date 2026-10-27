<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Catálogo de permissões dos grupos (controlador => métodos), montado sem executar os controllers:
 * - os controladores são os dos menus habilitados para a empresa (mais contatos_interno);
 * - os métodos vêm das funções públicas do arquivo do controller e das chamadas has_permissao('ctrl', 'acao')
 *   espalhadas pelo código (permissões como chamados/reagendar não são métodos).
 */
class Permissoes_model extends CI_Model {

    // Métodos auxiliares (AJAX, callbacks) que não fazem sentido como permissão.
    private $ignorados = array(
        '__construct', 'get_instance', 'buscar_endereco', 'buscar_metodos', 'buscar_detalhes_contato', 'buscar_detalhes_sms',
        'get_produtos_habilitados', 'get_modulo_categorias', 'get_modulos_habilitados', 'get_servicos', 'get_users_unidade',
        'buscar_seguimentos', 'mostrar_resolvidos', 'mostrar_corrigidos', 'q', 'q_geral', 'get_professores', 'remover_dispositivo',
        'get_unidades_livro', 'server_side', 'selecao_curso', 'get_alunos_map', 'has_fb_access_token', 'update_fb_access_token',
        'get_audio_base64', 'get_eventos', 'pagar_entrada_ead', 'pagar_contrato_ead', 'buscar_dados_user_voxy',
        'carregar_graficos_supervisos', 'add_vinculo', 'remove_vinculo', 'get_pacotesub', 'calculaPrazo', 'buscar_prod_compra',
        'buscar_prod_venda', 'carregar_historico', 'carregar_produtos', 'carregar_unidades', 'carregar_condicoes',
        'carregar_vencimentos', 'carregar_cursos', 'get_plano_acao', 'busca_sidebar', 'server_side_ativos', 'check_can_apontar',
        'alterar_id_assinatura_ajax', 'verificar_anulacao', 'oauth', 'produtos_cliente', 'salvar_habilitacao', 'alterar_status',
    );

    private $rotulos = array(
        'index' => 'Acessar / listar',
        'create' => 'Criar',
        'edit' => 'Editar',
        'delete' => 'Excluir',
        'deactivate' => 'Desativar',
        'activate' => 'Ativar',
        'details' => 'Ver detalhes',
        'historico' => 'Histórico',
        'status' => 'Alterar status',
        'imprimir' => 'Imprimir',
        'escalar' => 'Escalar',
        'reagendar' => 'Reagendar',
        'apontamentos' => 'Apontamentos',
        'profile' => 'Perfil',
        'metricas' => 'Métricas',
        'gantt' => 'Cronograma (Gantt)',
    );

    public function rotulo($metodo)
    {
        if (isset($this->rotulos[$metodo])) {
            return $this->rotulos[$metodo];
        }
        return ucfirst(str_replace('_', ' ', $metodo));
    }

    // Catálogo da empresa: array(controlador => array('nome' => ..., 'metodos' => array(...)))
    public function catalogo($empresa_id)
    {
        $nomes = array();
        foreach ($this->db->select('m.controlador, m.nome, m.pai_id')
            ->join('menus m', 'm.id = me.menus_id')
            ->where('me.empresas_id', (int) $empresa_id)
            ->order_by('m.pai_id IS NULL', 'DESC', false)
            ->get('menus_empresas me')->result() as $menu) {
            $controlador = strtolower(trim($menu->controlador));
            if ($controlador === '' || isset($nomes[$controlador])) {
                continue;
            }
            $nomes[$controlador] = $menu->nome;
        }
        if (!isset($nomes['contatos_interno'])) {
            $nomes['contatos_interno'] = 'Contatos internos';
        }

        $virtuais = $this->permissoes_no_codigo();
        $catalogo = array();
        foreach ($nomes as $controlador => $nome) {
            $metodos = $this->metodos_do_controller($controlador);
            if (isset($virtuais[$controlador])) {
                $metodos = array_merge($metodos, $virtuais[$controlador]);
            }
            $metodos = array_values(array_unique(array_diff($metodos, array_map('strtolower', $this->ignorados))));
            if (!$metodos) {
                continue;
            }
            // index primeiro; os demais em ordem alfabética.
            usort($metodos, function ($a, $b) {
                if ($a === 'index') { return -1; }
                if ($b === 'index') { return 1; }
                return strcmp($a, $b);
            });
            $catalogo[$controlador] = array('nome' => $nome, 'metodos' => $metodos);
        }
        uasort($catalogo, function ($a, $b) {
            return strcasecmp($a['nome'], $b['nome']);
        });
        return $catalogo;
    }

    // Todas as permissões possíveis (controlador => métodos), usada no grupo admin de uma empresa nova.
    public function todas()
    {
        $ignorados = array_map('strtolower', $this->ignorados);
        $todas = array();
        foreach (glob(APPPATH . 'controllers/*.php') as $arquivo) {
            $base = basename($arquivo, '.php');
            if (preg_match('/(^auth|^crons$|^index$|-|old|novo|backup|bkp|temp|teste|_alt$)/i', $base)) {
                continue;
            }
            $metodos = array_values(array_unique(array_diff($this->metodos_do_controller($base), $ignorados)));
            if ($metodos) {
                $todas[strtolower($base)] = $metodos;
            }
        }
        foreach ($this->permissoes_no_codigo() as $controlador => $metodos) {
            if (isset($todas[$controlador])) {
                $todas[$controlador] = array_values(array_unique(array_merge($todas[$controlador], $metodos)));
            }
        }
        ksort($todas);
        return $todas;
    }

    private function metodos_do_controller($controlador)
    {
        $arquivo = APPPATH . 'controllers/' . ucfirst($controlador) . '.php';
        if (!is_file($arquivo)) {
            return array();
        }
        preg_match_all('/^\s*public\s+function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/m', file_get_contents($arquivo), $encontrados);
        return array_map('strtolower', $encontrados[1]);
    }

    // Pares has_permissao('controlador', 'acao') escritos literalmente no código.
    private function permissoes_no_codigo()
    {
        $pares = array();
        $arquivos = array();
        foreach (array('controllers', 'views', 'models', 'libraries', 'helpers', 'core', 'hooks') as $pasta) {
            if (!is_dir(APPPATH . $pasta)) {
                continue;
            }
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APPPATH . $pasta, FilesystemIterator::SKIP_DOTS)) as $arquivo) {
                $arquivos[] = $arquivo;
            }
        }
        foreach ($arquivos as $arquivo) {
            if (substr($arquivo->getFilename(), -4) !== '.php') {
                continue;
            }
            $conteudo = file_get_contents($arquivo->getPathname());
            if (strpos($conteudo, 'has_permissao') === false) {
                continue;
            }
            preg_match_all('/has_permissao\(\s*[\'"]([a-z0-9_]+)[\'"]\s*,\s*[\'"]([a-z0-9_]+)[\'"]\s*\)/i', $conteudo, $encontrados, PREG_SET_ORDER);
            foreach ($encontrados as $par) {
                $pares[strtolower($par[1])][] = strtolower($par[2]);
            }
        }
        return $pares;
    }

    public static function desserializar($valor)
    {
        $permissoes = (is_string($valor) && $valor !== '') ? @unserialize($valor) : array();
        return is_array($permissoes) ? $permissoes : array();
    }

    // Aplica o que foi marcado no formulário: só controladores e métodos do catálogo mudam;
    // permissões de controladores fora do catálogo (menus desabilitados) são mantidas.
    public function mesclar($atuais, $marcadas, $catalogo)
    {
        $resultado = array();
        foreach ($atuais as $controlador => $metodos) {
            if (!isset($catalogo[$controlador]) && is_array($metodos) && $metodos) {
                $resultado[$controlador] = array_values($metodos);
            }
        }
        $marcadas = is_array($marcadas) ? $marcadas : array();
        foreach ($catalogo as $controlador => $item) {
            if (empty($marcadas[$controlador]) || !is_array($marcadas[$controlador])) {
                continue;
            }
            $metodos = array_values(array_intersect($item['metodos'], array_map('strval', $marcadas[$controlador])));
            if ($metodos) {
                $resultado[$controlador] = $metodos;
            }
        }
        ksort($resultado);
        return $resultado;
    }
}
