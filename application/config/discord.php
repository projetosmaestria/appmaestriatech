<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Token do bot do Discord usado para enviar mensagens diretas aos colaboradores.
| Defina a variável de ambiente DISCORD_BOT_TOKEN; o token nunca deve ser versionado.
*/
$config['discord_bot_token'] = getenv('DISCORD_BOT_TOKEN') ?: '';
