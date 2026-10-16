import { api_get } from "../../helpers/api/get.js";
import { Decimal, getDecimal, DecimalInterno, DecimalToFloat } from "./helper.js"
import { GetImpostos, Impostos } from "./impostos.js"
import { datatable as dtFornecedor } from "../../general/providers/utils/datatable.js";
import { datatable as dtFormaPagamento } from "../../financial/payment_form/utils/datatable.js";

$('#btnModalProduto').on('click',()=>{ modalProduto() })
$('#m_tabela').on('blur', ()=>{ GetTabelaPrecoModalId() })
$('#tabela_ref').on('click',()=>{ getTabPrecoProduto() })
$('#m_tabela_btn').on('click',()=>{ getTabPrecoProduto() })
$('#produto_id').on('blur', ()=>{ GetProdutoId() })
$('#produto_ref').on('click',()=>{ getProdutos() })
$('#produto_btn').on('click',()=>{ getProdutos() })
$('#btnProdutoAdd').on('click',()=>{ modalProdutoSalvar() })
$('#btnModalPadronizado').on('click',()=>{ modalPadronizado() })
$('#padronizado_id').on('blur', ()=>{ GetPadronizadoId() })
$('#padronizado_btn').on('click',()=>{ getPadronizados() })
$('#btnPadronizadoAdd').on('click',()=>{ modalPadronizadoSalvar() })
$(document).on('click', '.editar_produto', function (event) {
    let strct = event.target.id.split('_')
    modalProduto(strct[strct.length-1])
});
$(document).on('click', '.remover_produto', function (event) {
    produtoRemove(event.target.id)
});
$('#btnModalServico').on('click',()=>{ modalServico() })
$('#servico_id').on('blur', ()=>{ GetServicoId() })
$('#servico_btn').on('click',()=>{ getServicos() })
$('#m_servico_qtd').on('change',()=>{ CalculaValoresServico() })
$('#m_servico_preco').on('change',()=>{ CalculaValoresServico() })
$('#m_servico_per_desc').on('change',()=>{ CalculaValoresServico() })
$('#m_servico_desconto').on('change',()=>{ CalculaValoresServico('desconto') })
$('#btnServicoAdd').on('click',()=>{ modalServicoSalvar() })
$(document).on('click', '.editar_servico', function (event) {
    let strct = event.target.id.split('_')
    modalServico(strct[strct.length-1])
});
$(document).on('click', '.remover_servico', function (event) {
    servicoRemove(event.target.id)
});
$(document).on('click', '.impostos_produto', function (event) {
    let strct = event.target.id.split('_')
    modalImpostosProduto(strct[strct.length-1])
});

$('#tabela').on('click',()=>{ getTabPreco() })
$('#tabela_btn').on('click',()=>{ getTabPreco() })

$('#transportadora_id').on('blur', ()=>{ GetTransportadoraId() })
$('#transportadora').on('click',()=>{ GetTransportadora() })
$('#transportadora_btn').on('click',()=>{ GetTransportadora() })

$('#m_quantidade').on('change',()=>{ CalculaValoresProduto() })
$('#m_preco').on('change',()=>{ CalculaValoresProduto() })
$('#m_per_desc').on('change',()=>{ CalculaValoresProduto() })
$('#m_desconto').on('change',()=>{ CalculaValoresProduto('desconto') })

$('#indicador_presenca').on('change', ()=>{ IndicadorPresenca() })

$('#intermediador_marketplace_id').on('blur', ()=>{ GetMarketplaceId() })
$('#intermediador_marketplace').on('click',()=>{ GetMarketplace() })
$('#intermediador_marketplace_btn').on('click',()=>{ GetMarketplace() })

$('#intermediador_pagamento_id').on('blur', ()=>{ GetInterPagamentoId() })
$('#intermediador_pagamento').on('click',()=>{ GetInterPagamento() })
$('#intermediador_pagamento_btn').on('click',()=>{ GetInterPagamento() })

GetFormaPagamentoId()

$('#forma_pagamento_id').on('blur', ()=>{ GetFormaPagamentoId() })
$('#forma_pagamento').on('click',()=>{ GetFormaPagamento() })
$('#forma_pagamento_btn').on('click',()=>{ GetFormaPagamento() })

/* adicionado inputmask nos campos com decimal 23/05/2024 */
$('.decimalUnit').inputmask({
    'alias':          'decimal',
    'groupSeparator': ',',
    'autoGroup':      true, 
    'digits':         $('#decimal_prod_unit').val(),
    'digitsOptional': false,
    'placeholder':    '0,00',
    'radixPoint': ','
});
$('.decimalQtde').inputmask({
    'alias':          'decimal',
    'groupSeparator': ',',
    'autoGroup':      true, 
    'digits':         $('#decimal_prod_qtde').val(),
    'digitsOptional': false,
    'placeholder':    '0,00',
    'radixPoint': ','
});
$('.decimalUnitCompras').inputmask({
    'alias':          'decimal',
    'groupSeparator': ',',
    'autoGroup':      true, 
    'digits':         $('#decimal_prod_unit_compras').val(),
    'digitsOptional': false,
    'placeholder':    '0,00',
    'radixPoint': ','
});
$('.decimalQtdeCompras').inputmask({
    'alias':          'decimal',
    'groupSeparator': ',',
    'autoGroup':      true, 
    'digits':         $('#decimal_prod_qtde_compras').val(),
    'digitsOptional': false,
    'placeholder':    '0,00',
    'radixPoint': ','
});
$('.decimalUnitVendas').inputmask({
    'alias':          'decimal',
    'groupSeparator': ',',
    'autoGroup':      true, 
    'digits':         $('#decimal_prod_unit_vendas').val(),
    'digitsOptional': false,
    'placeholder':    '0,00',
    'radixPoint': ','
});
$('.decimalQtdeVendas').inputmask({
    'alias':          'decimal',
    'groupSeparator': ',',
    'autoGroup':      true, 
    'digits':         $('#decimal_prod_qtde_vendas').val(),
    'digitsOptional': false,
    'placeholder':    '0,00',
    'radixPoint': ','
});

if ($("#frete_seguro").val() == "0,00") {
    SomaFreteSeguro();
}

$(".frete_seguro").on("change", () => {
    SomaFreteSeguro();
});

function modalProduto(id = null){
    if(id != null){
        let pos = null
        for(let i = 0; i < $('.produtos_id').length; i++){
            if($('.produtos_id')[i].value == id){
                pos = i
                break
            }
        }

        let prod_id = $('.produtos_id')[pos].value
        let prod_seq = $('.produtos_seq')[pos].value
        let prod_qtd = $('.produtos_qtd')[pos].innerText
        let prod_preco = $('.produtos_preco')[pos].innerText
        let prod_desc = $('.produtos_desc')[pos].innerText
        let prod_total = $('.produtos_total')[pos].innerText

        $('#produto_ref').text('')
        $('#preco_ref').text('')
        $('#produto_id').val(prod_id)
        //$('#m_quantidade').val(parseFloat(prod_qtd).toFixed($('#decimal_prod_qtde_vendas').val()).replace('.',','))
        $('#m_quantidade').val(parseInt(prod_qtd))
        $('#m_preco').val(prod_preco)
        $('#m_per_desc').val(0)
        $('#m_desconto').val(prod_desc)
        $('#m_total').val(prod_total)

        $('#position').val(pos)
        
        GetProdutoId(false)
        
        $('#modal-produto').modal('show')
    }else{
        $('#produto_ref').text('')
        $('#preco_ref').text('')
        $('#estoque_real_ref').text('')
        $('#produto_id').val('')
        //$('#m_quantidade').val(parseFloat(1).toFixed($('#decimal_prod_unit_vendas').val()).replace('.',','))
        $('#m_quantidade').val(1)
        $('#m_preco').val(0)
        $('#m_per_desc').val(0)
        $('#m_desconto').val(0)
        $('#m_total').val(0)
        $('#position').val('')
        
        $('#modal-produto').modal('show')

        $('#modal-produto').on('shown.bs.modal', function () {
            $('#produto_id').focus();
        })
    }

    $("#m_tabela").val($('#tabela_id').val());
    $("#tabela_ref").text($('#tabela').text());

    $('#produto_img').removeAttr('src')
}

