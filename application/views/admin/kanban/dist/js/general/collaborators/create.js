import { datatable_html, datatable } from "../../financial/costs_center/utils/datatable.js";
import { datatable_html_sector, datatable_sector } from "../../purchase/sectors/utils/datatable.js";
import { api_post } from "../../helpers/api/post.js";
import { api_get } from "../../helpers/api/get.js";
import { get_collaborators_edit } from "./utils/list.js";

$(function () {
  $("input[data-bootstrap-switch]").each(function(){
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
  });

  $(".content-wrapper").append(datatable_html(true));
  $(".content-wrapper").append(datatable_html_sector(true));
  
  get_collaborators_edit()

  api_get('/pag_ini_usuario/').then(response => {
    let options = '<option value="0">Nenhum</option>';
    if (response.data) {
      response.data.forEach(item => {
        options += `<option value="${item.codigo}">${item.descricao}</option>`;
      });
    }
    $('select[name="pag_inicial"]').html(options);
  });

  $('form').on('submit', function(e){
    e.preventDefault();
    let chk_opcoes = $('.chk_opcoes')
    let permissoes = []
    for(let i = 0; i < chk_opcoes.length; i ++){
      if(chk_opcoes[i].checked){
        permissoes.push(chk_opcoes[i].value)
      }
    }

    var form = $(this)[0];
    var body = JSON.stringify({
      permissoes,
      cod_centrocusto: $('input[name="centrocusto_id"]').val(),
      nome: $('input[name="nome"]').val(),
      cpf: $('input[name="cpf"]').val(),
      rg: $('input[name="rg"]').val(),
      apelido: $('input[name="apelido"]').val(),
      ativo: $('[name="ativo"]').is(":checked") ? 'S' : 'N',
      pag_unica: $('[name="pag_unica"]').is(":checked") ? 1 : 0,
      pag_inicial: $('select[name="pag_inicial"]').val() !== '' ? $('select[name="pag_inicial"]').val() : 0,
      telefone: $('input[name="telefone"]').val(),
      celular: $('input[name="celular"]').val(),
      cep: $('input[name="cep"]').val(),
      endereco: $('input[name="endereco"]').val(),
      tipo: $('select[name="tipo"]').val(),
      data_nasc: $('input[name="data_nasc"]').val(),
      cidade: $('input[name="cidade"]').val(),
      estado: $('select[name="estado"]').val(),
      email: $('input[name="email"]').val(),
      complemento: $('input[name="complemento"]').val(),
      data_ativacao: $('input[name="data_ativacao"]').val(),
      pais: '1058'
    });
    
    api_post('/colaborador/', body).then(response => {
      toastr["success"]("Colaborador #"+response.codigo+" criado.", "Sucesso");
      window.history.back();
    });
  });
});

$( "#centrocusto" ).focus(function() {
      getCentroCusto();
});

function getCentroCusto() {
      $( "#modal-dt-costs_center" ).modal( "show" );

      if ( ! $.fn.DataTable.isDataTable( '#datatable-centrocusto' ) ) {
            var dt_centrocusto = datatable(true);
      }

      $('#modal-dt-costs_center').find('#datatable-centrocusto tbody').on('click', 'tr', function () {
            $("input[name='centrocusto_id']").val(dt_centrocusto.row( this ).data()[0]);
            $("input[name='centrocusto']").val(dt_centrocusto.row( this ).data()[0] + " - " + dt_centrocusto.row( this ).data()[1]);
            $( "#modal-dt-costs_center" ).modal( "hide" );
      });
}

$( "#setorestoque" ).focus(function() {
  getSetEstoque();
});

function getSetEstoque() {
  $( "#modal-dt-setors" ).modal( "show" );

  if ( ! $.fn.DataTable.isDataTable( '#datatable-sector' ) ) {
        var dt_setestoque = datatable_sector(true);
  }

  $('#modal-dt-setors').find('#datatable-sector tbody').on('click', 'tr', function () {
        $("input[name='setestoque_id']").val(dt_setestoque.row( this ).data()[0]);
        $("input[name='setestoque']").val(dt_setestoque.row( this ).data()[0] + " - " + dt_setestoque.row( this ).data()[1]);
        $( "#modal-dt-setors" ).modal( "hide" );
  });
}