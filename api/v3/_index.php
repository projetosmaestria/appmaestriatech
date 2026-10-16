<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SAXweb - API</title>
    </head>
    <body>
        <p>
            <h3>/*MÉTODOS EXISTENTES NA API*/</h3>
        </p>
        <hr>
        <!-- <p>
            <b>URL:</b> api/v3/getUnidadeToken<br>
            <b>DESCRIÇÃO:</b> resgate de token existente da unidade informada<br>
            <b>PARÂMETROS:</b> cnpj<br>
        </p> -->
        <p>
            <b>URL:</b> api/v3/getRecursosWs<br>
            <b>DESCRIÇÃO:</b> retorna os recursos webservice estipulados no SAXweb para a unidade enviada por parâmetro.<br>
            <b>PARÂMETROS:</b> token (string)<br>
            <b>RETORNO:</b> dados dos recursos<br>
        </p>
        <p>
            <b>URL:</b> api/v3/gravarUsuario<br>
            <b>DESCRIÇÃO:</b> cria e/ou altera usuário no SAXweb, é necessário que o grupo de Aluno esteja parametrizado no SAXweb a empresa da filial informada. O método vincula o usuário à empresa, filial e grupo do aluno após o cadastro/edição nas tabelas de usuário e pessoa. Não é permitido criação e alteração de e-mails que já estiverem cadastrados no SAXweb.<br>
            <b>PARÂMETROS:</b> token (string), tabela (enum('cad_clie', 'cad_vend', 'cad_forn')), email (string), user_id (int OBRIGATÓRIO APENAS PARA ALTERAÇÃO), data_nascimento (date NÃO É OBRIGATÓRIO format: Y-m-d), telefone (string NÃO É OBRIGATÓRIO), celular (string NÃO É OBRIGATÓRIO), cep (string NÃO É OBRIGATÓRIO), endereco (string NÃO É OBRIGATÓRIO), numero (string NÃO É OBRIGATÓRIO), bairro (string NÃO É OBRIGATÓRIO), cidade (string NÃO É OBRIGATÓRIO), uf (string NÃO É OBRIGATÓRIO)<br>
            <b>RETORNO:</b> dados do usuário<br>
        </p>
        <p>
            <b>URL:</b> api/v3/vincularUsuario<br>
            <b>DESCRIÇÃO:</b> vincula usuário com um e-mail já utilizado no SAXweb. O usário é inserido na empresa e filial informada e no grupo de aluno parametrizado no SAXweb referente à empresa. Importante se atentar a não utilizar esse método em usuários da mesma filial já vinculados pelo SAX, uma dica é utilizar o campo `usuario_saxweb` no banco do SAX para controlar isso.<br>
            <b>PARÂMETROS:</b> token (string), tabela (enum('cad_clie', 'cad_vend', 'cad_forn')), email (string), data_nascimento (date NÃO É OBRIGATÓRIO format: Y-m-d), telefone (string NÃO É OBRIGATÓRIO), celular (string NÃO É OBRIGATÓRIO), cep (string NÃO É OBRIGATÓRIO), endereco (string NÃO É OBRIGATÓRIO), numero (string NÃO É OBRIGATÓRIO), bairro (string NÃO É OBRIGATÓRIO), cidade (string NÃO É OBRIGATÓRIO), uf (string NÃO É OBRIGATÓRIO)<br>
            <b>RETORNO:</b> dados do usuário<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getProfessor<br>
            <b>DESCRIÇÃO:</b> retornam os professores no SAXweb da unidade enviada por parâmetro.<br>
            <b>PARÂMETROS:</b> token (string), id (int NÃO É OBRIGATÓRIO)<br>
            <b>RETORNO:</b> dados dos professores<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getLivro<br>
            <b>DESCRIÇÃO:</b> retornam os livros recebidos no SAXweb da unidade enviada por parâmetro.<br>
            <b>PARÂMETROS:</b> token (string), id (int NÃO É OBRIGATÓRIO)<br>
            <b>RETORNO:</b> dados dos livros<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getTurma<br>
            <b>DESCRIÇÃO:</b> retornam as turmas recebidos no SAXweb da unidade enviada por parâmetro.<br>
            <b>PARÂMETROS:</b> token (string), id (int NÃO É OBRIGATÓRIO)<br>
            <b>RETORNO:</b> dados das turmas<br>
        </p>
        <p>
            <b>URL:</b> api/v3/gravarTurma<br>
            <b>DESCRIÇÃO:</b> cria e/ou altera turma no SAXweb da unidade enviada por parâmetro. É possível cadastrar horários para a turma neste mesmo método, no caso de alteração de horário, é necessário o envio da lista de todos horários cadastrados.<br>
            <b>PARÂMETROS:</b> token (string), id (int OBRIGATÓRIO APENAS PARA ALTERAÇÃO), livro (int), descricao string, capacidade_aluno int, data_inicio (Y-m-d), data_termino (Y-m-d), obs string, horario array(professor int, dia_semana string, hora_inicial time, hora_final time)<br>
            <b>RETORNO:</b> dados da turma<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getContato<br>
            <b>DESCRIÇÃO:</b> retornam os contatos recebidos no SAXweb para a unidade enviada por parâmetro.<br>
            <b>PARÂMETROS:</b> token (string), id (int NÃO É OBRIGATÓRIO)<br>
            <b>RETORNO:</b> dados dos contatos<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getDisciplina<br>
            <b>DESCRIÇÃO:</b> retornam as disciplinas recebidos no SAXweb para a unidade enviada por parâmetro.<br>
            <b>PARÂMETROS:</b> token (string), id (int NÃO É OBRIGATÓRIO)<br>
            <b>RETORNO:</b> dados das disciplinas<br>
        </p>
        <p>
            <b>URL:</b> api/v3/gravarPauta<br>
            <b>DESCRIÇÃO:</b> cria e/ou altera pauta no SAXweb da unidade enviada por parâmetro.<br>
            <b>PARÂMETROS:</b> token (string), id (int OBRIGATÓRIO APENAS PARA ALTERAÇÃO), turma (int), professor (int), disciplina (int), situacao_pauta enum('True', 'False'), situacao_disciplina enum('True', 'False'), justificativa string, obs string, aula_data (Y-m-d), aula_tempo (H:i:s), frequente int, evasao int, aula_hora (H:i:s)<br>
            <b>RETORNO:</b> dados da pauta<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getPedidoPrecode<br>
            <b>DESCRIÇÃO:</b> retorna pedidos da plataforma PRECODE.<br>
            <b>PARÂMETROS:</b> conteudo (obj), autorizacao (string), password (string)<br>
            <b>RETORNO:</b> pedidos<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getPedidoStatusPrecode<br>
            <b>DESCRIÇÃO:</b> retorna status do pedido da plataforma PRECODE.<br>
            <b>PARÂMETROS:</b> conteudo (obj), autorizacao (string), password (string)<br>
            <b>RETORNO:</b> status pedido<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getPedidoNovoPrecode<br>
            <b>DESCRIÇÃO:</b> retorna novos pedido da plataforma PRECODE.<br>
            <b>PARÂMETROS:</b> autorizacao (string), password (string)<br>
            <b>RETORNO:</b> pedidos<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getPedidoAprovadoPrecode<br>
            <b>DESCRIÇÃO:</b> retorna pedidos aprovados da plataforma PRECODE.<br>
            <b>PARÂMETROS:</b> autorizacao (string), password (string)<br>
            <b>RETORNO:</b> pedidos<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getProdutoPrecode<br>
            <b>DESCRIÇÃO:</b> retorna produtos da plataforma PRECODE.<br>
            <b>PARÂMETROS:</b> conteudo (obj), autorizacao (string), password (string)<br>
            <b>RETORNO:</b> produtos<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getSaldoProdutoPrecode<br>
            <b>DESCRIÇÃO:</b> retorna saldo do produto da plataforma PRECODE.<br>
            <b>PARÂMETROS:</b> conteudo (obj), autorizacao (string), password (string)<br>
            <b>RETORNO:</b> produto<br>
        </p>
        <p>
            <b>URL:</b> api/v3/getPrecoProdutoPrecode<br>
            <b>DESCRIÇÃO:</b> retorna preço do produto da plataforma PRECODE.<br>
            <b>PARÂMETROS:</b> conteudo (obj), autorizacao (string), password (string)<br>
            <b>RETORNO:</b> produto<br>
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
            <b>CÓDIGO:</b> 24<br>
            <b>DESCRIÇÃO:</b> Usuário não possui acesso a este livro!
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
            <b>CÓDIGO:</b> 18<br>
            <b>DESCRIÇÃO:</b> Senha alterada com sucesso.
        </p>
        <p>
            <b>CÓDIGO:</b> 20<br>
            <b>DESCRIÇÃO:</b> Nova senha gerada com sucesso, verique sua caixa de mensagens!
        </p>
        <p>
            <b>CÓDIGO:</b> 30<br>
            <b>DESCRIÇÃO:</b> Token da unidade encontrado e retornado!
        </p>
    </body>
</html>