function GetProdutoId(override = true){
    let params = {
        tabela: $('#tabela_id').val(),
        setor: $('#setor_id').val(),
        empresa: $('#filial_id').val(),
        ativo: 'S'
    }
    
    $('#produto_spin').show()
    api_get('/produto/'+$('#produto_id').val(), params).then(response => {
        $('#produto_spin').hide()
        
        if(response != null){
            SetProduto(response, override)
            $('#produto_p').text('')

            CalculaValoresProduto()
        }else{
            $('#produto_id').val('')
            $('#produto_ref').text('')
            $('#preco_ref').text('')
            $('#estoque_real_ref').text('')
            $('#m_preco').val('')
            $('#m_per_desc').val('')
            $('#m_desconto').val('')
            $('#m_total').val('')
            $('#produto_p').text('Produto não encontrado')
        }
    })
}

function CalculaValoresProduto(campo = null){
    let quantidade = DecimalToFloat($('#m_quantidade').val())
    let preco = DecimalToFloat($('#m_preco').val())
    let per_desc = DecimalToFloat($('#m_per_desc').val())
    let desconto = DecimalToFloat($('#m_desconto').val())
    let desc_max = parseFloat(localStorage.getItem('desconto_maximo'))
    
    if(campo == 'desconto'){
        per_desc = desconto/preco*100
    }

    if(per_desc > desc_max){
        per_desc = desc_max
        $('#m_per_desc').val(DecimalInterno(per_desc, $('#decimal_prod_unit_vendas').val()))
    }
    desconto = (per_desc / 100 * preco);
    $('#m_desconto').val(DecimalInterno(desconto, $('#decimal_prod_unit_vendas').val()))
    $('#m_total').val(DecimalInterno((preco - parseFloat(desconto)) * quantidade, $('#decimal_prod_unit_vendas').val()))
}

function GetTabelaPrecoModalId(){
    let params = {
        cliente: $('#cliente_id').val(),
        vendedor: $('#vendedor_id').val(),
        ativo: 'S'
    }

    $('#m_tabela_spin').show()
    api_get('/tabela_precos/'+$('#m_tabela').val(), params).then(response => {
        $('#m_tabela_spin').hide()
        if(response != null){
            $('#tabela_ref').text(response.codigo + " / " + response.descricao);
            $('#m_tabela_p').text('')
        }else{
            $('#m_tabela').val('')
            $('#tabela_ref').text('')
            $('#m_tabela_p').text('Tabela não encontrada')
        }
    })
}

function getTabPrecoProduto() {
    $( "#modal-tabela_precos" ).modal( "show" );
    $('#datatable-tabela_precos').DataTable().destroy();
    TabPrecoDatatable("#datatable-tabela_precos")

    $('#datatable-tabela_precos tbody').on('click', 'tr', function () {
        SetTabPrecoProduto(this.childNodes)
    });
}

function SetTabPrecoProduto(data){
    $("#tabela_m").text(data[0].innerText + " / " + data[1].innerText);
    $("#tabela_ref").text(data[0].innerText + " / " + data[1].innerText);
    $("#modal-tabela_precos").modal( "hide" );
}

function TabPrecoDatatable(id){
    return $(id).DataTable({
        "responsive": true, 
        "select": true,
        "columnDefs": [
            { "responsivePriority": 1, "targets": 0 },
            { "responsivePriority": 2, "targets": 1 },
            { "responsivePriority": 3, "targets": 2 }
        ],
        "lengthChange": true, 
        "pageLength" : 10,
        "lengthMenu": [ 10, 25, 50, 75, 100 ],
        "autoWidth": false,
        "ordering": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
        },
        "searching": true,
        "aaSorting": [ [0,'asc'] ],
        "processing": true,
        "serverSide": true,
        "initComplete": function () {
              $(id+'_filter label input').focus();
        },
        "ajax": async (data, callback) => {
            var column_to_order = "";
            if(data.order[0].column == 0)
                column_to_order = "codigo";
            if(data.order[0].column == 1)
                column_to_order = "descricao";
            if(data.order[0].column == 2)
                column_to_order = "padrao";

            var params_get = { 
                offset: data.start, 
                limit: data.length, 
                ordem: JSON.stringify([{campo : column_to_order, ordem : data.order[0].dir}]),
                texto: data.search.value,
                cliente: $('#cliente_id').val(),
                vendedor: $('#vendedor_id').val(),
                ativo: 'S'
          };

            var url = '/tabela_precos'
            api_get(url, params_get).then(response => {
                const objs = [];
                $.each(response.data, function (index, value) {
                    let obj = [];
                    obj.push(value.codigo);
                    obj.push(value.descricao);
                    obj.push((value.padrao == "S") ? "Sim" : "Não");
                    objs.push(obj);
                });

                var json_data = {
                    "draw"        : data.draw,  
                    "recordsTotal"    : response.total,  
                    "recordsFiltered" : response.total, 
                    "data"        : objs
                };
                
                callback(json_data);
            })
        }
    });
}

function getTabPreco() {
    $( "#modal-tabela_precos" ).modal( "show" );
    $('#datatable-tabela_precos').DataTable().destroy();
    TabPrecoDatatable("#datatable-tabela_precos")
    
    $('#datatable-tabela_precos tbody').on('click', 'tr', function () {
        $("#tabela_id").val(this.childNodes[0].innerText);
        $("#tabela").text(this.childNodes[0].innerText + " / " + this.childNodes[1].innerText);
        $( "#modal-tabela_precos" ).modal( "hide" );
        
        SetTabPrecoProduto(this.childNodes)
    });
}

function SetProduto(produto, override = true){
    if(produto != undefined){
        let preco = ''
        
        if(produto.tabela != undefined){
            preco = 'Preço: R$' + getDecimal(produto.tabela.produto.preco)
            preco += ' | Preço mínimo: R$' + getDecimal(produto.tabela.produto.preco_minimo)
            if(produto.tabela.produto.promocional_marcado == 'S'){
                preco += ' | Preço promocional: R$' + getDecimal(produto.tabela.produto.promocao)
            }
            
            if(override){
                $('#m_preco').val(getDecimal(produto.tabela.produto.preco))
            }
        }

        let fracionavel = null
        if(produto.fracionavel != null){
            fracionavel = produto.fracionavel
        }else{
            fracionavel = produto.unid_prod?produto.unid_prod.fracionavel:null
        }
        
        if(fracionavel == 'S'){
            //$('#m_quantidade').val(Decimal(parseFloat($('#m_quantidade').val().replaceAll('.','').replace(',','.')), $('#decimal_prod_qtde_vendas').val()))
            //$('#m_quantidade').val(parseInt($('#m_quantidade').val()))
            //$('#m_quantidade').on('keyup',(e)=>{
            //    let elem = e.currentTarget
            //    elem.value = Decimal(parseFloat(elem.value.replaceAll('.','').replace(',','.')), $('#decimal_prod_qtde_vendas').val())
            //});
            $('#m_quantidade').inputmask({
                'alias':          'decimal',
                'groupSeparator': ',',
                'autoGroup':      true, 
                'digits':         $('#decimal_prod_qtde_vendas').val(),
                'digitsOptional': false,
                'placeholder':    '0,00',
                'radixPoint': ','
            });
        }else{
            $('#m_quantidade').off('keyup')
        }
        
        if(produto.codigo != undefined){
            $("#produto_id").val(produto.codigo)
            $('#produto_ref').text(produto.codigo + ' / ' + produto.descricao);
            $('#produto_img').attr('src', api_url+'/produto/foto/' + produto.codigo)
            if(override){
                $('#preco_ref').text(preco);
            }
            if(produto.estoque_real !== undefined && produto.estoque_real !== null && produto.estoque_real !== ''){
                $('#estoque_real_ref').html('Estoque real: ' + produto.estoque_real);
            } else {
                $('#estoque_real_ref').text('');
            }
        }
    }
}

let ultimoModalAberto = null;


$('#produto_btn').on('click', function () {
    ultimoModalAberto = $("#modal-novo_pc"); 
    ultimoModalAberto.modal("hide");

    setTimeout(() => {
        getProdutos();
    }, 100);
});


