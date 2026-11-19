<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Endpoints para sistemas externos, autenticados pelo token gerado em Preferências > Integração
 * (Integracao_token_model). O token identifica a empresa: os clientes listados e os usuários criados são dela.
 *
 *   GET  integracao/empresas  Clientes (unidades) ativos da empresa, com CNPJ e nome fantasia.
 *   POST integracao/usuarios  Cadastra um usuário no grupo "Clientes", vinculado à unidade do CNPJ informado.
 *
 * O token vai no cabeçalho "Authorization: Bearer <token>" (ou "X-Api-Token: <token>", para servidores que
 * descartam o Authorization). Toda resposta é JSON com "ok"; o cadastro devolve também "logs", a lista do que
 * deu certo e do que deu errado ({ "tipo": "sucesso|info|aviso|erro", "mensagem": "...", "campo": "..." }).
 */
class Integracao extends CI_Controller {

    const LIMITE_REQUISICOES = 60;
    const JANELA_SEGUNDOS = 60;
    const MAXIMO_CNPJS = 50;

    private $logs = array();
    private $empresa_id = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        // CI_Controller não passa pelo MY_Controller, que é quem carrega o helper url (base_url).
        $this->load->helper('url');
        $this->load->model('integracao_token_model');
    }

    /**
     * GET integracao/empresas
     * { "ok": true, "total": 2, "empresas": [{ "cnpj": "12345678000190", "nome_fantasia": "Cliente X" }] }
     */
    public function empresas()
    {
        if (!$this->exigir_metodo(array('get', 'post')) || !$this->autenticar()) {
            return;
        }

        $linhas = $this->db->select('unidade, cnpj')
            ->where('empresa_id', $this->empresa_id)
            ->where('actived', 's')
            ->where("TRIM(cnpj) <> ''", null, false)
            ->order_by('unidade', 'ASC')
            ->get('unidades')
            ->result();

        $empresas = array();
        foreach ($linhas as $linha) {
            $cnpj = preg_replace('/\D/', '', (string) $linha->cnpj);
            // Mesmo CNPJ em duas unidades aparece uma vez só (é por ele que o cadastro encontra a unidade).
            if (strlen($cnpj) !== 14 || isset($empresas[$cnpj])) {
                continue;
            }
            $empresas[$cnpj] = array('cnpj' => $cnpj, 'nome_fantasia' => trim((string) $linha->unidade));
        }

        $this->responder(200, array('ok' => true, 'total' => count($empresas), 'empresas' => array_values($empresas)));
    }

    /**
     * POST integracao/usuarios  (JSON ou formulário)
     * { "nome": "Maria", "sobrenome": "Silva", "email": "maria@cliente.com.br", "celular": "(44) 99999-0000", "cnpj": "12.345.678/0001-90" }
     *
     * "cnpj" aceita uma ou várias unidades: lista JSON (["12.345.678/0001-90", "98.765.432/0001-10"]),
     * campos de formulário cnpj[] ou texto separado por vírgula/ponto e vírgula. O usuário é vinculado a todas.
     * Se algum CNPJ não for de uma unidade ativa da empresa, nada é gravado.
     *
     * Cria o usuário já ativo, vinculado à empresa, às unidades dos CNPJs e ao grupo "Clientes", e envia o e-mail
     * para ele cadastrar a senha (o mesmo do "Novo solicitante" da abertura de chamado).
     * 201 criado · 401 token · 404 CNPJ · 409 e-mail já existe · 422 dados inválidos · 500 falha ao gravar.
     */
    public function usuarios()
    {
        if (!$this->exigir_metodo(array('post')) || !$this->autenticar()) {
            return;
        }

        $nome = trim((string) $this->campo('nome'));
        $sobrenome = trim((string) $this->campo('sobrenome'));
        $email = strtolower(trim((string) $this->campo('email')));
        $celular = preg_replace('/\D/', '', (string) $this->campo('celular'));
        $cnpjs = $this->lista_cnpjs();

        // Valida tudo de uma vez, para o sistema externo receber todos os erros na mesma resposta.
        if ($nome === '') {
            $this->log('erro', 'Informe o nome.', 'nome');
        } elseif (mb_strlen($nome, 'UTF-8') > 50) {
            $this->log('erro', 'O nome pode ter no máximo 50 caracteres.', 'nome');
        }
        if ($sobrenome === '') {
            $this->log('erro', 'Informe o sobrenome.', 'sobrenome');
        } elseif (mb_strlen($sobrenome, 'UTF-8') > 50) {
            $this->log('erro', 'O sobrenome pode ter no máximo 50 caracteres.', 'sobrenome');
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $this->log('erro', 'Informe um e-mail válido.', 'email');
        }
        if (strlen($celular) < 10 || strlen($celular) > 11) {
            $this->log('erro', 'Informe um celular válido com DDD (10 ou 11 dígitos).', 'celular');
        }
        if (!$cnpjs) {
            $this->log('erro', 'Informe o CNPJ de pelo menos uma unidade.', 'cnpj');
        } elseif (count($cnpjs) > self::MAXIMO_CNPJS) {
            $this->log('erro', 'Envie no máximo ' . self::MAXIMO_CNPJS . ' CNPJs por usuário.', 'cnpj');
        }
        foreach ($cnpjs as $cnpj) {
            if (strlen($cnpj) !== 14) {
                $this->log('erro', 'CNPJ inválido: "' . $cnpj . '". Cada CNPJ precisa ter 14 dígitos.', 'cnpj');
            }
        }
        if ($this->tem_erro()) {
            $this->responder(422);
            return;
        }
        $this->log('info', 'Dados recebidos e validados (' . count($cnpjs) . (count($cnpjs) > 1 ? ' CNPJs' : ' CNPJ') . ').');

        // Todas as unidades precisam existir: com um CNPJ errado, nada é gravado.
        $unidades = array();
        foreach ($cnpjs as $cnpj) {
            $unidade = $this->unidade_por_cnpj($cnpj);
            if (!$unidade) {
                $this->log('erro', 'Nenhuma unidade ativa com o CNPJ ' . $this->formatar_cnpj($cnpj) . ' nesta empresa.', 'cnpj');
                continue;
            }
            $this->log('info', 'Unidade encontrada: ' . trim($unidade->unidade) . ' (#' . (int) $unidade->id . ', CNPJ ' . $this->formatar_cnpj($cnpj) . ').');
            $unidades[(int) $unidade->id] = array('id' => (int) $unidade->id, 'cnpj' => $cnpj, 'nome_fantasia' => trim($unidade->unidade));
        }
        if ($this->tem_erro()) {
            $this->responder(404);
            return;
        }
        $unidades = array_values($unidades);
        $unidades_ids = array();
        $unidades_nomes = array();
        foreach ($unidades as $u) {
            $unidades_ids[] = $u['id'];
            $unidades_nomes[] = $u['nome_fantasia'];
        }

        $this->load->model('ion_auth_model');
        $existente = $this->db->select('id')->where('email', $email)->limit(1)->get('users')->row();
        if ($existente) {
            $this->log('erro', 'Já existe um usuário com o e-mail ' . $email . ' (#' . (int) $existente->id . ').', 'email');
            $this->responder(409);
            return;
        }

        $this->load->model('chamados_model');
        $grupo = $this->chamados_model->get_grupo_clientes($this->empresa_id);
        if (!$grupo) {
            $this->log('erro', 'O grupo "Clientes" não existe nesta empresa. Cadastre-o em Grupos.');
            $this->responder(500);
            return;
        }

        $celular_formatado = strlen($celular) === 11
            ? '(' . substr($celular, 0, 2) . ') ' . substr($celular, 2, 5) . '-' . substr($celular, 7, 4)
            : '(' . substr($celular, 0, 2) . ') ' . substr($celular, 2, 4) . '-' . substr($celular, 6, 4);

        // O register do ion_auth lê as preferências da empresa da sessão; a integração não tem login.
        $empresa_sessao = $this->session->userdata('empresa_id');
        $this->session->set_userdata('empresa_id', $this->empresa_id);
        $usuario_id = $this->chamados_model->criar_solicitante(array(
            'first_name' => $nome,
            'last_name' => $sobrenome,
            'email' => $email,
            'celular' => $celular_formatado,
        ), $this->empresa_id, $unidades_ids, (int) $grupo->id);
        if ($empresa_sessao === null) {
            $this->session->unset_userdata('empresa_id');
        } else {
            $this->session->set_userdata('empresa_id', $empresa_sessao);
        }

        if (!$usuario_id) {
            $this->log('erro', 'Não foi possível gravar o usuário. Tente de novo.');
            log_message('error', 'Integracao::usuarios - falha ao criar ' . $email . ' nas unidades #' . implode(', #', $unidades_ids));
            $this->responder(500);
            return;
        }
        $this->log('sucesso', 'Usuário #' . $usuario_id . ' cadastrado e ativo no grupo "' . $grupo->name . '".');
        foreach ($unidades as $u) {
            $this->log('sucesso', 'Vinculado à unidade ' . $u['nome_fantasia'] . ' (CNPJ ' . $this->formatar_cnpj($u['cnpj']) . ').');
        }

        $this->ion_auth_model->create_auditoria(array(
            'user_id' => $usuario_id,
            'empresa_id' => $this->empresa_id,
            'controlador' => 'integracao',
            'metodo' => 'usuarios',
            'obs' => 'Usuário #' . $usuario_id . ' (' . $email . ') criado pela integração, vinculado às unidades #' . implode(', #', $unidades_ids) . ' e ao grupo #' . (int) $grupo->id . '.',
        ));

        // O usuário já está gravado: uma falha no e-mail vira aviso e não derruba a resposta de sucesso.
        $email_enviado = false;
        try {
            $this->load->library('senha_inicial');
            $email_enviado = $this->senha_inicial->enviar($email, $nome, implode(', ', $unidades_nomes), $this->empresa_id);
        } catch (Exception $e) {
            log_message('error', 'Integracao::usuarios - e-mail de senha para ' . $email . ': ' . $e->getMessage());
        } catch (Throwable $e) {
            log_message('error', 'Integracao::usuarios - e-mail de senha para ' . $email . ': ' . $e->getMessage());
        }
        if ($email_enviado) {
            $this->log('sucesso', 'E-mail para cadastrar a senha enviado para ' . $email . '.');
        } else {
            $this->log('aviso', 'O usuário foi criado, mas o e-mail para cadastrar a senha não foi enviado. Ele pode usar "Esqueci minha senha" na tela de login.');
        }

        $this->responder(201, array(
            'usuario' => array(
                'id' => $usuario_id,
                'nome' => $nome,
                'sobrenome' => $sobrenome,
                'email' => $email,
                'celular' => $celular_formatado,
                'unidades' => array_map(function ($u) {
                    return array('cnpj' => $u['cnpj'], 'nome_fantasia' => $u['nome_fantasia']);
                }, $unidades),
            ),
            'email_enviado' => $email_enviado,
        ));
    }

    /* ---------- Apoio ---------- */

    /**
     * CNPJs enviados, só os dígitos e sem repetição. Aceita lista JSON, cnpj[] no formulário ou texto
     * separado por vírgula, ponto e vírgula, barra vertical, espaço ou quebra de linha.
     */
    private function lista_cnpjs()
    {
        $bruto = $this->input->post('cnpj');
        if ($bruto === null) {
            $json = $this->corpo_json();
            $bruto = isset($json['cnpj']) ? $json['cnpj'] : (isset($json['cnpjs']) ? $json['cnpjs'] : '');
        }
        $itens = is_array($bruto) ? $bruto : preg_split('/[,;|\s]+/', (string) $bruto);
        $cnpjs = array();
        foreach ($itens as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $digitos = preg_replace('/\D/', '', (string) $item);
            if ($digitos !== '' && !in_array($digitos, $cnpjs, true)) {
                $cnpjs[] = $digitos;
            }
        }
        return $cnpjs;
    }

    // Unidade ativa da empresa com o CNPJ (compara só os dígitos, para aceitar o CNPJ gravado com ou sem máscara).
    private function unidade_por_cnpj($cnpj)
    {
        $linhas = $this->db->query("SELECT id, unidade
                FROM unidades
                WHERE empresa_id = ? AND actived = 's'
                    AND REPLACE(REPLACE(REPLACE(REPLACE(TRIM(cnpj), '.', ''), '/', ''), '-', ''), ' ', '') = ?
                ORDER BY id ASC",
            array($this->empresa_id, $cnpj))->result();
        if (count($linhas) > 1) {
            $this->log('aviso', 'Há ' . count($linhas) . ' unidades ativas com este CNPJ; o usuário foi vinculado à mais antiga (#' . (int) $linhas[0]->id . ').');
        }
        return $linhas ? $linhas[0] : null;
    }

    // Confere o token enviado e define a empresa dele.
    private function autenticar()
    {
        if (!$this->integracao_token_model->disponivel()) {
            $this->log('erro', 'Integração indisponível: aplique o script sql/2026-11-17_integracao_token.sql no banco.');
            $this->responder(503);
            return false;
        }
        if (!$this->dentro_do_limite()) {
            $this->log('erro', 'Muitas requisições. Tente novamente em instantes.');
            $this->responder(429);
            return false;
        }

        $enviado = '';
        $authorization = (string) $this->input->get_request_header('Authorization', true);
        if (preg_match('/^Bearer\s+(.+)$/i', trim($authorization), $m)) {
            $enviado = trim($m[1]);
        } else {
            $enviado = trim((string) $this->input->get_request_header('X-Api-Token', true));
        }
        $this->empresa_id = $this->integracao_token_model->empresa_do_token($enviado);
        if ($this->empresa_id <= 0) {
            $this->log('erro', 'Token de integração ausente, inválido ou revogado. Gere um em Preferências > Integração.');
            $this->responder(401);
            return false;
        }
        return true;
    }

    private function exigir_metodo($metodos)
    {
        if (in_array($this->input->method(), $metodos, true)) {
            return true;
        }
        $this->output->set_header('Allow: ' . strtoupper(implode(', ', $metodos)));
        $this->log('erro', 'Método não permitido.');
        $this->responder(405);
        return false;
    }

    // Aceita o campo como formulário ou no corpo JSON.
    private function campo($nome)
    {
        $valor = $this->input->post($nome);
        if ($valor !== null) {
            return is_scalar($valor) ? $valor : '';
        }
        $json = $this->corpo_json();
        return isset($json[$nome]) && is_scalar($json[$nome]) ? $json[$nome] : '';
    }

    private function corpo_json()
    {
        static $json = null;
        if ($json === null) {
            $json = json_decode($this->input->raw_input_stream, true);
            $json = is_array($json) ? $json : array();
        }
        return $json;
    }

    private function log($tipo, $mensagem, $campo = null)
    {
        $item = array('tipo' => $tipo, 'mensagem' => $mensagem);
        if ($campo !== null) {
            $item['campo'] = $campo;
        }
        $this->logs[] = $item;
    }

    private function tem_erro()
    {
        foreach ($this->logs as $item) {
            if ($item['tipo'] === 'erro') {
                return true;
            }
        }
        return false;
    }

    private function formatar_cnpj($cnpj)
    {
        return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
    }

    // Limite por IP em arquivo temporário (como no Saxweb_app). Se o arquivo não abrir, a requisição segue.
    private function dentro_do_limite()
    {
        $arquivo = sys_get_temp_dir() . '/integracao_' . md5($this->input->ip_address()) . '.json';
        $fp = @fopen($arquivo, 'c+');
        if (!$fp) {
            return true;
        }
        flock($fp, LOCK_EX);
        $registro = json_decode(stream_get_contents($fp), true);
        $agora = time();
        if (!is_array($registro) || !isset($registro['inicio'], $registro['total']) || $registro['inicio'] + self::JANELA_SEGUNDOS <= $agora) {
            $registro = array('inicio' => $agora, 'total' => 0);
        }
        $registro['total']++;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($registro));
        flock($fp, LOCK_UN);
        fclose($fp);
        return $registro['total'] <= self::LIMITE_REQUISICOES;
    }

    // Toda resposta leva "ok" (sem erro nos logs) e, se houver, os "logs".
    private function responder($status, $dados = array())
    {
        $corpo = array_merge(array('ok' => !$this->tem_erro() && $status < 400), $dados);
        if ($this->logs) {
            $corpo['logs'] = $this->logs;
        }
        $this->output->set_content_type('application/json', 'utf-8')
            ->set_status_header($status)
            ->set_output(json_encode($corpo));
    }
}
