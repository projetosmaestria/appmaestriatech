<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI     =& get_instance();
$original = $CI->output->get_output();

// Scripts, <pre> e <textarea> ficam intactos: juntar as linhas faria um comentário "//"
// anular o restante do script e apagaria as quebras de linha dos textos editáveis.
$preserved = array();
$buffer = preg_replace_callback('#<(script|pre|textarea)\b[^>]*>.*?</\1>#is', function ($match) use (&$preserved) {
    $key = '<!--compress-preserve-' . count($preserved) . '-->';
    $preserved[$key] = $match[0];
    return $key;
}, $original);

$search = array(
    '/\>[^\S ]+/s',
    '/[^\S ]+\</s',
    '/(\s)+/s',
    '/\>(\s)+\</'
);

$replace = array(
    '>',
    '<',
    '\\1',
    '><'
);

if ($buffer !== null) {
    $buffer = preg_replace($search, $replace, $buffer);
}

// Se alguma expressão falhar (ex.: limite do PCRE em páginas muito grandes), envia sem compactar.
$CI->output->set_output($buffer !== null ? strtr($buffer, $preserved) : $original);
$CI->output->_display();