function getProdutos() {
    $("#modalAddProduct").modal("show");
    $('#datatable-add_product').DataTable().destroy();

    var dt_add = $("#datatable-add_product").DataTable({
        "responsive": true,
        "lengthChange": true,
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 75, 100],
        "autoWidth": false,
        "ordering": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
        },
        "columnDefs": [{ targets: [3], className: 'text-right' }],
        "searching": true,
        "aaSorting": [[1, 'asc']],
        "processing": true,
        "serverSide": true,
        "initComplete": function () {
            $('#datatable-add_product_filter label input').focus();
        },
        "ajax": async (data, callback) => {
            var params_get = { 
                offset: data.start, 
                limit: data.length, 
                texto: data.search.value,
                tabela: $('#m_tabela').val(),
                setor: $('#setor_id').val(),
                empresa: $('#filial_id').val(),
                ativo: 'S'
            };

            api_get('/produto/', params_get).then(response => {
                const objs = [];
                
                $.each(response.data, function (index, value) {
                    let preco = value.tabela?.produto?.preco ?? 0;

                    let obj = [];
                    obj.push(value.codigo);
                    obj.push(value.descricao);
                    obj.push(value.estoque_real);
                    obj.push(parseFloat(preco).toFixed(2).replace('.', ','));
                    obj.push(value.unid_prod ? value.unid_prod.fracionavel : '');
                    objs.push(obj);
                });

                var json_data = {
                    "draw": data.draw,
                    "recordsTotal": response.total,
                    "recordsFiltered": response.total,
                    "data": objs
                };

                callback(json_data);
            });
        }
    });

    $('#datatable-add_product tbody').on('click', 'tr', function () {
        let produto = {
            codigo: this.childNodes[0].innerText,
            descricao: this.childNodes[1].innerText,
            estoque_real: this.childNodes[2].innerText,
            fracionavel: this.childNodes[4].innerText,
            tabela: {
                produto: {
                    preco: DecimalToFloat(this.childNodes[3].innerText),
                    preco_minimo: 0,
                    promocional_marcado: 'N',
                    promocao: 0
                }
            }
        };

        $("#descricao\\ desc_prod").val(produto.descricao);

        SetProduto(produto);
        CalculaValoresProduto();

        $("#modalAddProduct").modal("hide");
    });

    $('#modalAddProduct').on('hidden.bs.modal', function () {
        if (ultimoModalAberto) {
            ultimoModalAberto.modal("show");
        }
    });
}


async function modalProdutoSalvar(){
    let item = {
        id: $('#produto_id').val(),
        quantidade: $('#m_quantidade').val(),
        preco: $('#m_preco').val(),
        per_desc: $('#m_per_desc').val(),
        desconto: $('#m_desconto').val(),
        total: $('#m_total').val(),
    }

    await ProdutoSalvar(item)
                
    ProdutoTotais()
    Impostos()
    
    if($('#position').val() != ''){
        $('#modal-produto').modal('hide')
    }else{
        modalProduto()
    }
}

async function ProdutoSalvar(item){
    if(item.id != ''){
        $("#btnProdutoAdd").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...')
        $('#btnProdutoAdd').attr('disabled', true)
        await api_get('/produto/'+item.id, {}).then(response => {
            if(response.codigo != undefined){
                let pos = $('#position').val()

                if(pos == ''){
                    let sequencia = 1
                    
                    if($('.produto_sequencia').length > 0){
                        sequencia = parseInt($('.produto_sequencia')[$('.produto_sequencia').length-1].innerText) + 1
                    }

                    let tr = $("<tr class='produto_tr' id='"+response.codigo+"'>")
                    
                    tr.append("<td width='60px'><img class='produto_img' src='"+api_url+"/produto/foto/" + response.codigo + "' width='100%'></td>")
                    tr.append($('<td class="produto_sequencia">').text(sequencia))

                    let td = $('<td>')
                    td.append("<span class='produto_span'>"+response.codigo+"</span>")
                    td.append($("<input type='hidden' class='produtos_id'>").val(response.codigo))
                    td.append($("<input type='hidden' class='produtos_seq'>").val(sequencia))
                    //td.append($("<input type='hidden' class='produtos_qtd'>").val(quantidade.replace('.','').replace(',','.')))
                    //td.append($("<input type='hidden' class='produtos_preco'>").val(preco.replace('.','').replace(',','.')))
                    //td.append($("<input type='hidden' class='produtos_p_desc'>").val(per_desc.replace('.','').replace(',','.')))
                    //td.append($("<input type='hidden' class='produtos_desc'>").val(desconto.replace('.','').replace(',','.')))
                    //td.append($("<input type='hidden' class='produtos_total'>").val(total.replace('.','').replace(',','.')))

                    let html = '<div class="btn-group">'
                    html += '    <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown">'
                    html += '        <span class="sr-only">Toggle Dropdown</span>'
                    html += '    </button>'
                    html += '    <div class="dropdown-menu" role="menu">'
                    html += '        <a class="dropdown-item editar_produto text-primary" href="javascript:void(0)" id="editar_produto_'+response.codigo+'">Editar</a>'
                    html += '        <a class="dropdown-item impostos_produto text-primary" href="javascript:void(0)" id="impostos_produto_'+response.codigo+'">Impostos</a>'
                    html += '        <a class="dropdown-item remover_produto text-danger" href="javascript:void(0)" id="remover_produto_'+response.codigo+'">Excluir</button>'
                    html += '    </div>'
                    html += '</div>'

                    tr.append(td)
                    tr.append($('<td>').text(response.descricao))
                    tr.append('<td><span class="produtos_qtd">'+item.quantidade+'</span>')
                    tr.append($('<td>').text('UNID'))
                    tr.append('<td><span class="produtos_preco">'+item.preco+'</span>')
                    tr.append('<td><span class="produtos_p_desc">'+item.per_desc+'</span>')
                    tr.append('<td><span class="produtos_desc">'+item.desconto+'</span>')
                    tr.append('<td><span class="produtos_total">'+item.total+'</span>')
                    tr.append($('<td>').append(html))

                    $('#datatable-produtos').append(tr)
                    
                }else{
                    $('.produto_tr')[pos].id = response.codigo
                    $('.produto_img')[pos].src = api_url+"/produto/foto/" + response.codigo
                    $('.produto_span')[pos].innerText = response.codigo
                    $('.produtos_id')[pos].value = response.codigo
                    $('.produtos_qtd')[pos].innerText = item.quantidade
                    $('.produtos_preco')[pos].innerText = item.preco
                    $('.produtos_p_desc')[pos].innerText = item.per_desc
                    $('.produtos_desc')[pos].innerText = item.desconto
                    $('.produtos_total')[pos].innerText = item.total
                    $('.editar_produto')[pos].id = "editar_produto_"+response.codigo
                    $('.remover_produto')[pos].value = "remover_produto"+response.codigo
                }
            }
            
            $("#btnProdutoAdd").html('Adicionar')
            $('#btnProdutoAdd').attr('disabled', false) 
        }) 
    }
}

function ProdutoTotais(loadTotal = true){
    let preco = 0;
    let desconto = 0;
    let quantidade = 0;

    let produtos_qtd = $('.produtos_qtd');
    for(let i = 0; i < produtos_qtd.length; i++){
        //quantidade += parseFloat(produtos_qtd[i].innerText.replace('.','').replace(',','.'))
        quantidade += parseInt(produtos_qtd[i].innerText)
    }
    
    let produtos_preco = $('.produtos_preco');
    for(let i = 0; i < produtos_preco.length; i++){
        preco += parseFloat(
            produtos_preco[i].innerText.replace('.','').replace(',','.') *
            produtos_qtd[i].innerText.replace('.','').replace(',','.')
        )
    }

    let produtos_desc = $('.produtos_desc');
    for(let i = 0; i < produtos_desc.length; i++){
        desconto += parseFloat(produtos_desc[i].innerText.replace('.','').replace(',','.') *
            produtos_qtd[i].innerText.replace('.','').replace(',','.')
        )
    }

    let per_desconto_produto = 0
    if(preco > 0){
        per_desconto_produto = desconto/preco*100
    }
    
    $('#produtos_desc').text(desconto.toFixed($('#decimal_prod_unit_vendas').val()).replace('.',','))
    $('#produtos_total').text((preco - desconto).toFixed($('#decimal_prod_unit_vendas').val()).replace('.',','))

    //$('#qtde_produtos').val(quantidade.toFixed($('#decimal_prod_qtde_vendas').val()).replace('.',','))
    $('#qtde_produtos').val(quantidade)
    $('#total_produtos').val(preco.toFixed($('#decimal_prod_unit_vendas').val()).replace('.',','))
    $('#desconto_produto').val(desconto.toFixed($('#decimal_prod_unit_vendas').val()).replace('.',','))
    $('#per_desconto_produto').val(per_desconto_produto.toFixed($('#decimal_prod_unit_vendas').val()).replace('.',','))

    if(loadTotal){
        TotalGeral()
    }
}

