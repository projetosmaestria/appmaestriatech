<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Envia modelos de mensagem aprovados pela Meta pelo canal oficial do FLW Chat.
 */
class Flwchat_notifier {

    // A Meta recusa variáveis com quebra de linha, tabulação ou mais de 4 espaços seguidos.
    const MAX_PARAM_LENGTH = 250;

    private $url;
    private $token;
    private $canal;
    private $last_error = '';
    private $last_message_id = '';

    public function __construct()
    {
        $CI =& get_instance();
        $CI->config->load('flwchat', true);
        $this->url = rtrim($CI->config->item('flwchat_url', 'flwchat'), '/');
        $this->token = $CI->config->item('flwchat_token', 'flwchat');
        $this->canal = preg_replace('/\D/', '', $CI->config->item('flwchat_canal', 'flwchat'));
    }

    public function is_configured()
    {
        return $this->token !== '' && $this->canal !== '';
    }

    public function last_error()
    {
        return $this->last_error;
    }

    public function last_message_id()
    {
        return $this->last_message_id;
    }

    /**
     * Converte o celular cadastrado para 55 + DDD + número. Retorna '' se não for um celular válido.
     */
    public static function normalize_phone($phone)
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        $digits = ltrim($digits, '0');
        if (strlen($digits) === 10 || strlen($digits) === 11) {
            $digits = '55' . $digits;
        }
        if (strlen($digits) < 12 || strlen($digits) > 13 || substr($digits, 0, 2) !== '55') {
            return '';
        }
        return $digits;
    }

    public static function clean_param($value)
    {
        $value = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)));
        if (mb_strlen($value, 'UTF-8') > self::MAX_PARAM_LENGTH) {
            $value = rtrim(mb_substr($value, 0, self::MAX_PARAM_LENGTH - 1, 'UTF-8')) . '…';
        }
        return $value !== '' ? $value : '-';
    }

    /**
     * @param string $to         Celular já normalizado (normalize_phone).
     * @param string $template_id Id do modelo no FLW Chat.
     * @param array  $parameters  Variáveis do modelo, com a chave igual ao nome no FLW Chat (ex.: [NOME]).
     */
    public function send_template($to, $template_id, $parameters, $sender_id = null)
    {
        $this->last_error = '';
        $this->last_message_id = '';
        if (!$this->is_configured() || $template_id === '') {
            $this->last_error = 'Integração com o WhatsApp não configurada.';
            return false;
        }

        $clean = array();
        foreach ($parameters as $name => $value) {
            $clean[$name] = self::clean_param($value);
        }
        $payload = array(
            'to' => $to,
            'from' => $this->canal,
            'templateId' => $template_id,
            'parameters' => $clean,
        );
        if ($sender_id !== null) {
            $payload['senderId'] = (string) $sender_id;
        }

        $curl = curl_init($this->url . '/v1/send/template');
        curl_setopt_array($curl, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ),
            CURLOPT_POSTFIELDS => json_encode($payload),
        ));
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($body === false) {
            $this->last_error = 'Falha de conexão com o FLW Chat: ' . $curl_error;
            return false;
        }

        $response = json_decode($body, true);
        if ($status < 200 || $status >= 300) {
            $detail = '';
            if (is_array($response)) {
                foreach (array('detail', 'title', 'text', 'error') as $key) {
                    if (!empty($response[$key]) && is_string($response[$key])) {
                        $detail = $response[$key];
                        break;
                    }
                }
            }
            $this->last_error = 'FLW Chat respondeu HTTP ' . $status . ': ' . ($detail !== '' ? $detail : substr((string) $body, 0, 300));
            return false;
        }
        if (is_array($response) && isset($response['status']) && $response['status'] === 'FAILED') {
            $this->last_error = 'FLW Chat recusou a mensagem: ' .
                (!empty($response['failedReason']) ? $response['failedReason'] : 'motivo não informado');
            return false;
        }

        $this->last_message_id = is_array($response) && !empty($response['id']) ? $response['id'] : '';
        return true;
    }
}
