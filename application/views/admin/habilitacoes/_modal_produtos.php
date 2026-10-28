<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Modal "Produtos do cliente": habilitar, alterar, desabilitar e reabilitar produtos.
// Quem inclui define window.habilAoAlterar() para atualizar a própria tela depois de cada gravação.
?>
<div id="habilModal" class="modal fade" role="dialog" aria-labelledby="habilModalTitulo">
    <div class="modal-dialog modal-lg">
        <div class="modal-content habil-modal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="habilModalTitulo">Produtos de <span id="habil-cliente-nome"></span></h4>
                <div class="habil-modal-subtitulo" id="habil-cliente-detalhe"></div>
            </div>
            <div class="modal-body">
                <div id="habil-alerta"></div>
                <div id="habil-carregando" class="text-center text-muted habil-carregando"><i class="fa fa-spinner fa-spin"></i> Carregando produtos...</div>
                <div id="habil-conteudo" style="display:none;">
                    <div class="habil-secao">
                        <h5><span class="habil-step">1</span> Produtos do cliente <small class="text-muted" id="habil-contagem"></small></h5>
                        <div id="habil-lista"></div>
                    </div>
                    <div class="habil-secao">
                        <h5><span class="habil-step">2</span> Habilitar produto</h5>
                        <div class="row">
                            <div class="col-sm-7 form-group" style="margin-bottom:8px;">
                                <select id="habil-novo-produto" class="form-control"></select>
                            </div>
                            <div class="col-sm-5">
                                <p class="help-block" id="habil-novo-info"></p>
                            </div>
                        </div>
                        <div id="habil-novo-form"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    window.addEventListener('load', function() {
        var urlBase = '<?php echo base_url('habilitacoes') ?>/';
        var clienteAtual = null;
        var disponiveis = [];
        var situacoes = {
            vigente: {texto: 'Vigente', classe: 'label-success'},
            vencendo: {texto: 'Vencendo', classe: 'label-warning'},
            vencida: {texto: 'Vencida', classe: 'label-danger'},
            desabilitada: {texto: 'Desabilitada', classe: 'label-default'}
        };
        var acessos = [
            {valor: 'L', texto: 'Liberado'},
            {valor: 'C', texto: 'Consulta'},
            {valor: 'B', texto: 'Bloqueado'}
        ];

        function el(tag, classe, texto) {
            var item = $('<' + tag + '>');
            if (classe) { item.addClass(classe); }
            if (texto !== undefined && texto !== null) { item.text(texto); }
            return item;
        }

        function alerta(tipo, mensagem) {
            var caixa = $('#habil-alerta').empty();
            if (!mensagem) { return; }
            el('div', 'alert alert-' + tipo + ' habil-alerta', mensagem).prepend(
                $('<i class="fa ' + (tipo === 'success' ? 'fa-check' : 'fa-exclamation-triangle') + '"></i> ')
            ).appendTo(caixa);
        }

        function textoSituacao(item) {
            if (item.situacao === 'vencendo') {
                return item.dias === 0 ? 'Vence hoje' : 'Vence em ' + item.dias + (item.dias === 1 ? ' dia' : ' dias');
            }
            if (item.situacao === 'vencida') {
                var dias = Math.abs(item.dias);
                return 'Vencida há ' + dias + (dias === 1 ? ' dia' : ' dias');
            }
            return situacoes[item.situacao].texto;
        }

        function resumoModulos(modulos) {
            if (!modulos.length) { return 'Sem módulos'; }
            var liberados = 0, consulta = 0, usuarios = 0;
            $.each(modulos, function(i, modulo) {
                if (modulo.status === 'L') { liberados++; }
                if (modulo.status === 'C') { consulta++; }
                if (modulo.status !== 'B') { usuarios += parseInt(modulo.usuarios, 10) || 0; }
            });
            var texto = liberados + ' de ' + modulos.length + ' módulo' + (modulos.length === 1 ? '' : 's') + ' liberado' + (liberados === 1 ? '' : 's');
            if (consulta) { texto += ', ' + consulta + ' em consulta'; }
            if (usuarios) { texto += ' · ' + usuarios + ' usuário' + (usuarios === 1 ? '' : 's'); }
            return texto;
        }

        // Formulário de habilitação (novo ou existente).
        function montarFormulario(dados) {
            var form = $('<form class="habil-form" autocomplete="off"></form>');
            form.append($('<input type="hidden" name="id_habilitacao">').val(dados.id || ''));
            form.append($('<input type="hidden" name="id_cliente">').val(clienteAtual.id));
            form.append($('<input type="hidden" name="id_produto">').val(dados.id_produto));
            form.append($('<input type="hidden" name="demonstracao">').val(dados.demonstracao ? 'S' : 'N'));

            var linha = el('div', 'row');
            var tipo = el('div', 'col-sm-12 form-group').append(el('label', '', 'Tipo'));
            var grupo = el('div', 'btn-group habil-tipo');
            $.each([{valor: 'N', texto: 'Período contratado', icone: 'fa-calendar'}, {valor: 'S', texto: 'Demonstração (30 dias)', icone: 'fa-flask'}], function(i, opcao) {
                var ativo = (opcao.valor === 'S') === !!dados.demonstracao;
                $('<button type="button" class="btn btn-default btn-sm"></button>')
                    .toggleClass('active', ativo)
                    .attr('data-valor', opcao.valor)
                    .append($('<i class="fa ' + opcao.icone + '"></i>'), document.createTextNode(' ' + opcao.texto))
                    .appendTo(grupo);
            });
            tipo.append($('<div></div>').append(grupo));
            linha.append(tipo);

            var periodo = el('div', 'col-sm-8 form-group habil-periodo');
            periodo.append(el('label', '', 'Período'));
            var datas = el('div', 'habil-datas');
            $.each([['inicio', dados.inicio, 'Início'], ['fim', dados.fim, 'Fim']], function(i, campo) {
                var caixa = el('div', 'input-group date');
                caixa.append($('<input type="text" class="form-control" placeholder="dd/mm/aaaa">').attr('name', campo[0]).attr('aria-label', campo[2]).val(campo[1] || ''));
                caixa.append('<span class="input-group-addon"><i class="fa fa-calendar"></i></span>');
                datas.append(caixa);
                if (i === 0) { datas.append(el('span', 'habil-ate', 'até')); }
            });
            periodo.append(datas);
            periodo.append(el('p', 'help-block habil-periodo-ajuda'));
            linha.append(periodo);

            var contrato = el('div', 'col-sm-4 form-group');
            contrato.append(el('label', '', 'Contrato MaestriaTech'));
            contrato.append($('<input type="text" name="contrato_sax" class="form-control" inputmode="numeric" maxlength="10" placeholder="Nº do contrato">').val(dados.contrato_sax || ''));
            linha.append(contrato);
            form.append(linha);

            if (dados.modulos.length) {
                var cabecalho = el('div', 'habil-modulos-topo');
                cabecalho.append(el('strong', '', 'Módulos'));
                var atalhos = el('span', 'habil-atalhos');
                $.each(acessos, function(i, acesso) {
                    $('<a href="#" class="js-todos"></a>').attr('data-valor', acesso.valor).text(acesso.valor === 'L' ? 'liberar todos' : (acesso.valor === 'C' ? 'todos em consulta' : 'bloquear todos')).appendTo(atalhos);
                });
                cabecalho.append(atalhos);
                form.append(cabecalho);

                var tabela = $('<table class="table table-condensed habil-modulos"><thead><tr><th>Módulo</th><th style="width:110px;">Usuários</th><th style="width:270px;">Acesso</th></tr></thead><tbody></tbody></table>');
                $.each(dados.modulos, function(i, modulo) {
                    var tr = $('<tr></tr>');
                    tr.append(el('td', '', modulo.nome));
                    tr.append($('<td></td>').append(
                        $('<input type="number" min="0" max="99999" class="form-control input-sm">').attr('name', 'modulos[' + modulo.id + '][usuarios]').val(modulo.usuarios || 0)
                    ));
                    var acesso = $('<div class="btn-group btn-group-xs habil-acesso" data-toggle="buttons"></div>');
                    $.each(acessos, function(j, opcao) {
                        var marcado = (modulo.status || 'B') === opcao.valor;
                        $('<label class="btn btn-default"></label>')
                            .addClass('acesso-' + opcao.valor)
                            .toggleClass('active', marcado)
                            .append($('<input type="radio" autocomplete="off">').attr('name', 'modulos[' + modulo.id + '][status]').val(opcao.valor).prop('checked', marcado))
                            .append(document.createTextNode(' ' + opcao.texto))
                            .appendTo(acesso);
                    });
                    tr.append($('<td></td>').append(acesso));
                    tabela.find('tbody').append(tr);
                });
                form.append(tabela);
            } else {
                form.append(el('p', 'text-muted', 'Este produto não tem módulos cadastrados.'));
            }

            var rodape = el('div', 'habil-form-rodape');
            rodape.append(el('span', 'text-red habil-form-erro'));
            rodape.append($('<button type="button" class="btn btn-default btn-sm js-cancelar">Cancelar</button>'));
            rodape.append($('<button type="submit" class="btn btn-primary btn-sm"></button>').append('<i class="fa fa-check"></i> ').append(document.createTextNode(dados.id ? 'Salvar alterações' : 'Habilitar produto')));
            form.append(rodape);

            form.find('.input-group.date').datetimepicker({locale: 'pt-br', format: 'DD/MM/YYYY', showClose: true, useCurrent: false, icons: {close: 'closeText'}});
            atualizarTipo(form, dados);
            return form;
        }

        function atualizarTipo(form, dados) {
            var demo = form.find('input[name="demonstracao"]').val() === 'S';
            form.find('.habil-datas input').prop('disabled', demo);
            var ajuda = form.find('.habil-periodo-ajuda');
            if (demo && dados.id && dados.demonstracao) {
                ajuda.text('Demonstração em andamento; o período não muda ao salvar.');
            } else if (demo) {
                var fim = new Date();
                fim.setDate(fim.getDate() + 30);
                ajuda.text('Começa hoje e termina em ' + ('0' + fim.getDate()).slice(-2) + '/' + ('0' + (fim.getMonth() + 1)).slice(-2) + '/' + fim.getFullYear() + '.');
            } else {
                ajuda.text('Informe o período do contrato.');
            }
        }

        function montarCard(item) {
            var card = el('div', 'habil-card situacao-' + item.situacao);
            var topo = el('div', 'habil-card-topo');
            var titulo = el('div', 'habil-card-titulo');
            titulo.append(el('strong', '', item.produto));
            titulo.append(' ', el('span', 'label ' + situacoes[item.situacao].classe, textoSituacao(item)));
            if (item.demonstracao) { titulo.append(' ', el('span', 'label label-info', 'Demonstração')); }
            topo.append(titulo);

            var meta = [];
            if (item.inicio || item.fim) { meta.push(item.inicio + ' a ' + item.fim); }
            if (item.contrato_sax) { meta.push('Contrato MaestriaTech ' + item.contrato_sax); }
            meta.push(resumoModulos(item.modulos));
            topo.append(el('div', 'habil-card-meta', meta.join(' · ')));

            var botoes = el('div', 'habil-card-botoes');
            botoes.append($('<button type="button" class="btn btn-default btn-xs js-editar"><i class="fa fa-pencil"></i> Editar</button>'));
            if (item.situacao === 'desabilitada') {
                botoes.append($('<button type="button" class="btn btn-default btn-xs js-status" data-status="S"><i class="fa fa-undo"></i> Reabilitar</button>'));
            } else {
                botoes.append($('<button type="button" class="btn btn-default btn-xs js-status" data-status="N"><i class="fa fa-ban"></i> Desabilitar</button>'));
            }
            if (item.historico.length) {
                botoes.append($('<button type="button" class="btn btn-link btn-xs js-historico"></button>').text('Histórico (' + item.historico.length + ')'));
            }
            topo.append(botoes);
            card.append(topo);

            var editar = el('div', 'habil-card-painel habil-card-editar').hide();
            card.append(editar);

            var status = el('div', 'habil-card-painel habil-card-status').hide();
            var desabilitar = item.situacao !== 'desabilitada';
            status.append(el('label', '', desabilitar ? 'Por que desabilitar este produto?' : 'Por que reabilitar este produto?'));
            status.append($('<textarea class="form-control" rows="2" maxlength="1000"></textarea>').attr('placeholder', desabilitar ? 'Ex.: contrato encerrado em 30/09.' : 'Ex.: contrato renovado.'));
            var rodape = el('div', 'habil-form-rodape');
            rodape.append(el('span', 'text-red habil-form-erro'));
            rodape.append($('<button type="button" class="btn btn-default btn-sm js-cancelar">Cancelar</button>'));
            rodape.append($('<button type="button" class="btn btn-sm js-confirmar-status"></button>').addClass(desabilitar ? 'btn-danger' : 'btn-success').attr('data-status', desabilitar ? 'N' : 'S').text(desabilitar ? 'Desabilitar' : 'Reabilitar'));
            status.append(rodape);
            card.append(status);

            if (item.historico.length) {
                var historico = el('ul', 'habil-card-painel habil-historico').hide();
                $.each(item.historico, function(i, registro) {
                    var li = el('li');
                    li.append(el('strong', '', registro.acao), ' ', el('span', 'text-muted', (registro.usuario || 'Usuário removido') + ' · ' + registro.data));
                    li.append(el('div', '', registro.observacao));
                    historico.append(li);
                });
                card.append(historico);
            }

            card.data('item', item);
            return card;
        }

        function renderizar(dados) {
            clienteAtual = dados.cliente;
            disponiveis = dados.disponiveis;
            $('#habil-cliente-nome').text(dados.cliente.nome);
            $('#habil-cliente-detalhe').text(dados.cliente.razao_social && dados.cliente.razao_social !== dados.cliente.nome ? dados.cliente.razao_social : '');

            var lista = $('#habil-lista').empty();
            var ativos = 0;
            $.each(dados.habilitados, function(i, item) {
                if (item.situacao === 'vigente' || item.situacao === 'vencendo') { ativos++; }
                lista.append(montarCard(item));
            });
            if (!dados.habilitados.length) {
                lista.append(el('p', 'text-muted habil-vazio', 'Nenhum produto habilitado para este cliente ainda.'));
            }
            $('#habil-contagem').text(dados.habilitados.length ? '(' + ativos + ' vigente' + (ativos === 1 ? '' : 's') + ' de ' + dados.habilitados.length + ')' : '');

            var select = $('#habil-novo-produto').empty();
            select.append($('<option value="">Selecione o produto...</option>'));
            $.each(disponiveis, function(i, produto) {
                var ja = $.grep(dados.habilitados, function(item) { return item.id_produto === produto.id && (item.situacao === 'vigente' || item.situacao === 'vencendo'); }).length > 0;
                select.append($('<option></option>').val(produto.id).text(produto.nome + (ja ? ' (já vigente)' : '')));
            });
            $('#habil-novo-info').text('');
            $('#habil-novo-form').empty();
            $('#habil-carregando').hide();
            $('#habil-conteudo').show();
        }

        function carregar(idCliente, mensagem) {
            if (!mensagem) { alerta(); }
            $('#habil-conteudo').hide();
            $('#habil-carregando').show();
            $.post(urlBase + 'produtos_cliente', {id_cliente: idCliente}, null, 'json')
                .done(function(dados) {
                    renderizar(dados);
                    if (mensagem) { alerta('success', mensagem); }
                })
                .fail(function(xhr) {
                    $('#habil-carregando').hide();
                    alerta('danger', (xhr.responseJSON && xhr.responseJSON.msg) || 'Não foi possível carregar os produtos do cliente.');
                });
        }

        function aposGravar(mensagem) {
            carregar(clienteAtual.id, mensagem);
            if (typeof window.habilAoAlterar === 'function') { window.habilAoAlterar(); }
        }

        window.habilAbrirProdutos = function(idCliente) {
            $('#habil-cliente-nome').text('');
            $('#habil-cliente-detalhe').text('');
            $('#habilModal').modal('show');
            carregar(idCliente);
        };

        var modal = $('#habilModal');

        modal.on('change', '#habil-novo-produto', function() {
            var id = parseInt($(this).val(), 10);
            var produto = $.grep(disponiveis, function(item) { return item.id === id; })[0];
            $('#habil-novo-form').empty();
            $('#habil-novo-info').text('');
            if (!produto) { return; }
            var info = [];
            if (produto.aplicacao) { info.push(produto.aplicacao); }
            if (produto.versao) { info.push('versão ' + produto.versao); }
            $('#habil-novo-info').text(info.join(' · '));
            var modulos = $.map(produto.modulos, function(modulo) { return {id: modulo.id, nome: modulo.nome, usuarios: 0, status: 'L'}; });
            $('#habil-novo-form').append(montarFormulario({id_produto: produto.id, demonstracao: false, inicio: '', fim: '', contrato_sax: '', modulos: modulos}));
        });

        modal.on('click', '.habil-tipo .btn', function() {
            var form = $(this).closest('form');
            $(this).addClass('active').siblings().removeClass('active');
            form.find('input[name="demonstracao"]').val($(this).data('valor'));
            var card = form.closest('.habil-card');
            atualizarTipo(form, card.length ? card.data('item') : {});
        });

        modal.on('click', '.js-todos', function(event) {
            event.preventDefault();
            var valor = $(this).data('valor');
            $(this).closest('form').find('.habil-acesso').each(function() {
                $(this).find('label').removeClass('active').find('input').prop('checked', false);
                $(this).find('input[value="' + valor + '"]').prop('checked', true).parent().addClass('active');
            });
        });

        modal.on('click', '.js-editar', function() {
            var card = $(this).closest('.habil-card');
            var painel = card.find('.habil-card-editar');
            card.find('.habil-card-status, .habil-historico').hide();
            if (painel.is(':visible')) { painel.hide(); return; }
            painel.empty().append(montarFormulario(card.data('item'))).show();
        });

        modal.on('click', '.js-status', function() {
            var card = $(this).closest('.habil-card');
            card.find('.habil-card-editar, .habil-historico').hide();
            card.find('.habil-card-status').toggle().find('textarea').focus();
        });

        modal.on('click', '.js-historico', function() {
            var card = $(this).closest('.habil-card');
            card.find('.habil-card-editar, .habil-card-status').hide();
            card.find('.habil-historico').toggle();
        });

        modal.on('click', '.js-cancelar', function() {
            var painel = $(this).closest('.habil-card-painel');
            if (painel.length) { painel.hide(); return; }
            $('#habil-novo-produto').val('').trigger('change');
        });

        modal.on('submit', '.habil-form', function(event) {
            event.preventDefault();
            var form = $(this);
            var erro = form.find('.habil-form-erro').text('');
            if (form.find('input[name="demonstracao"]').val() !== 'S') {
                var inicio = form.find('input[name="inicio"]').val(), fim = form.find('input[name="fim"]').val();
                if (!/^\d{2}\/\d{2}\/\d{4}$/.test(inicio) || !/^\d{2}\/\d{2}\/\d{4}$/.test(fim)) {
                    erro.text('Informe o início e o fim do período.');
                    return;
                }
                if (fim.split('/').reverse().join('') < inicio.split('/').reverse().join('')) {
                    erro.text('O fim precisa ser igual ou posterior ao início.');
                    return;
                }
            }
            var botao = form.find('button[type="submit"]').prop('disabled', true);
            $.post(urlBase + 'salvar_habilitacao', form.serialize(), null, 'json')
                .done(function(resposta) { aposGravar(resposta.msg); })
                .fail(function(xhr) {
                    erro.text((xhr.responseJSON && xhr.responseJSON.msg) || 'Não foi possível salvar. Tente novamente.');
                    botao.prop('disabled', false);
                });
        });

        modal.on('click', '.js-confirmar-status', function() {
            var botao = $(this);
            var card = botao.closest('.habil-card');
            var justificativa = $.trim(card.find('.habil-card-status textarea').val());
            var erro = card.find('.habil-card-status .habil-form-erro').text('');
            if (!justificativa) {
                erro.text('Informe a justificativa.');
                return;
            }
            botao.prop('disabled', true);
            $.post(urlBase + 'alterar_status', {id_habilitacao: card.data('item').id, status: botao.data('status'), justificativa: justificativa}, null, 'json')
                .done(function(resposta) { aposGravar(resposta.msg); })
                .fail(function(xhr) {
                    erro.text((xhr.responseJSON && xhr.responseJSON.msg) || 'Não foi possível alterar. Tente novamente.');
                    botao.prop('disabled', false);
                });
        });

        $(document).on('click', '.js-gerenciar-produtos', function() {
            window.habilAbrirProdutos($(this).data('cliente'));
        });
    });