function TotalGeral(){
    let ipi = $('#total_ipi').val().replace('.','').replace(',','.')
    let valor_frete = $('#valor_frete').val().replace('.','').replace(',','.')
    let valor_seguro = $('#valor_seguro').val().replace('.','').replace(',','.')
    let outras_despesas = $('#outras_despesas').val().replace('.','').replace(',','.')

    let total_produtos = $('#total_produtos').val().replace('.','').replace(',','.') - $('#desconto_produto').val().replace('.','').replace(',','.')
    let total_servicos = $('#total_servico').val().replace('.','').replace(',','.') - $('#desconto_servico').val().replace('.','').replace(',','.')

    let adic_financeiro = $('#adic_financeiro').val().replace('.','').replace(',','.')

    $('#total_geral').val(
        getDecimal(
            parseFloat(ipi) +
            parseFloat(valor_frete) + 
            parseFloat(valor_seguro) + 
            parseFloat(outras_despesas) + 
            parseFloat(total_produtos) + 
            parseFloat(total_servicos) + 
            parseFloat(adic_financeiro)
        )
    )

    CalculaValorParcelas()
}

function CalculaValorParcelas(start = 0) {
    let formaPagamentoId = $('#forma_pagamento_id').val();

    if (!formaPagamentoId) {
        return;
    }

    api_get(`/forma_pagamento/${formaPagamentoId}`, {}).then(formaPagamento => {
        if (!formaPagamento) {
            console.error("Forma de pagamento não encontrada para o ID:", formaPagamentoId);
            return;
        }

        let numParcelas = parseInt(formaPagamento.num_parcelas) || 1;
        let intervalo = parseInt(formaPagamento.intervalo) || 30;
        let diasEntrada = parseInt(formaPagamento.dias_entrada) || 0;

        let total = parseFloat($('#total_geral').val().replace(/\./g, '').replace(',', '.')) || 0;
        let cobranca_frete = $('#cobranca_frete').val();
        let primeira_parcela = 0;

        let valor_frete = parseFloat($('#valor_frete').val().replace(/\./g, '').replace(',', '.')) || 0;
        let valor_outras_despesas = parseFloat($('#outras_despesas').val().replace(/\./g, '').replace(',', '.')) || 0;
        let valor_seguro = parseFloat($('#valor_seguro').val().replace(/\./g, '').replace(',', '.')) || 0;
        let valor_tributos = (parseFloat($('#total_ipi').val().replace(/\./g, '').replace(',', '.')) || 0) +
                             (parseFloat($('#valor_icms_sub').val().replace(/\./g, '').replace(',', '.')) || 0);

        // Aplica a regra de cobrança dos adicionais na primeira parcela
        if (cobranca_frete == 0) {
            total -= (valor_frete + valor_outras_despesas + valor_seguro + valor_tributos);
            primeira_parcela = valor_frete + valor_outras_despesas + valor_seguro + valor_tributos;
        } else if (cobranca_frete == 2) {
            total -= valor_frete;
            primeira_parcela = valor_frete;
        } else if (cobranca_frete == 3) {
            total -= valor_outras_despesas;
            primeira_parcela = valor_outras_despesas;
        } else if (cobranca_frete == 4) {
            total -= valor_seguro;
            primeira_parcela = valor_seguro;
        } else if (cobranca_frete == 5) {
            total -= valor_tributos;
            primeira_parcela = valor_tributos;
        }

        if (start < numParcelas) {
            let valorParcela = parseFloat(total) / numParcelas;

            for (let i = 0; i < numParcelas; i++) {
                let parcelaValor = (i === 0) ? valorParcela + primeira_parcela : valorParcela;
                $('#valor_parcela_' + i).val(parcelaValor.toFixed($('#decimal_prod_unit_vendas').val()).replace('.', ','));
            }

            // Se for um cálculo a partir de um start diferente
            if (start > 0) {
                let fixos = 0;
                for (let i = 0; i < start; i++) {
                    fixos += parseFloat($('#valor_parcela_' + i).val().replace(/\./g, '').replace(',', '.')) || 0;
                }

                let valorRestante = parseFloat(total - fixos) / (numParcelas - start);
                for (let i = start; i < numParcelas; i++) {
                    $('#valor_parcela_' + i).val(valorRestante.toFixed($('#decimal_prod_unit_vendas').val()).replace('.', ','));
                }

                let ajuste = $('#valor_parcela_' + (start - 1)).val();
                ajuste = parseFloat(ajuste.replace(/\./g, '').replace(',', '.')).toFixed($('#decimal_prod_unit_vendas').val()).replace('.', ',');
                $('#valor_parcela_' + (start - 1)).val(ajuste);
            }
        }
    }).catch(error => {
        console.error("Erro ao buscar forma de pagamento:", error);
    });
}

function modalPadronizado(id = null){
    if(id != null){
        let pos = null
        for(let i = 0; i < $('.produtos_id').length; i++){
            if($('.produtos_id')[i].value == id){
                pos = i
                break
            }
        }

        let prod_id = $('.produtos_id')[pos].value
        let prod_seq = $('.produtos_seq')[pos].value
        let prod_qtd = $('.produtos_qtd')[pos].innerText
        let prod_preco = $('.produtos_preco')[pos].innerText
        let prod_desc = $('.produtos_desc')[pos].innerText
        let prod_total = $('.produtos_total')[pos].innerText

        $('#produto_ref').text('')
        $('#preco_ref').text('')
        $('#produto_id').val(prod_id)
        //$('#m_quantidade').val(parseFloat(prod_qtd).toFixed($('#decimal_prod_unit_vendas').val()).replace('.',','))
        $('#m_quantidade').val(parseInt(prod_qtd))
        $('#m_preco').val(prod_preco)
        $('#m_per_desc').val(0)
        $('#m_desconto').val(prod_desc)
        $('#m_total').val(prod_total)

        $('#position').val(pos)
        
        GetProdutoId(false)
        
        $('#modal-produto').modal('show')
    }else{
        $('#padronizado_id').val('')
        $('#mp_quantidade').val(1)

        $('#padronizado_ref').text('')
        $('#mp_preco_ref').text('')

        // $('#mp_preco').val(0)
        // $('#mp_per_desc').val(0)
        // $('#mp_desconto').val(0)
        // $('#mp_total').val(0)
        
        $('#position').val('')
        
        $('#modal-padronizado').modal('show')

        $('#modal-padronizado').on('shown.bs.modal', function () {
            $('#padronizado_id').focus();
        })
    }

    // $("#m_tabela").val($('#tabela_id').val());
    // $("#tabela_ref").text($('#tabela').text());

}

function GetPadronizadoId() {
    $('#padronizado_spin').show()
    if($('#forma_pagamento_id').val() != ''){
        api_get('/padronizado/'+$('#padronizado_id').val(), {}).then(response => {
            $('#padronizado_spin').hide()
            
            if(response != null){
                $('#padronizado_ref').text(response.descricao);
                $('#mp_preco_ref').text('R$ '+getDecimal(response.valor_total));
                $('#padronizado_p').text('')
            }else{
                $('#padronizado_id').val('')
                $('#mp_preco_ref').text('')
                $('#padronizado_p').text('Padronizado não encontrado')
            }
        })
    }else{
        $('#padronizado_ref').text('')
        $('#mp_preco_ref').text('')
    }
    //$('#padronizado_spin').hide()
}

