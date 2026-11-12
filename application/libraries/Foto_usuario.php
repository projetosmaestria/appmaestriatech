<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Foto de perfil do usuário.
 *
 * Os arquivos ficam em upload/users/ (no próprio servidor/FTP) e a tabela users guarda só o nome do arquivo
 * na coluna foto (sql/2026-11-12_users_foto.sql). Para cada foto é gerada uma miniatura quadrada
 * (thumb_<nome>.jpg), usada no cabeçalho, no menu lateral e no perfil.
 *
 * A foto não pode passar de 5 MB: acima disso ela é convertida para JPEG e compactada (qualidade e,
 * se preciso, dimensões) até ficar dentro do limite. Fotos menores são guardadas como vieram.
 */
class Foto_usuario {

    const LIMITE_BYTES = 5242880;
    const PASTA = 'upload/users/';
    const LADO_MINIATURA = 256;
    // Acima disso o GD precisaria de memória demais para abrir a imagem.
    const MAX_PIXELS = 50000000;

    protected $erro = '';

    // Configuráveis no load->library(): as novidades usam a mesma lógica em outra pasta,
    // com uma versão reduzida retangular (sem recorte) em vez da miniatura quadrada.
    protected $pasta = self::PASTA;
    protected $lado_miniatura = self::LADO_MINIATURA;
    protected $miniatura_quadrada = true;

    public function __construct($params = array())
    {
        if (!empty($params['pasta'])) {
            $this->pasta = rtrim($params['pasta'], '/') . '/';
        }
        if (!empty($params['lado_miniatura'])) {
            $this->lado_miniatura = (int) $params['lado_miniatura'];
        }
        if (isset($params['miniatura_quadrada'])) {
            $this->miniatura_quadrada = (bool) $params['miniatura_quadrada'];
        }
    }

    public function erro()
    {
        return $this->erro;
    }

    /**
     * URL da foto (miniatura por padrão) ou null quando o usuário não tem foto.
     */
    public function url($foto, $miniatura = true)
    {
        if (!$this->nome_valido($foto)) {
            return null;
        }
        if ($miniatura) {
            $thumb = $this->nome_miniatura($foto);
            if (is_file(FCPATH . $this->pasta . $thumb)) {
                return base_url($this->pasta . $thumb);
            }
        }
        return is_file(FCPATH . $this->pasta . $foto) ? base_url($this->pasta . $foto) : null;
    }

    /**
     * Valida e grava a foto enviada ($_FILES[campo]). Retorna o nome do arquivo gravado ou false (ver erro()).
     */
    public function salvar($arquivo, $user_id, $prefixo = 'u')
    {
        $this->erro = '';

        if (empty($arquivo) || !isset($arquivo['error']) || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
            $this->erro = 'Selecione uma foto.';
            return false;
        }
        if ($arquivo['error'] === UPLOAD_ERR_INI_SIZE || $arquivo['error'] === UPLOAD_ERR_FORM_SIZE) {
            $this->erro = 'A foto passa do limite de envio do servidor (' . ini_get('upload_max_filesize') . '). Escolha uma imagem menor.';
            return false;
        }
        if ($arquivo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'])) {
            $this->erro = 'Não foi possível receber a foto. Tente novamente.';
            return false;
        }

        $info = @getimagesize($arquivo['tmp_name']);
        $tipos = array(IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif');
        if (defined('IMAGETYPE_WEBP') && function_exists('imagecreatefromwebp')) {
            $tipos[IMAGETYPE_WEBP] = 'webp';
        }
        if (!$info || !isset($tipos[$info[2]])) {
            $this->erro = 'O arquivo não é uma imagem aceita. Envie ' . strtoupper(implode(', ', array_unique($tipos))) . '.';
            return false;
        }
        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            $this->erro = 'A imagem tem resolução muito alta (' . $info[0] . 'x' . $info[1] . '). Envie uma foto menor.';
            return false;
        }

        $pasta = FCPATH . $this->pasta;
        if (!is_dir($pasta) && !@mkdir($pasta, 0755, true)) {
            $this->erro = 'Não foi possível criar a pasta das fotos no servidor.';
            return false;
        }
        if (!is_file($pasta . 'index.html')) {
            @file_put_contents($pasta . 'index.html', '<html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>');
        }

        $base = preg_replace('/[^a-z]/', '', strtolower($prefixo)) . (int) $user_id . '_' . date('YmdHis') . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
        $tamanho = (int) filesize($arquivo['tmp_name']);

        if ($tamanho > self::LIMITE_BYTES) {
            if (!$this->gd_disponivel()) {
                $this->erro = 'A foto tem mais de 5 MB e o servidor não consegue compactá-la. Envie uma imagem de até 5 MB.';
                return false;
            }
            $nome = $base . '.jpg';
            if (!$this->compactar($arquivo['tmp_name'], $info, $pasta . $nome)) {
                return false;
            }
        } else {
            $nome = $base . '.' . $tipos[$info[2]];
            if (!move_uploaded_file($arquivo['tmp_name'], $pasta . $nome)) {
                $this->erro = 'Não foi possível gravar a foto no servidor.';
                return false;
            }
        }
        @chmod($pasta . $nome, 0644);

        // Sem miniatura a foto continua funcionando: url() usa o arquivo original.
        if ($this->gd_disponivel()) {
            $this->gerar_miniatura($pasta . $nome, $pasta . $this->nome_miniatura($nome));
        }

        return $nome;
    }

