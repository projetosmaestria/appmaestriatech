<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Token dos endpoints de integração (controllers/Integracao.php), gerado em Preferências > Integração.
 * O banco guarda só o hash SHA-256 e o começo do token (para reconhecer qual está em uso): o token completo
 * aparece uma única vez, na hora de gerar. Cada empresa tem no máximo um token ativo, e o token diz de qual
 * empresa são os clientes listados e os usuários cadastrados.
 * Tabela criada por sql/2026-11-17_integracao_token.sql.
 */
class Integracao_token_model extends CI_Model
{
    const TABELA = 'integracao_token';
    const PREFIXO = 'xk_';

    public function disponivel()
    {
        return $this->db->table_exists(self::TABELA);
    }

    // Token ativo da empresa (sem o hash), com o nome de quem gerou; null se não houver.
    public function ativo($empresa_id)
    {
        if (!$this->disponivel()) {
            return null;
        }
        return $this->db->select('t.id, t.prefixo, t.created_at, t.ultimo_uso_em, u.first_name, u.last_name')
            ->join('users u', 'u.id = t.criado_por', 'left')
            ->where('t.empresa_id', (int) $empresa_id)
            ->where('t.revogado_em IS NULL', null, false)
            ->order_by('t.id', 'DESC')
            ->limit(1)
            ->get(self::TABELA . ' t')
            ->row();
    }

    /**
     * Gera um token novo para a empresa e revoga o anterior. Devolve o token completo (só existe aqui)
     * ou false se não for possível gerar bytes aleatórios seguros.
     */
    public function gerar($empresa_id, $user_id)
    {
        $forte = false;
        $bytes = function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes(24, $forte) : false;
        if ($bytes === false || !$forte) {
            return false;
        }
        $token = self::PREFIXO . bin2hex($bytes);
        $agora = date('Y-m-d H:i:s');

        $this->db->trans_start();
        $this->revogar($empresa_id);
        $this->db->insert(self::TABELA, array(
            'empresa_id' => (int) $empresa_id,
            'token_hash' => hash('sha256', $token),
            'prefixo' => substr($token, 0, 9),
            'criado_por' => (int) $user_id ?: null,
            'created_at' => $agora,
        ));
        $this->db->trans_complete();

        return $this->db->trans_status() ? $token : false;
    }

    public function revogar($empresa_id)
    {
        $this->db->where('empresa_id', (int) $empresa_id)
            ->where('revogado_em IS NULL', null, false)
            ->update(self::TABELA, array('revogado_em' => date('Y-m-d H:i:s')));
        return $this->db->affected_rows();
    }

    // Empresa dona do token enviado (0 se o token não existe ou foi revogado). Registra o último uso.
    public function empresa_do_token($token)
    {
        $token = trim((string) $token);
        if ($token === '' || strpos($token, self::PREFIXO) !== 0 || !$this->disponivel()) {
            return 0;
        }
        $row = $this->db->select('id, empresa_id')
            ->where('token_hash', hash('sha256', $token))
            ->where('revogado_em IS NULL', null, false)
            ->limit(1)
            ->get(self::TABELA)
            ->row();
        if (!$row) {
            return 0;
        }
        $this->db->where('id', (int) $row->id)->update(self::TABELA, array('ultimo_uso_em' => date('Y-m-d H:i:s')));
        return (int) $row->empresa_id;
    }
}