function getPadronizados() {
    $( "#modalAddPadronizado" ).modal( "show" );
    $('#datatable-add_padronizado').DataTable().destroy();
    
    // if($('#m_tabela').val() != ''){
    //     headers.body = { tabela: $('#m_tabela').val() }
    // }
    
    var dt_add = $("#datatable-add_padronizado").DataTable({
        "responsive": true, 
        "lengthChange": true, 
        "pageLength" : 10,
        "lengthMenu": [ 10, 25, 50, 75, 100 ],
        "autoWidth": false,
        "ordering": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
        },
        'columnDefs': [
            { "responsivePriority": 1, "targets": 1 },
            { "responsivePriority": 2, "targets": 2 },
            //{ "responsivePriority": 3, "targets": 3 }
        ],
        "searching": true,
        "aaSorting": [ [1,'asc'] ],
        "processing": true,
        "serverSide": true,
        "initComplete": function () {
            $('#datatable-add_padronizado_filter label input').focus();
        },
        "ajax": async (data, callback) => {
            var column_to_order = "";
            if(data.order[0].column == 0)
                column_to_order = "codigo";
            if(data.order[0].column == 1)
                column_to_order = "descricao";
            if(data.order[0].column == 2)
                column_to_order = "preco";

            var params_get = {
                prod_serv: 'P',
                ativo: 'S'
            };

            api_get('/padronizado/', params_get).then(response => {
                const objs = [];
                
                $.each(response.data, function (index, value) {
                    // let preco = null
                    // if(value.tabela != undefined){
                    //     preco = value.tabela.produto.preco
                    // }

                    let obj = [];
                    obj.push(value.codigo);
                    obj.push(value.descricao);
                    obj.push(getDecimal(value.valor_total));
                    objs.push(obj);
                });
                var json_data = {
                    "draw"        : data.draw,  
                    "recordsTotal"    : response.total,  
                    "recordsFiltered" : response.total, 
                    "data"        : objs
                };
                
                callback(json_data);
            })
        }
    });
    
    $('#datatable-add_padronizado tbody').on('click', 'tr', function () {
    //     $("#m_desconto").val(0);

    //     let produto = {
    //         codigo: this.childNodes[0].innerText,
    //         descricao: this.childNodes[1].innerText,
    //         tabela: {
    //             produto: {
    //                 preco: this.childNodes[3].innerText,
    //                 preco_minimo: 0,
    //                 promocional_marcado: 'N',
    //                 promocao: 0
    //             }
    //         }
    //     }
        SetPadronizado(this.childNodes)
    //     // CalculaValoresProduto()
        $( "#modalAddPadronizado" ).modal( "hide" );
    });

}

function SetPadronizado(padronizado){
    $("#padronizado_id").val(padronizado[0].innerText);
    $("#padronizado_ref").text(padronizado[1].innerText);
    $("#mp_preco").val(padronizado[2].innerText);
    $("#mp_preco_ref").text('R$ '+padronizado[2].innerText);
    
}

function modalPadronizadoSalvar(){

    $("#btnPadronizadoAdd").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...')
    $('#btnPadronizadoAdd').attr('disabled', true)  

    api_get('/padronizado/'+$('#padronizado_id').val(), {}).then(async response => {
        for(let i = 0; i < response.itens.length; i++){
            let mp_quantidade = $('#mp_quantidade').val().replace('.','').replace(',','.')
            let per_desc = response.itens[i].valor_desconto / response.itens[i].valor_total * 100

            let item = {
                id: response.itens[i].produto.codigo,
                quantidade: getDecimal(response.itens[i].quantidade * mp_quantidade),
                preco: getDecimal(response.itens[i].valor_unitario),
                per_desc: getDecimal(per_desc),
                desconto: getDecimal(response.itens[i].valor_desconto),
                total: getDecimal(response.itens[i].valor_total * mp_quantidade),
            }

            await ProdutoSalvar(item)
        }

        ProdutoTotais()
        Impostos()

        $("#btnPadronizadoAdd").html('Adicionar')
        $('#btnPadronizadoAdd').attr('disabled', false)  
    })

    if($('#position').val() != ''){
        $('#modal-produto').modal('hide')
    }else{
        modalPadronizado()
    }
}

function produtoRemove(id){
    let strct = id.split('_')
    $('#'+strct[strct.length-1]).remove()
    ProdutoTotais()
}

function modalServico(id = null){
    if(id != null){
        let pos = null
        for(let i = 0; i < $('.servicos_seq').length; i++){
            if($('.servicos_seq')[i].value == id){
                pos = i
                break
            }
        }
        
        let serv_id = $('.servicos_id')[pos].value
        let serv_seq = $('.servicos_seq')[pos].value
        let serv_qtd = $('.servicos_qtd')[pos].innerText
        let serv_preco = $('.servicos_preco')[pos].innerText
        let serv_desc = $('.servicos_desc')[pos].innerText
        let serv_total = $('.servicos_total')[pos].innerText

        $('#servico_id').val(serv_id)
        GetServicoId(false)

        $('#servico_ref').text('')
        $('#servico_preco_ref').text('')
        $('#m_servico_qtd').val(parseFloat(serv_qtd).toFixed($('#decimal_prod_unit_vendas').val()).replace('.',','))
        $('#m_servico_preco').val(serv_preco)
        $('#m_servico_per_desc').val(0)
        $('#m_servico_desconto').val(serv_desc)
        $('#m_servico_total').val(serv_total)

        $('#position').val(pos)

        $('#modal-servico').modal('show')
    }else{
        $('#position').val('')

        $('#servico_id').val('')
        $('#m_servico_qtd').val('1,00')
        $('#m_servico_preco').val('')
        $('#m_servico_per_desc').val('0,00')
        $('#m_servico_desconto').val('0,00')
        $('#m_servico_total').val('')
        $('#servico_ref').val('')
        $('#servico_preco_ref').val('')
        
        $('#modal-servico').modal('show')

        $('#modal-servico').on('shown.bs.modal', function () {
            $('#servico_id').focus();
        })
    }
}

function modalServicoSalvar(){
    let id = $('#servico_id').val()
    let quantidade = $('#m_servico_qtd').val()
    let preco = $('#m_servico_preco').val()
    let per_desc = $('#m_servico_per_desc').val()
    let desconto = $('#m_servico_desconto').val()
    let total = $('#m_servico_total').val()

    if(id != ''){
        $("#btnServicoAdd").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...')
        $('#btnServicoAdd').attr('disabled', true)
        api_get('/servico/'+id, {}).then(response => {
            if(response.codigo != undefined){
                let pos = $('#position').val()
                if(pos == ''){
                    let sequencia = 1
                    if($('.servico_sequencia').length > 0){
                        sequencia = parseInt($('.servico_sequencia')[$('.servico_sequencia').length-1].innerText) + 1
                    }

                    let tr = $("<tr class='servico_tr' id='"+sequencia+"'>")
                    
                    tr.append($('<td class="servico_sequencia">').text(sequencia))

                    let td = $('<td>').text(response.codigo)
                    td.append($("<input type='hidden' class='servicos_id'>").val(response.codigo))
                    td.append($("<input type='hidden' class='servicos_seq'>").val(sequencia))
                    
                    let html = '<div class="btn-group">'
                    html += '    <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown">'
                    html += '        <span class="sr-only">Toggle Dropdown</span>'
                    html += '    </button>'
                    html += '    <div class="dropdown-menu" role="menu">'
                    html += '        <a class="dropdown-item editar_servico text-primary" href="javascript:void(0)" id="editar_servico_'+sequencia+'">Editar</a>'
                    html += '        <a class="dropdown-item remover_servico text-danger" href="javascript:void(0)" id="remover_servico_'+sequencia+'">Excluir</button>'
                    html += '    </div>'
                    html += '</div>'

                    tr.append(td)
                    tr.append($('<td>').text(response.aplicacao))
                    tr.append('<td><span class="servicos_qtd">'+quantidade+'</span></tr>')
                    tr.append('<td><span class="servicos_preco">'+preco+'</span></tr>')
                    tr.append('<td><span class="servicos_p_desc">'+per_desc+'</span></tr>')
                    tr.append('<td><span class="servicos_desc">'+desconto+'</span></tr>')
                    tr.append('<td><span class="servicos_total">'+total+'</span></tr>')
                    tr.append($('<td>').append(html))

                    $('#datatable-servicos').append(tr)
                } else{
                    $('.servico_tr')[pos].id = response.codigo
                    $('.servicos_id')[pos].innerText = response.codigo
                    $('.servicos_qtd')[pos].innerText = quantidade
                    $('.servicos_preco')[pos].innerText = preco
                    //$('.servicos_p_desc')[pos].innerText = per_desc
                    $('.servicos_desc')[pos].innerText = desconto
                    $('.servicos_total')[pos].innerText = total
                    $('.editar_servico')[pos].id = "editar_servico"+response.codigo
                    $('.remover_servico')[pos].value = "remover_servico"+response.codigo

                    $('#modal-servico').modal('hide')
                }

                ServicoTotais()
            }

            $("#btnServicoAdd").html('Adicionar')
            $('#btnServicoAdd').attr('disabled', false)
        })
    }
}