    /**
     * Apaga a foto e a miniatura.
     */
    public function remover($foto)
    {
        if (!$this->nome_valido($foto)) {
            return;
        }
        @unlink(FCPATH . $this->pasta . $foto);
        @unlink(FCPATH . $this->pasta . $this->nome_miniatura($foto));
    }

    protected function nome_valido($foto)
    {
        return is_string($foto) && $foto !== '' && preg_match('/^[A-Za-z0-9_-]+\.(jpg|jpeg|png|gif|webp)$/', $foto);
    }

    protected function nome_miniatura($foto)
    {
        return 'thumb_' . pathinfo($foto, PATHINFO_FILENAME) . '.jpg';
    }

    protected function gd_disponivel()
    {
        return function_exists('imagecreatetruecolor') && function_exists('imagejpeg');
    }

    protected function abrir($caminho, $tipo)
    {
        switch ($tipo) {
            case IMAGETYPE_JPEG:
                $img = @imagecreatefromjpeg($caminho);
                break;
            case IMAGETYPE_PNG:
                $img = @imagecreatefrompng($caminho);
                break;
            case IMAGETYPE_GIF:
                $img = @imagecreatefromgif($caminho);
                break;
            default:
                $img = (defined('IMAGETYPE_WEBP') && $tipo === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) ? @imagecreatefromwebp($caminho) : false;
        }
        if ($img && $tipo === IMAGETYPE_JPEG) {
            $img = $this->corrigir_orientacao($img, $caminho);
        }
        return $img;
    }

    /**
     * Converte para JPEG reduzindo a qualidade e, se não bastar, as dimensões, até caber em 5 MB.
     */
    protected function compactar($origem, $info, $destino)
    {
        @ini_set('memory_limit', '512M');
        $img = $this->abrir($origem, $info[2]);
        if (!$img) {
            $this->erro = 'Não foi possível abrir a imagem para compactar.';
            return false;
        }

        $largura = imagesx($img);
        $altura = imagesy($img);
        // Uma foto de perfil não precisa de mais que 4000px no maior lado.
        $escala = min(1, 4000 / max($largura, $altura));

        for ($tentativa = 0; $tentativa < 8; $tentativa++) {
            $nova_largura = max(1, (int) round($largura * $escala));
            $nova_altura = max(1, (int) round($altura * $escala));
            $tela = $this->tela_branca($nova_largura, $nova_altura);
            imagecopyresampled($tela, $img, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura, $altura);

            foreach (array(85, 75, 65) as $qualidade) {
                ob_start();
                imagejpeg($tela, null, $qualidade);
                $bytes = ob_get_clean();
                if (strlen($bytes) <= self::LIMITE_BYTES) {
                    imagedestroy($tela);
                    imagedestroy($img);
                    if (file_put_contents($destino, $bytes) === false) {
                        $this->erro = 'Não foi possível gravar a foto no servidor.';
                        return false;
                    }
                    return true;
                }
            }
            imagedestroy($tela);
            $escala *= 0.75;
        }

        imagedestroy($img);
        $this->erro = 'Não foi possível compactar a foto para 5 MB. Envie uma imagem menor.';
        return false;
    }