</script>
<style type="text/css">
    .habil-modal .modal-title { font-weight: 600; }
    .habil-modal-subtitulo { color: #777; font-size: 12px; margin-top: 2px; }
    .habil-carregando { padding: 30px 0; }
    .habil-alerta { margin-bottom: 12px; }
    .habil-secao { padding: 12px 0; border-top: 1px solid #f0f2f4; }
    .habil-secao:first-of-type { border-top: 0; padding-top: 0; }
    .habil-secao h5 { margin: 0 0 10px; font-weight: 600; font-size: 14px; }
    .habil-step { display: inline-block; width: 22px; height: 22px; line-height: 22px; margin-right: 4px; border-radius: 50%; background: #3c8dbc; color: #fff; text-align: center; font-size: 12px; }
    .habil-vazio { margin: 0; }
    .habil-card { border: 1px solid #d2d6de; border-left: 4px solid #00a65a; border-radius: 4px; margin-bottom: 8px; background: #fff; }
    .habil-card.situacao-vencendo { border-left-color: #f39c12; }
    .habil-card.situacao-vencida { border-left-color: #dd4b39; }
    .habil-card.situacao-desabilitada { border-left-color: #b5bbc8; background: #fafafa; }
    .habil-card-topo { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 12px; padding: 9px 12px; }
    .habil-card-titulo { flex: 1 1 260px; }
    .habil-card-titulo strong { font-size: 14px; }
    .habil-card-meta { flex: 1 1 100%; order: 3; color: #666; font-size: 12px; }
    .habil-card-botoes { display: flex; gap: 4px; }
    .habil-card-painel { border-top: 1px solid #eef0f2; padding: 10px 12px; }
    .habil-historico { list-style: none; margin: 0; font-size: 12px; }
    .habil-historico li { padding: 4px 0; border-bottom: 1px dashed #eee; }
    .habil-historico li:last-child { border-bottom: 0; }
    .habil-form label { font-weight: 600; }
    .habil-datas { display: flex; align-items: center; gap: 6px; }
    .habil-datas .input-group { flex: 1 1 0; }
    .habil-ate { color: #777; }
    .habil-tipo .btn.active { background: #eaf2f8; border-color: #3c8dbc; box-shadow: none; }
    .habil-modulos-topo { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; margin-bottom: 4px; }
    .habil-atalhos a { margin-left: 10px; font-size: 12px; }
    .habil-modulos > tbody > tr > td { vertical-align: middle; border-top: 0; border-bottom: 1px solid #eef0f2; }
    .habil-modulos .input-sm { width: 90px; }
    .habil-acesso .btn.active.acesso-L { background: #00a65a; border-color: #008d4c; color: #fff; }
    .habil-acesso .btn.active.acesso-C { background: #3c8dbc; border-color: #367fa9; color: #fff; }
    .habil-acesso .btn.active.acesso-B { background: #dd4b39; border-color: #d73925; color: #fff; }
    .habil-form-rodape { display: flex; justify-content: flex-end; align-items: center; gap: 6px; margin-top: 8px; }
    .habil-form-erro { margin-right: auto; font-size: 12px; }
</style>