function GetServicoId(override = true){
    let params = {
        ativo: 'S'
    }
    
    $('#servico_spin').show()
    api_get('/servico/'+$('#servico_id').val(), params).then(response => {
        $('#servico_spin').hide()
        
        if(response != null){
            SetServico(response, override)
            $('#servico_p').text('')

            CalculaValoresServico()
        }else{
            $('#servico_id').val('')
            $('#m_servico_qtd').val(1)
            $('#m_servico_preco').val('')
            $('#m_servico_per_desc').val('')
            $('#m_servico_desconto').val('')
            $('#m_servico_total').val('')
            $('#servico_ref').val('')
            $('#servico_preco_ref').val('')
            $('#servico_p').text('Servico não encontrado')
        }
    })
}

function getServicos() {
    $( "#modalAddServico" ).modal( "show" );
    $('#datatable-add_servico').DataTable().destroy();
    
    var dt_add = $("#datatable-add_servico").DataTable({
        "responsive": true, 
        "lengthChange": true, 
        "pageLength" : 10,
        "lengthMenu": [ 10, 25, 50, 75, 100 ],
        "autoWidth": false,
        "ordering": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
        },
        'columnDefs': [
            { "responsivePriority": 1, "targets": 1 },
            { "responsivePriority": 2, "targets": 2 },
            { "responsivePriority": 3, "targets": 3 }
        ],
        "searching": true,
        "aaSorting": [ [1,'asc'] ],
        "processing": true,
        "serverSide": true,
        "initComplete": function () {
            $('#datatable-add_servico_filter label input').focus();
        },
        "ajax": async (data, callback) => {
            var column_to_order = "";
            if(data.order[0].column == 0)
                column_to_order = "codigo";
            if(data.order[0].column == 1)
                column_to_order = "aplicacao";
            if(data.order[0].column == 2)
                column_to_order = "tipo_serv";
            if(data.order[0].column == 3)
                column_to_order = "valor";

            var params_get = {
                ativo: 'S'
            };

            api_get('/servico/', params_get).then(response => {
                const objs = [];
                
                $.each(response.data, function (index, value) {
                    let preco = null
                    if(value.tabela != undefined){
                        preco = value.tabela.produto.preco
                    }

                    let obj = [];
                    obj.push(value.codigo);
                    obj.push(value.aplicacao);
                    obj.push(value.tipo_serv);
                    obj.push(value.valor);
                    objs.push(obj);
                });
                var json_data = {
                    "draw"        : data.draw,  
                    "recordsTotal"    : response.total,  
                    "recordsFiltered" : response.total, 
                    "data"        : objs
                };
                
                callback(json_data);
            })
        }
    });

    $('#datatable-add_servico tbody').on('click', 'tr', function () {
        $("#m_servico_desconto").val(0);
        let servico = {
            codigo: this.childNodes[0].innerText,
            aplicacao: this.childNodes[1].innerText,
            valor: this.childNodes[3].innerText,
        }
        SetServico(servico)
        CalculaValoresServico()
        $( "#modalAddServico" ).modal( "hide" );
    });

}

function SetServico(servico, override = true){
    if(servico != undefined){
        if(servico.codigo != undefined){
            if(override){
                $('#m_servico_preco').val(0)
                $('#m_servico_per_desc').val(0)
                $("#servico_id").val(servico.codigo)
            }
            $('#servico_ref').text(servico.codigo + ' / ' + servico.aplicacao);
            $('#preco_ref').text('Preço: R$' + parseFloat(servico.valor).toFixed(2).replace('.',','));
        }
    }
}

function CalculaValoresServico(campo = null){
    let quantidade = parseFloat($('#m_servico_qtd').val().replace('.','').replace(',','.'))
    let preco = parseFloat($('#m_servico_preco').val().replace('.','').replace(',','.'))||0
    let per_desc = parseFloat($('#m_servico_per_desc').val().replace('.','').replace(',','.'))
    let desconto = parseFloat($('#m_servico_desconto').val().replace('.','').replace(',','.'))
    let desc_max = parseFloat(localStorage.getItem('desconto_maximo'))
    
    if(campo == 'desconto'){
        per_desc = desconto/preco*100
    }

    if(per_desc > desc_max){
        per_desc = desc_max
        $('#m_servico_per_desc').val(DecimalInterno(per_desc, $('#decimal_prod_unit_vendas').val()))
    }
    desconto = (per_desc / 100 * preco)
    
    $('#m_servico_desconto').val(DecimalInterno(desconto, $('#decimal_prod_unit_vendas').val()))
    $('#m_servico_total').val(DecimalInterno((preco - parseFloat(desconto)) * quantidade, $('#decimal_prod_unit_vendas').val()))
}

function servicoRemove(id){
    let strct = id.split('_')
    $('#'+strct[strct.length-1]).remove()
    ServicoTotais()
}

function ServicoTotais(loadTotal = true){
    let preco = 0;
    let desconto = 0;
    let quantidade = 0;

    let servicos_qtd = $('.servicos_qtd');
    for(let i = 0; i < servicos_qtd.length; i++){
        quantidade += parseFloat(servicos_qtd[i].innerText.replace('.','').replace(',','.'))
    }
    
    let servicos_preco = $('.servicos_preco');
    for(let i = 0; i < servicos_preco.length; i++){
        preco += (
            parseFloat(servicos_preco[i].innerText.replace('.','').replace(',','.')) *
            parseFloat(servicos_qtd[i].innerText.replace('.','').replace(',','.'))
        )
    }

    let servicos_desc = $('.servicos_desc');
    for(let i = 0; i < servicos_desc.length; i++){
        desconto += parseFloat(servicos_desc[i].innerText.replace('.','').replace(',','.') *
            servicos_qtd[i].innerText.replace('.','').replace(',','.')
        )
    }

    let per_desconto_servico = 0
    if(preco > 0){
        per_desconto_servico = desconto/preco*100
    }
    
    $('#servicos_desc').text(getDecimal(desconto))
    $('#servicos_total').text(getDecimal(preco))

    $('#qtde_servicos').val(quantidade.toFixed($('#decimal_prod_qtde_vendas').val()).replace('.',','))
    $('#total_servico').val(preco.toFixed($('#decimal_prod_unit_vendas').val()).replace('.',','))
    $('#desconto_servico').val((preco - desconto).toFixed($('#decimal_prod_unit_vendas').val()).replace('.',','))
    $('#per_desconto_servico').val(per_desconto_servico.toFixed($('#decimal_prod_unit_vendas').val()).replace('.',','))
    
    if(loadTotal){
        TotalGeral()
    }
}