    /**
     * Miniatura em JPEG: quadrada com recorte central (foto de perfil) ou reduzida
     * mantendo a proporção, com o maior lado em $lado_miniatura (imagens das novidades).
     */
    protected function gerar_miniatura($origem, $destino)
    {
        $info = @getimagesize($origem);
        if (!$info) {
            return false;
        }
        @ini_set('memory_limit', '512M');
        $img = $this->abrir($origem, $info[2]);
        if (!$img) {
            return false;
        }

        $largura = imagesx($img);
        $altura = imagesy($img);
        if ($this->miniatura_quadrada) {
            $lado = min($largura, $altura);
            $x = (int) floor(($largura - $lado) / 2);
            $y = (int) floor(($altura - $lado) / 2);
            $origem_largura = $origem_altura = $lado;
            $destino_largura = $destino_altura = min($this->lado_miniatura, $lado);
        } else {
            $x = $y = 0;
            $origem_largura = $largura;
            $origem_altura = $altura;
            $escala = min(1, $this->lado_miniatura / max($largura, $altura));
            $destino_largura = max(1, (int) round($largura * $escala));
            $destino_altura = max(1, (int) round($altura * $escala));
        }

        $tela = $this->tela_branca($destino_largura, $destino_altura);
        imagecopyresampled($tela, $img, 0, 0, $x, $y, $destino_largura, $destino_altura, $origem_largura, $origem_altura);
        $ok = imagejpeg($tela, $destino, 85);
        imagedestroy($tela);
        imagedestroy($img);
        if ($ok) {
            @chmod($destino, 0644);
        }
        return $ok;
    }

    // Fundo branco: PNG/GIF com transparência viram JPEG sem ficar com fundo preto.
    protected function tela_branca($largura, $altura)
    {
        $tela = imagecreatetruecolor($largura, $altura);
        imagefill($tela, 0, 0, imagecolorallocate($tela, 255, 255, 255));
        return $tela;
    }

    /**
     * Fotos de celular vêm "deitadas" com a rotação só na marcação EXIF. O GD ignora essa marcação,
     * então a imagem é girada antes de gerar a miniatura ou a versão compactada.
     */
    protected function corrigir_orientacao($img, $caminho)
    {
        $orientacao = $this->orientacao_jpeg($caminho);
        $angulos = array(3 => 180, 6 => -90, 8 => 90);
        if (!isset($angulos[$orientacao])) {
            return $img;
        }
        $girada = imagerotate($img, $angulos[$orientacao], 0);
        if ($girada) {
            imagedestroy($img);
            return $girada;
        }
        return $img;
    }

    /**
     * Lê a orientação EXIF direto do JPEG (a extensão exif nem sempre está instalada).
     */
    protected function orientacao_jpeg($caminho)
    {
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($caminho);
            return isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;
        }

        $dados = @file_get_contents($caminho, false, null, 0, 131072);
        if ($dados === false || substr($dados, 0, 2) !== "\xFF\xD8") {
            return 1;
        }
        $pos = 2;
        $total = strlen($dados);
        while ($pos + 4 <= $total) {
            if ($dados[$pos] !== "\xFF") {
                return 1;
            }
            $marcador = ord($dados[$pos + 1]);
            $tamanho = unpack('n', substr($dados, $pos + 2, 2));
            $tamanho = $tamanho[1];
            if ($marcador === 0xE1 && substr($dados, $pos + 4, 6) === "Exif\0\0") {
                $tiff = $pos + 10;
                $little = substr($dados, $tiff, 2) === 'II';
                $ler16 = function ($offset) use ($dados, $little) {
                    $v = unpack($little ? 'v' : 'n', substr($dados, $offset, 2));
                    return $v[1];
                };
                $ler32 = function ($offset) use ($dados, $little) {
                    $v = unpack($little ? 'V' : 'N', substr($dados, $offset, 4));
                    return $v[1];
                };
                $ifd = $tiff + $ler32($tiff + 4);
                if ($ifd + 2 > $total) {
                    return 1;
                }
                $entradas = $ler16($ifd);
                for ($i = 0; $i < $entradas; $i++) {
                    $entrada = $ifd + 2 + $i * 12;
                    if ($entrada + 12 > $total) {
                        break;
                    }
                    if ($ler16($entrada) === 0x0112) {
                        return (int) $ler16($entrada + 8);
                    }
                }
                return 1;
            }
            // SOS: dali em diante é a imagem, sem mais metadados.
            if ($marcador === 0xDA) {
                return 1;
            }
            $pos += 2 + $tamanho;
        }
        return 1;
    }
}
