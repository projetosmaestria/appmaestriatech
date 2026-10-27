<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Aviso ao solicitante pelo WhatsApp oficial (FLW Chat) na abertura e no encerramento do chamado.
| Todos os valores vêm de variáveis de ambiente; o token nunca deve ser versionado.
| Sem FLWCHAT_TOKEN, FLWCHAT_CANAL ou o id do modelo, o envio correspondente fica desligado.
*/
$config['flwchat_url'] = getenv('FLWCHAT_URL') ?: 'https://api.painel.xkeysti.com.br/chat';
$config['flwchat_token'] = getenv('FLWCHAT_TOKEN') ?: '';
// Número do canal oficial que envia as mensagens, só dígitos com DDI (ex.: 554431234652).
$config['flwchat_canal'] = getenv('FLWCHAT_CANAL') ?: '';
$config['flwchat_modelo_abertura'] = getenv('FLWCHAT_MODELO_ABERTURA') ?: '';
$config['flwchat_modelo_encerramento'] = getenv('FLWCHAT_MODELO_ENCERRAMENTO') ?: '';
// Os modelos levam a marca da XKEY: só os chamados desta empresa recebem o aviso.
$config['flwchat_empresa_id'] = (int) (getenv('FLWCHAT_EMPRESA_ID') ?: 1);