async function modalImpostosProduto(id) {
    let imposto = await GetImpostos(id);
    
    if (imposto && imposto.produtos.length > 0) {
        let produto = imposto.produtos.find(p => p.produto_id == id);
        
        if (produto) {
            // Tributos Federais
            $('#m_base_ipi').text(Decimal(produto.base_ipi, $('#decimal_prod_valor_vendas').val()));
            $('#m_valor_ipi').text(Decimal(produto.valor_ipi, $('#decimal_prod_valor_vendas').val()));
            $('#m_regra_pis_cofins').text(produto.regra_pis_cofins);
            $('#m_aliquota_pis').text(Decimal(produto.aliquota_pis, $('#decimal_prod_valor_vendas').val()));
            $('#m_incidencia_pis').text(Decimal(produto.incidencia_pis, $('#decimal_prod_valor_vendas').val()));
            $('#m_base_pis').text(Decimal(produto.base_pis, $('#decimal_prod_valor_vendas').val()));
            $('#m_valor_pis').text(Decimal(produto.valor_pis, $('#decimal_prod_valor_vendas').val()));
            $('#m_cst_pis').text(produto.cst_pis);
            $('#m_aliquota_cofins').text(Decimal(produto.aliquota_cofins, $('#decimal_prod_valor_vendas').val()));
            $('#m_incidencia_cofins').text(Decimal(produto.incidencia_cofins, $('#decimal_prod_valor_vendas').val()));
            $('#m_base_cofins').text(Decimal(produto.base_cofins, $('#decimal_prod_valor_vendas').val()));
            $('#m_valor_cofins').text(Decimal(produto.valor_cofins, $('#decimal_prod_valor_vendas').val()));
            $('#m_cst_cofins').text(produto.cst_cofins);

            // Tributos Estaduais
            $('#m_regra_icms').text(produto.regra_icms);
            $('#m_aliquota_icms').text(Decimal(produto.aliquota_icms, $('#decimal_prod_valor_vendas').val()));
            $('#m_incidencia_icms').text(Decimal(produto.incidencia_icms, $('#decimal_prod_valor_vendas').val()));
            $('#m_base_icms').text(Decimal(produto.base_icms, $('#decimal_prod_valor_vendas').val()));
            $('#m_valor_icms').text(Decimal(produto.valor_icms, $('#decimal_prod_valor_vendas').val()));
            $('#m_cst_icms').text(produto.cst_icms);
            $('#m_aliquota_icms_subtracao').text(Decimal(produto.aliquota_icms_subtracao, $('#decimal_prod_valor_vendas').val()));
            $('#m_incidencia_icms_subtracao').text(Decimal(produto.incidencia_icms_subtracao, $('#decimal_prod_valor_vendas').val()));
            $('#m_aliquota_icmsst').text(Decimal(produto.aliquota_icmsst, $('#decimal_prod_valor_vendas').val()));
            $('#m_incidencia_icmsst').text(Decimal(produto.incidencia_icmsst, $('#decimal_prod_valor_vendas').val()));
            $('#m_margem_icmsst').text(Decimal(produto.margem_icmsst, $('#decimal_prod_valor_vendas').val()));
            $('#m_pauta_icmsst').text(Decimal(produto.pauta_icmsst, $('#decimal_prod_valor_vendas').val()));
            $('#m_base_icmsst').text(Decimal(produto.base_icmsst, $('#decimal_prod_valor_vendas').val()));
            $('#m_valor_icmsst').text(Decimal(produto.valor_icmsst, $('#decimal_prod_valor_vendas').val()));
            $('#m_cst_icmsst').text(produto.cst_icmsst);
            $('#m_regra_fcp').text(produto.regra_fcp);
            $('#m_aliquota_fcp').text(Decimal(produto.aliquota_fcp, $('#decimal_prod_valor_vendas').val()));
            $('#m_base_fcp').text(Decimal(produto.base_fcp, $('#decimal_prod_valor_vendas').val()));
            $('#m_valor_fcp').text(Decimal(produto.valor_fcp, $('#decimal_prod_valor_vendas').val()));
            $('#m_aliquota_difal').text(Decimal(produto.aliquota_difal, $('#decimal_prod_valor_vendas').val()));
            $('#m_base_difal').text(Decimal(produto.base_difal, $('#decimal_prod_valor_vendas').val()));
            $('#m_valor_difal').text(Decimal(produto.valor_difal, $('#decimal_prod_valor_vendas').val()));
        } else {
            console.warn("Produto não encontrado na resposta do imposto.");
        }
    } else {
        console.warn("Nenhum imposto encontrado para o produto especificado.");
    }

    $('#modal-impostos').modal('show');
}

function GetTransportadoraId(){
    $('#transportadora_spin').show()

    if($('#transportadora_id').val() != ''){
        api_get('/fornecedor/'+$('#transportadora_id').val(), { ativ_empr: 'P' }).then(response => {
            if(response != null){
                $('#transportadora').text(response.nome_fantasia);
                $('#transportadora_p').text('')
            }else{
                $('#transportadora_id').val('')
                $('#transportadora').text('')
                $('#transportadora_p').text('Transportadora não encontrada')
            }
        })
    }else{
        $('#transportadora').text('')
    }

    $('#transportadora_spin').hide()
}

function GetTransportadora() {
    $( "#modal-transportadora" ).modal( "show" );

    $('#datatable-transportadora').DataTable().destroy();
    $("#datatable-transportadora").DataTable({
        "responsive": true, 
        "select": true,
        "columnDefs": [
            { "responsivePriority": 1, "targets": 0 },
            { "responsivePriority": 2, "targets": 1 },
            { "responsivePriority": 3, "targets": 2 }
        ],
        "lengthChange": true, 
        "pageLength" : 10,
        "lengthMenu": [ 10, 25, 50, 75, 100 ],
        "autoWidth": false,
        "ordering": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
        },
        "searching": true,
        "aaSorting": [ [0,'asc'] ],
        "processing": true,
        "serverSide": true,
        "initComplete": function () {
              $('#datatable-transportadora_filter label input').focus();
        },
        "ajax": async (data, callback) => {
            var column_to_order = "";
            if(data.order[0].column == 0)
                column_to_order = "codigo";
            if(data.order[0].column == 1)
                column_to_order = "razao_social";
            if(data.order[0].column == 2)
                column_to_order = "nome_fantasia";

            var params_get = { 
                offset: data.start, 
                limit: data.length, 
                ordem: JSON.stringify([{campo : column_to_order, ordem : data.order[0].dir}]),
                texto: data.search.value,
                ativ_empr: 'P'
            };

            api_get('/fornecedor', params_get).then(response => {
                const objs = [];
                
                $.each(response.data, function (index, value) {
                    let obj = [];
                    obj.push(value.codigo);
                    obj.push(value.razao_social);
                    obj.push(value.nome_fantasia);
                    objs.push(obj);
                });

                var json_data = {
                    "draw"        : data.draw,  
                    "recordsTotal"    : response.total,  
                    "recordsFiltered" : response.total, 
                    "data"        : objs
                };
                
                callback(json_data);
            })
        }
    });

    $('#datatable-transportadora tbody').on('click', 'tr', function () {
        $("#transportadora_id").val(this.childNodes[0].innerText);
        $("#transportadora").text(this.childNodes[0].innerText + " / s" + this.childNodes[2].innerText);
        $( "#modal-transportadora" ).modal( "hide" );
    });
}

function GetMarketplaceId(){
    $('#intermediador_marketplace_spin').show()
    if($('#intermediador_marketplace_id').val() != ''){
        api_get('/fornecedor/'+$('#intermediador_marketplace_id').val(), { ativo: 'S' }).then(response => {
            $('#intermediador_marketplacespin').hide()
            
            if(response != null){
                $('#intermediador_marketplace').text(response.nome_fantasia);
                $('#intermediador_marketplace_p').text('')
            }else{
                $('#intermediador_marketplace_id').val('')
                $('#intermediador_marketplace').text('')
                $('#intermediador_marketplace_p').text('Marketplace não encontrado')
            }
        })
    }else{
        $('#intermediador_marketplace').text('')
    }
    $('#intermediador_marketplace_spin').hide()
}

function GetMarketplace() {
    $( "#modal-marketplace" ).modal( "show" );

    $('#datatable-marketplace').DataTable().destroy();
    dtFornecedor(true, "#datatable-marketplace");
    
    $('#datatable-marketplace tbody').on('click', 'tr', function () {
        $("#intermediador_marketplace_id").val(this.childNodes[0].innerText);
        $("#intermediador_marketplace").text(this.childNodes[0].innerText + " / " + this.childNodes[2].innerText);
        $( "#modal-marketplace" ).modal( "hide" );
    });
}

function GetInterPagamentoId(){
    $('#intermediador_pagamento_spin').show()
    api_get('/fornecedor/'+$('#intermediador_pagamento_id').val(), { ativo: 'S' }).then(response => {
        $('#intermediador_pagamento_spin').hide()
        
        if(response != null){
            $('#intermediador_pagamento').text(response.nome_fantasia);
            $('#intermediador_pagamento_p').text('')
        }else{
            $('#intermediador_pagamento_id').val('')
            $('#intermediador_pagamento').text('')
            $('#intermediador_pagamento_p').text('Marketplace não encontrado')
        }
    })
}

function GetInterPagamento() {
    $( "#modal-inter_pagamento" ).modal( "show" );

    $('#datatable-inter_pagamento').DataTable().destroy();
    dtFornecedor(true, "#datatable-inter_pagamento");
    
    $('#datatable-inter_pagamento tbody').on('click', 'tr', function () {
        $("#intermediador_pagamento_id").val(this.childNodes[0].innerText);
        $("#intermediador_pagamento").text(this.childNodes[0].innerText + " / " + this.childNodes[2].innerText);
        $( "#modal-inter_pagamento" ).modal( "hide" );
    });
}

