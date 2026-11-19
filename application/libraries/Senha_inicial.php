<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * E-mail com o link para o usuário recém-criado cadastrar a própria senha.
 * O link é o mesmo do "Esqueci minha senha" (auth/reset_password, que não expira), mas o código é gerado aqui:
 * o Ion_auth_model::forgotten_password() manda por conta própria o e-mail genérico de redefinição, e o usuário
 * receberia dois e-mails.
 * Usado no "Novo solicitante" da abertura de chamado e no cadastro pela integração (que não tem o helper url
 * carregado pelo MY_Controller, por isso ele é carregado aqui).
 */
class Senha_inicial {

    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('ion_auth_model');
        $this->CI->load->library('phpmailer_library');
        $this->CI->load->helper(array('url', 'functions_helper'));
    }

    // Grava um código novo de "cadastrar senha" para o usuário do e-mail. Devolve o código ou false.
    private function gerar_codigo($email)
    {
        $forte = false;
        $bytes = function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes(32, $forte) : false;
        if ($bytes === false || !$forte) {
            return false;
        }
        $codigo = bin2hex($bytes);
        $this->CI->db->where('email', $email)->update('users', array(
            'forgotten_password_code' => $codigo,
            'forgotten_password_time' => time(),
        ));
        return $this->CI->db->affected_rows() === 1 ? $codigo : false;
    }

    /**
     * @param int $empresa_id empresa cuja configuração de e-mail (config_email) é usada
     * @return bool se o e-mail foi aceito pelo servidor SMTP
     */
    public function enviar($email, $first_name, $unidade_nome, $empresa_id)
    {
        $CI = $this->CI;
        $codigo = $this->gerar_codigo($email);
        if ($codigo === false) {
            return false;
        }
        $link = base_url('auth/reset_password/' . $codigo);

        $email_config_db = $CI->ion_auth_model->get_email_config((int) $empresa_id)->row();
        if ($email_config_db && $email_config_db->test == 1) {
            $email_config = rowToConfigEmail($email_config_db);
            $from_email = $email_config_db->from_email;
            $from_name = $email_config_db->from_name;
        } else {
            $email_config = $CI->config->item('email_config', 'ion_auth');
            $from_email = $CI->config->item('admin_email', 'ion_auth');
            $from_name = $CI->config->item('site_title', 'ion_auth');
        }

        $assunto = $from_name . ' - Cadastre sua senha de acesso';
        $corpo = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#333333;max-width:560px;">'
            . '<p>Olá, <strong>' . html_escape($first_name) . '</strong>!</p>'
            . '<p>Seu acesso ao <strong>' . html_escape($from_name) . '</strong> foi criado para o cliente <strong>' . html_escape($unidade_nome) . '</strong>, '
            . 'para você acompanhar os chamados abertos em seu nome.</p>'
            . '<p>Seu login é o e-mail <strong>' . html_escape($email) . '</strong>. Para entrar, cadastre sua senha no botão abaixo:</p>'
            . '<p style="margin:24px 0;"><a href="' . html_escape($link) . '" style="background:#3c8dbc;color:#ffffff;padding:10px 18px;text-decoration:none;border-radius:3px;display:inline-block;">Cadastrar minha senha</a></p>'
            . '<p style="font-size:12px;color:#777777;">Se o botão não funcionar, copie e cole este endereço no navegador:<br>' . html_escape($link) . '</p>'
            . '</div>';

        try {
            $mail = $CI->phpmailer_library->load();
            $mail->isSMTP();
            $mail->Host     = $email_config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $email_config['smtp_user'];
            $mail->Password = $email_config['smtp_pass'];
            $mail->SMTPSecure = 'tsl';
            $mail->Port     = $email_config['smtp_port'];
            $mail->setFrom($from_email, utf8_decode($from_name));
            $mail->addAddress($email);
            $mail->Subject = utf8_decode($assunto);
            $mail->isHTML(true);
            $mail->Body = utf8_decode($corpo);
            return (bool) $mail->send();
        } catch (Exception $e) {
            log_message('error', 'Senha_inicial::enviar - ' . $e->getMessage());
            return false;
        }
    }
}
