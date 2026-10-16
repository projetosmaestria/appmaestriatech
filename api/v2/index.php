<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SAXweb - API </title>
    </head>
    <body>
        <p>
            <h3>/*MÉTODOS EXISTENTES NA API*/</h3>
        </p>
        <p>
            <b>URL:</b> api/v2/login<br>
            <b>DESCRIÇÃO:</b> método para executar o login do usuário<br>
            <b>PARÂMETROS:</b> email,senha e device_id<br>
        </p>
        <p>
            <b>URL:</b> api/v2/logoff<br>
            <b>DESCRIÇÃO:</b> método para executar o logoff do usuário<br>
            <b>PARÂMETROS:</b> device_id e token<br>
        </p>
        <p>
            <b>URL:</b> api/v2/authenticate<br>
            <b>DESCRIÇÃO:</b> autenticação do usuário<br>
            <b>PARÂMETROS:</b> token e device_id<br>
        </p>
        <p>
            <b>URL:</b> api/v2/alterarSenha<br>
            <b>DESCRIÇÃO:</b> alteração de senha para o usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> token, device_id, senha_atual, nova_senha<br>
        </p>
        <p>
            <b>URL:</b> api/v2/recuperarSenha<br>
            <b>DESCRIÇÃO:</b> enviar email confirmando nova senha para o usuário<br>
            <b>PARÂMETROS:</b> email<br>
        </p>
        <!--<p>
            <b>URL:</b> api/v2/cadastrarUsuario<br>
            <b>DESCRIÇÃO:</b> cadastro do usuário pelo aplicativo<br>
            <b>PARÂMETROS:</b> nome,email,senha e device_id<br>
        </p>-->
        <p>
            <b>URL:</b> api/v2/getUsuario<br>
            <b>DESCRIÇÃO:</b> irá carregar informações do usuário autenticado e logado<br>
            <b>PARÂMETROS:</b> token e device_id<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getEventos<br>
            <b>DESCRIÇÃO:</b> irá carregar a lista de eventos existentes para o usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> token, device_id, limit(quantidade de ítens retornados para a lista) e next_id(irá retornar os ids dos eventos carregados em base64, quando houver e for diferente de NULL)<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getPodcasts<br>
            <b>DESCRIÇÃO:</b> irá carregar a lista de podcasts existentes para o usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> token, device_id, limit(quantidade de ítens retornados para a lista), data(NÃO É OBRIGATÓRIO) e next_id(irá retornar os ids dos eventos carregados em base64, quando houver e for diferente de NULL)<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getLivros<br>
            <b>DESCRIÇÃO:</b> irá carregar a lista de livros existentes para o usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> empresa (2 para CEBRAC e 3 para BSL), token e device_id<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getUnidadesLivro<br>
            <b>DESCRIÇÃO:</b> irá carregar a lista de unidades do livro existentes para o usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> livro, token e device_id<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getGrammarFile<br>
            <b>DESCRIÇÃO:</b> irá carregar o texto do grammar file da unidade<br>
            <b>PARÂMETROS:</b> unidade, token e device_id<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getUnidadesAtualizadas<br>
            <b>DESCRIÇÃO:</b> resgate de unidades que possuem atualizações, contendo todas as informações<br>
            <b>PARÂMETROS:</b> token, device_id, data(NÃO É OBRIGATÓRIO), livro(NÃO É OBRIGATÓRIO)<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getExercicios<br>
            <b>DESCRIÇÃO:</b> irá carregar a lista de exercícios e suas respectivas perguntas existentes para o usuário logado e autenticado.<br>
            Ao utilizar o campo unidade ele irá carregar somente os exercícios da unidade.<br>
            Ao utilizar o campo data ele irá retornar exercícios ou peguntas que possuírem data de atualização acima ou igual a mesma.<br>
            <b>PARÂMETROS:</b> unidade(NÃO É OBRIGATÓRIO), data(NÃO É OBRIGATÓRIO), down(NÃO É OBRIGATÓRIO) => 0/1, limit(NÃO É OBRIGATÓRIO), next_id(irá retornar os ids dos exercícios carregados em base64, quando houver e for diferente de NULL(NÃO É OBRIGATÓRIO)), id_exercicio(NÃO É OBRIGATÓRIO, id do último exercício recuperado a pesquisa será feita a partir dele), token e device_id<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getMidiasExercicio<br>
            <b>DESCRIÇÃO:</b> irá carregar a lista de exercícios que contenham mídia(áudio) para o usuário logado e autenticado.<br>
            Ao utilizar o campo exercicio ele irá carregar somente o exercício em questão.<br>
            Ao utilizar o campo unidade ele irá carregar somente os exercícios da unidade.<br>
            Ao utilizar o campo data ele irá retornar exercícios ou peguntas que possuírem data de atualização acima ou igual a mesma.<br>
            <b>PARÂMETROS:</b> exercicio(NÃO É OBRIGATÓRIO), unidade(NÃO É OBRIGATÓRIO), down(NÃO É OBRIGATÓRIO) => 0/1, data(NÃO É OBRIGATÓRIO), limit(quantidade de ítens retornados para a lista), next_id(irá retornar os ids dos eventos carregados em base64, quando houver e for diferente de NULL), token e device_id<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getAudiosUnidade<br>
            <b>DESCRIÇÃO:</b> irá carregar a lista de áudios existentes para a unidade para o usuário logado e autenticado.<br>
            Ao utilizar o campo unidade ele irá carregar somente os áudios da unidade.<br>
            Ao utilizar o campo data ele irá retornar apenas áudios que possuírem data de atualização acima ou igual a mesma.<br>
            <b>PARÂMETROS:</b> unidade(NÃO É OBRIGATÓRIO), data(NÃO É OBRIGATÓRIO), limit(quantidade de ítens retornados para a lista), next_id(irá retornar os ids dos eventos carregados em base64, quando houver e for diferente de NULL), token e device_id<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getBanners<br>
            <b>DESCRIÇÃO:</b> irá carregar a lista de banners existentes para o usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> token, device_id, unidade(não obrigatório), width(largura na qual o banner deve ser criado)<br>
        </p>
        <p>
            <b>URL:</b> api/v2/sendRespostas<br>
            <b>DESCRIÇÃO:</b> Método para envio de respostas do app<br>
            <b>PARÂMETROS:</b> token, device_id, array contendo as respostas no formato do exemplo('id_pergunta' => 1, 'resposta' => 'Resposta_Teste', 'audio' => 'N','data_resposta' => '9999-99-99 00:00:00', unidade => 1)<br>
            Quando houver resposta contendo áudio, o campo resposta está sendo enviado com o áudio em base64 e o campo audio recebe 'S'. No momento a extensão do arquivo enviada é .caf.
        </p>
        <p>
            <b>URL:</b> api/v2/getRespostas<br>
            <b>DESCRIÇÃO:</b> irá resgatar as respostas enviadas pelo alunco contendo ou não observações do professor para o usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> token, device_id, data(NÃO É OBRIGATÓRIO)<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getRecentActivities<br>
            <b>DESCRIÇÃO:</b> irá resgatar as atividades recentes e eventos que acontecerão nos próximos 30 dias para o usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> token, device_id, limit(quantidade de ítens retornados para a lista), next_id(irá retornar os ids dos exercícios carregados em base64, quando houver e for diferente de NULL) e eventos(0 ou 1) para resgate de eventos<br>
        </p>
        <p>
            <b>URL:</b> api/v2/setPushToken<br>
            <b>DESCRIÇÃO:</b> enviar token para notificações para o usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> token, device_id, device_token<br>
        </p>
        <p>
            <b>URL:</b> api/v2/setQRCode<br>
            <b>DESCRIÇÃO:</b> enviar qrcode para registro e relacionamento de aluno a livro para o usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> token, device_id, qrcode<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getQRCode<br>
            <b>DESCRIÇÃO:</b> resgate de qrcode existente para usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> token, device_id<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getPercents<br>
            <b>DESCRIÇÃO:</b> resgate de percentuais de exercícios respondidos<br>
            <b>PARÂMETROS:</b> token, device_id<br>
        </p>
        <p>
            <b>URL:</b> api/v2/getNotas<br>
            <b>DESCRIÇÃO:</b> irá carregar a lista de notas existentes para o usuário logado e autenticado<br>
            <b>PARÂMETROS:</b> token, device_id e empresa<br>
        </p>
        <hr>
        <p>
            <h3>/*ERROS POSSÍVEIS*/</h3>
        </p>
        <p>
            <b>CÓDIGO:</b> 1<br>
            <b>DESCRIÇÃO:</b> Campo está em branco.
        </p>
        <p>
            <b>CÓDIGO:</b> 2<br>
            <b>DESCRIÇÃO:</b> Email não é válido.
        </p>
        <p>
            <b>CÓDIGO:</b> 3<br>
            <b>DESCRIÇÃO:</b> Query não foi executada com sucesso.
        </p>
        <p>
            <b>CÓDIGO:</b> 4<br>
            <b>DESCRIÇÃO:</b> Token expirou, execute o login.
        </p>
        <p>
            <b>CÓDIGO:</b> 5<br>
            <b>DESCRIÇÃO:</b> Usuário ou senha não estão corretos.
        </p>
        <p>
            <b>CÓDIGO:</b> 6<br>
            <b>DESCRIÇÃO:</b> Nenhum parâmetro foi recebido no post.
        </p>
        <p>
            <b>CÓDIGO:</b> 7<br>
            <b>DESCRIÇÃO:</b> Autenticação falhou, o token não foi encontrado em registros.
        </p>
        <p>
            <b>CÓDIGO:</b> 8<br>
            <b>DESCRIÇÃO:</b> O email já existe em registro encontrado no banco.
        </p>
        <p>
            <b>CÓDIGO:</b> 9<br>
            <b>DESCRIÇÃO:</b> Não foi encontrado registro no banco.
        </p>
        <p>
            <b>CÓDIGO:</b> 17<br>
            <b>DESCRIÇÃO:</b> Senha atual não confere!
        </p>
        <p>
            <b>CÓDIGO:</b> 19<br>
            <b>DESCRIÇÃO:</b> Erro ao tentar enviar email com a nova senha, tente novamente!
        </p>
        <p>
            <b>CÓDIGO:</b> 23<br>
            <b>DESCRIÇÃO:</b> QRCode já foi registrado no sistema!
        </p>
        <p>
            <b>CÓDIGO:</b> 24<br>
            <b>DESCRIÇÃO:</b> Usuário não possui acesso a este livro!
        </p>
        <p>
            <b>CÓDIGO:</b> 25<br>
            <b>DESCRIÇÃO:</b> Aluno já possui qrcode registrado para o livro!
        </p>
        <p>
            <b>CÓDIGO:</b> 26<br>
            <b>DESCRIÇÃO:</b> QRCode inválido/não encontrado!
        </p>
        <p>
            <b>CÓDIGO:</b> 27<br>
            <b>DESCRIÇÃO:</b> Unidade(s) para respostas não foram enviadas!
        </p>
        <p>
            <b>CÓDIGO:</b> 28<br>
            <b>DESCRIÇÃO:</b> Respostas enviadas já foram corrigidas, não é possível atualizá-las!
        </p>
        <p>
            <b>CÓDIGO:</b> 30<br>
            <b>DESCRIÇÃO:</b> Usuário não possui acesso ao aplicativo!
        </p>
        <p>
            <b>CÓDIGO:</b> 31<br>
            <b>DESCRIÇÃO:</b> Aluno já possui 2 aparelhos cadastrados!
        </p>
        <p>
            <b>CÓDIGO:</b> 32<br>
            <b>DESCRIÇÃO:</b> Quantidade de respostas enviadas menores do que perguntas existentes!
        </p>
        <p>
            <b>CÓDIGO:</b> 33<br>
            <b>DESCRIÇÃO:</b> Token não registrado nesta unidade!
        </p>
        <p>
            <b>CÓDIGO:</b> 34<br>
            <b>DESCRIÇÃO:</b> Usuário já existente no Saxweb!
        </p>
        <p>
            <h3>/*SUCESSOS*/</h3>
        </p>
        <p>
            <b>CÓDIGO:</b> 10<br>
            <b>DESCRIÇÃO:</b> Registro executado com sucesso.
        </p>
        <p>
            <b>CÓDIGO:</b> 11<br>
            <b>DESCRIÇÃO:</b> Resgate de informações executado com sucesso.
        </p>
        <p>
            <b>CÓDIGO:</b> 12<br>
            <b>DESCRIÇÃO:</b> Usuário autenticado com sucesso.
        </p>
        <p>
            <b>CÓDIGO:</b> 13<br>
            <b>DESCRIÇÃO:</b> Tempo de expiração atualizado para o token.
        </p>
        <p>
            <b>CÓDIGO:</b> 14<br>
            <b>DESCRIÇÃO:</b> Login executado com sucesso.
        </p>
        <p>
            <b>CÓDIGO:</b> 15<br>
            <b>DESCRIÇÃO:</b> Logoff executado com sucesso.
        </p>
        <p>
            <b>CÓDIGO:</b> 16<br>
            <b>DESCRIÇÃO:</b> Logoff falhou.
        </p>
        <p>
            <b>CÓDIGO:</b> 18<br>
            <b>DESCRIÇÃO:</b> Senha alterada com sucesso.
        </p>
        <p>
            <b>CÓDIGO:</b> 20<br>
            <b>DESCRIÇÃO:</b> Nova senha gerada com sucesso, verique sua caixa de mensagens!
        </p>
        <p>
            <b>CÓDIGO:</b> 21<br>
            <b>DESCRIÇÃO:</b> QRCode registrado com sucesso!
        </p>
        <p>
            <b>CÓDIGO:</b> 22<br>
            <b>DESCRIÇÃO:</b> Usuário registrado, mas não possui código para acesso ao livro!
        </p>
        <p>
            <b>CÓDIGO:</b> 29<br>
            <b>DESCRIÇÃO:</b> Encontrado registro de qrcode para o aluno!
        </p>
        <p>
            <b>CÓDIGO:</b> 30<br>
            <b>DESCRIÇÃO:</b> Token da unidade encontrado e retornado!
        </p>
    </body>
</html>