function GetFormaPagamentoId(){
    $('#forma_pagamento_spin').show()
    if($('#forma_pagamento_id').val() != ''){
        api_get('/forma_pagamento/'+$('#forma_pagamento_id').val(), {}).then(response => {
            $('#forma_pagamento_spin').hide()
            
            if(response != null){
                $('#forma_pagamento').text(response.descricao);
                $('#forma_pagamento_p').text('')
            }else{
                $('#forma_pagamento_id').val('')
                $('#forma_pagamento').text('')
                $('#forma_pagamento_p').text('Forma de pagamento não encontrado')
            }
            SetParcelas()
        })
    }else{
        $('#forma_pagamento').text('')
    }
    $('#forma_pagamento_spin').hide()
}

function GetFormaPagamento() {
    $( "#modal-forma_pagamento" ).modal( "show" );

    $('#datatable-forma_pagamento').DataTable().destroy();
    dtFormaPagamento(true, "#datatable-forma_pagamento");
    
    
}
$('#datatable-forma_pagamento tbody').on('click', 'tr', function () {
    $("#forma_pagamento_id").val(this.childNodes[0].innerText);
    $("#forma_pagamento").text(this.childNodes[1].innerText);
    $( "#modal-forma_pagamento" ).modal( "hide" );

    SetParcelas()
});

function IndicadorPresenca(){
    if($('#indicador_presenca').val() >= 2){
        $('#intermediador_marketplace_span').show()
        $('#intermediador_pagamentos_span').show()
    }else{
        $('#intermediador_marketplace_span').hide()
        $('#intermediador_pagamentos_span').hide()
    }
}

function FormatarData(data){
    const yyyy = data.getFullYear();
    let mm = data.getMonth() + 1; // Months start at 0!
    let dd = data.getDate()

    if (dd < 10) dd = '0' + dd;
    if (mm < 10) mm = '0' + mm;

    //return dd + '/' + mm + '/' + yyyy;
    return [yyyy,mm,dd].join('-')
}

function SetParcelas() {
    var tabName = $('#tb_parcelas').prop("tagName");

    $('#tb_parcelas').html(''); // Limpa a tabela/lista

    let formaPagamentoId = $('#forma_pagamento_id').val(); // Obtém o ID da forma de pagamento selecionada

    if (!formaPagamentoId) {
        console.error("ID da forma de pagamento não encontrado.");
        return;
    }

    api_get(`/forma_pagamento/${formaPagamentoId}`, {}).then(formaPagamento => {
        if (!formaPagamento) {
            console.error("Forma de pagamento não encontrada para o ID:", formaPagamentoId);
            return;
        }

        let numParcelas = parseInt(formaPagamento.num_parcelas) || 1;
        let intervalo = parseInt(formaPagamento.intervalo) || 30;
        let diasEntrada = parseInt(formaPagamento.dias_entrada) || 0;

        let valorTotal = 0;
        if ($('#total_geral').length && $('#total_geral').val()) {
            valorTotal = parseFloat($('#total_geral').val().replace(/\./g, '').replace(',', '.')) || 0;
        } else {
            valorTotal = parseFloat($('#m_total').val()) || 0;
        }
        let valorParcela = (valorTotal / numParcelas).toFixed(2); // Divide igualmente entre as parcelas

        let data = new Date();
        data.setDate(data.getDate() + diasEntrada); // Ajusta data da primeira parcela

        for (let i = 0; i < numParcelas; i++) {
            let select = "<select id='parcela_tipo_recebimento_" + i + "' class='form-control form-control-sm parcelas_tipo_recebimento'></select>";

            let el;
            if (tabName === 'TBODY') {
                el = $('<tr>');
                el.append('<td><input type=hidden class="parcelas_seq" value=' + (i + 1) + '>' + (i + 1) + '</td>');
                el.append('<td><input id=valor_parcela_' + i + ' class="form-control form-control-sm decimalUnitVendas parcelas_valor" value="' + valorParcela + '"></td>');
                el.append('<td><input id=parcela_vencimento_' + i + ' type=date class="form-control form-control-sm parcelas_vencimento" value=' + FormatarData(data) + '></td>');
                el.append('<td><span id=parcela_semana_' + i + '>' + semana[data.getDay()] + '</span></td>');
                el.append('<td><span id=parcela_span_' + i + '>' + PrazoParcela(data) + '</span></td>');
                el.append('<td>' + select + '</td>');
            } else {
                el = $('<li class="list-group-item">');
                el.append('<b>Parcela:</b> ' + (i + 1) +
                    '<input type=hidden class="parcelas_seq" value="' + (i + 1) + '">' +
                    '<br>' +
                    '<div class="row">' +
                    '  <div class="col-6">' +
                    '    <label class="mb-0 small">Valor</label>' +
                    '    <div class="input-group input-group-sm mb-2">' +
                    '      <div class="input-group-prepend"><span class="input-group-text">R$</span></div>' +
                    '      <input id="valor_parcela_' + i + '" class="form-control decimalUnitVendas parcelas_valor" value="' + valorParcela + '">' +
                    '    </div>' +
                    '  </div>' +
                    '  <div class="col-6">' +
                    '    <label class="mb-0 small">Vencimento</label>' +
                    '    <input id="parcela_vencimento_' + i + '" type="date" class="form-control form-control-sm parcelas_vencimento" value="' + FormatarData(data) + '">' +
                    '  </div>' +
                    '</div>' +
                    '<div class="row mb-2">' +
                    '  <div class="col-6">' +
                    '    <small><b>Dia:</b> <span id="parcela_semana_' + i + '">' + semana[data.getDay()] + '</span></small>' +
                    '  </div>' +
                    '  <div class="col-6">' +
                    '    <small><b>Prazo:</b> <span id="parcela_span_' + i + '">' + PrazoParcela(data) + '</span> dias</small>' +
                    '  </div>' +
                    '</div>' +
                    '<div class="form-group mb-0">' +
                    '  <label class="mb-0 small">Tipo de Recebimento</label>' +
                    '  ' + select + 
                    '</div>');
            }

            $('#tb_parcelas').append(el);

            // Adiciona intervalo de dias para a próxima parcela (exceto na última)
            if (i < numParcelas - 1) {
                data.setDate(data.getDate() + intervalo);
            }
        }

        CalculaValorParcelas();
        ParcelasEventos();
    }).catch(error => {
        console.error("Erro ao buscar forma de pagamento:", error);
    });
}

let semana = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado']
function PrazoParcela(data, id){
    let dia = data.getDay()+1<=6?data.getDay()+1:0
    $('#parcela_semana_'+id).text(semana[dia])

    let agora = new Date()
    agora.setDate(agora.getDate()-1)
    let prazo = Math.round((data - agora) / (1000 * 60 * 60 * 24))
    let valor = prazo < 0 ? 0 : prazo
    $('#parcela_span_'+id).text(valor)

    return valor
}

async function ParcelasEventos(){
    let tipo_recebimento = await api_get('/tipo_recebimento/', { ativo: 'S', filial: $('#filial_id').val(), cliente: $('#cliente_id').val() })
   
    if(tipo_recebimento != null){
        let parcelas = $('.parcelas_seq')
        for(let i = 0; i < parcelas.length; i++){
            let select = ''
            for(let x = 0; x < tipo_recebimento.data.length; x++){
                let selected = ''
                if(tipo_recebimento.data[x].codigo == $('#parcela_tp_receb_'+(i+1)).val()){
                    selected = ' selected'
                }
                select += "<option value='"+tipo_recebimento.data[x].codigo+"'"+selected+">"+tipo_recebimento.data[x].nome+"</option>"
            }
            $('#parcela_tipo_recebimento_'+i).html(select)


            $('#parcela_vencimento_'+i).on('change',(e)=>{
                PrazoParcela(new Date(e.currentTarget.value), i)
            })
            $('#valor_parcela_'+i).on('change',()=>{
                CalculaValorParcelas(i+1)
            })
        }
    }
}

function SomaFreteSeguro() {
  let v_frete = parseFloat($('#valor_frete').val().replace('.', '').replace(',', '.'));
  let v_seguro = parseFloat($('#valor_seguro').val().replace('.', '').replace(',', '.'));
  $("#frete_seguro").val(
    (v_frete + v_seguro)
      .toFixed($("#decimal_prod_unit_vendas").val())
      .replace(".", ",")
  );

  TotalGeral();
}

export { TotalGeral, ProdutoTotais, ServicoTotais, CalculaValorParcelas, SetParcelas, SetProduto, CalculaValoresProduto }