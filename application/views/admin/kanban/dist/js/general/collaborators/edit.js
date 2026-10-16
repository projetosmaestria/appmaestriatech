import { datatable_html, datatable } from "../../financial/costs_center/utils/datatable.js";
import { api_get_id } from "../../helpers/api/get_id.js";
import { api_put } from "../../helpers/api/put.js";
import { api_get } from "../../helpers/api/get.js";
import { get_collaborators_edit } from "./utils/list.js";

$(function () {
  api_get('/pag_ini_usuario/').then(response_pag => {
    let options = '<option value="0">Nenhum</option>';
    if (response_pag.data) {
      response_pag.data.forEach(item => {
        options += `<option value="${item.codigo}">${item.descricao}</option>`;
      });
    }
    $('select[name="pag_inicial"]').html(options);

    api_get_id('/colaborador/', $("#id").val()).then(response => {
      if(response.ativo == 'S') {
        $("input[data-bootstrap-switch]").bootstrapSwitch('state', true);
      } else {
        $("input[data-bootstrap-switch]").bootstrapSwitch('state', false);
      }
      if(response.pag_unica) {
        $('[name="pag_unica"]').bootstrapSwitch('state', true);
      } else {
        $('[name="pag_unica"]').bootstrapSwitch('state', false);
      }
      $('select[name="pag_inicial"]').val(response.pag_inicial !== null && response.pag_inicial !== undefined ? response.pag_inicial : 0);
      $("input[name=nome]").val(response.nome);
      $("input[name=apelido]").val(response.apelido);
      $("select[name=tipo]").val(response.tipo);
      $("input[name=cpf]").val(response.cpf);
      $("input[name=rg]").val(response.rg);
      $('input[name="telefone"]').val(response.telefone);
      $('input[name="cep"]').val(response.cep);
      $('input[name="endereco"]').val(response.endereco);
      $('input[name="numero"]').val(response.numero);
      $('input[name="bairro"]').val(response.bairro);
      $('input[name="complemento"]').val(response.complemento);
      $('input[name="cidade"]').val(response.cidade);
      $('select[name="estado"]').val(response.estado);
      $('input[name="data_ativacao"]').val(response.data_ativacao);
      $('input[name="data_nasc"]').val(response.data_nasc);
    });
  });

  get_collaborators_edit()

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
      tipo: $('select[name="tipo"]').val(),
      tipo: $('[name="tipo"]').is(":selected") ? 'F' : 'V',
      nome: $('input[name="nome"]').val(),
      cpf: $('input[name="cpf"]').val(),
      rg: $('input[name="rg"]').val(),
      ativo: $('[name="ativo"]').is(":checked") ? 'S' : 'N',
      pag_unica: $('[name="pag_unica"]').is(":checked") ? 1 : 0,
      pag_inicial: $('select[name="pag_inicial"]').val() !== '' ? $('select[name="pag_inicial"]').val() : 0,
      telefone: $('input[name="telefone"]').val(),
      cep: $('input[name="cep"]').val(),
      endereco: $('input[name="logradouro"]').val(),
      numero: $('input[name="numero"]').val(),
      bairro: $('input[name="bairro"]').val(),
      complemento: $('input[name="complemento"]').val(),
      cidade: $('input[name="cidade"]').val(),
      estado: $('select[name="estado"]').val(),
      data_ativacao: $('select[name="data_ativacao"]').val(),
      data_nasc: $('input[name="data_nasc"]').val(),
      pais: '1058'
    });
    api_put('/colaborador/', $("#id").val(), body).then(response => {
      toastr["success"]("Colaborador #"+$("#id").val()+" editado.", "Sucesso");
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