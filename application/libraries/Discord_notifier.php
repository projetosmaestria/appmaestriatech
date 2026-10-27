<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Envia mensagens diretas pelo bot do Discord usando somente a API REST.
 */
class Discord_notifier {

    const API_URL = 'https://discord.com/api/v10';
    const MAX_MESSAGE_LENGTH = 2000;

    private $token;
    private $last_error = '';

    public function __construct()
    {
        $CI =& get_instance();
        $CI->config->load('discord', true);
        $this->token = $CI->config->item('discord_bot_token', 'discord');
    }

    public function is_configured()
    {
        return $this->token !== '';
    }

    public function last_error()
    {
        return $this->last_error;
    }

    public function send_direct_message($discord_user_id, $content)
    {
        $this->last_error = '';
        if (!$this->is_configured()) {
            $this->last_error = 'DISCORD_BOT_TOKEN não configurado.';
            return false;
        }

        $channel = $this->request('/users/@me/channels', array('recipient_id' => (string) $discord_user_id));
        if (!$channel || empty($channel['id'])) {
            return false;
        }

        $message = $this->request('/channels/' . $channel['id'] . '/messages', array(
            'content' => mb_substr($content, 0, self::MAX_MESSAGE_LENGTH, 'UTF-8'),
            'allowed_mentions' => array('parse' => array()),
        ));
        return $message && !empty($message['id']);
    }

    private function request($path, $payload, $attempt = 1)
    {
        $curl = curl_init(self::API_URL . $path);
        curl_setopt_array($curl, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bot ' . $this->token,
                'Content-Type: application/json',
                'User-Agent: SAXweb Kanban (https://app.xkeysti.com.br, 1.0)',
            ),
            CURLOPT_POSTFIELDS => json_encode($payload),
        ));
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($body === false) {
            $this->last_error = 'Falha de conexão com o Discord: ' . $curl_error;
            return false;
        }

        $response = json_decode($body, true);
        if ($status === 429 && $attempt < 3) {
            $retry_after = isset($response['retry_after']) ? (float) $response['retry_after'] : 1.0;
            usleep((int) (min($retry_after, 10) * 1000000));
            return $this->request($path, $payload, $attempt + 1);
        }
        if ($status < 200 || $status >= 300) {
            $this->last_error = 'Discord respondeu HTTP ' . $status . ': ' .
                (isset($response['message']) ? $response['message'] : substr((string) $body, 0, 200));
            return false;
        }
        return $response;
    }
}
