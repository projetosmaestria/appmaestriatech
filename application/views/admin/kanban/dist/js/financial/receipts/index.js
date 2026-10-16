import { datatable_html, datatable, formatarData, formatarValor, determinarSituacao, montarAcoes } from "./utils/datatable.js";
import { filters } from "../../helpers/filters.js";
import { api_get } from "../../helpers/api/get.js";

var dataTable;

window.verificarAcesso = function(acao, url) {
    Swal.fire({
        title: 'Verificando...',
        text: 'Aguarde enquanto verificamos sua permissão.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(api_url + '/contas_receber/permissao/' + acao, {
        method: 'GET',
        headers: headers
    })
    .then(async (response) => {
        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Sem permissão');
        }
        return response.json();
    })
    .then(() => {
        Swal.close();
        location.href = url;
    })
    .catch((error) => {
        Swal.fire({
            icon: 'error',
            title: 'Acesso Negado',
            html: error.message
        });
    });
};

// Monta "código / nome" (ou variação) a partir de um objeto relacionado, tolerando valores nulos
function formatarRelacionado(obj, campoCodigo = "codigo", campoNome = "nome") {
  if (!obj) return "-";
  const codigo = obj[campoCodigo];
  const nome = obj[campoNome];
  const partes = [];
  if (codigo !== undefined && codigo !== null && codigo !== "") partes.push(codigo);
  if (nome) partes.push(nome);
  return partes.length ? partes.join(" / ") : "-";
}

function Box(id, $linha) {
  $(".datatable-container table tbody tr").removeClass("linha-selecionada");
  if ($linha) $linha.addClass("linha-selecionada");

  $("#detalhe-codigo").text(id);
  $("#detalhe-erro").hide();
  $("#detalhe-conteudo").hide();
  $("#detalhe-loading").show();
  $("#modalDetalheRecebimento").modal("show");

  api_get("/contas_receber/" + id, {})
    .then((response) => {
      $("#detalhe-loading").hide();
      $("#detalhe-conteudo").show();

      const situacao = determinarSituacao(response.vencimento, response.data_pagamento);
      $("#detalhe-situacao").html(situacao.html);

      const valorDestaque = formatarValor(response.valor_acrescimo ?? response.valor);
      $("#detalhe-valor-destaque").text(valorDestaque || "-");

      const acoesHtml =
        `<button type="button" onclick="verificarAcesso('editar', '${base_url}/financial/receipts/edit/${id}')" class="btn btn-sm btn-outline-secondary">Editar</button>` +
        montarAcoes(id, response.data_pagamento)
          .map((a) => a.replace("dropdown-item", "btn btn-sm btn-outline-secondary"))
          .join("");
      $("#detalhe-acoes").html(acoesHtml);

      $("#box_filial").text(formatarRelacionado(response.filial, "codigo", "nome_fantasia"));
      $("#box_documento").text(response.documento || "-");
      $("#box_num_bloqueto").text(response.num_bloqueto || "-");
      $("#box_serie").text(formatarRelacionado(response.serie));
      $("#box_cliente").text(formatarRelacionado(response.cliente));
      $("#box_vendedor").text(formatarRelacionado(response.vendedor));

      $("#box_vencimento").text(formatarData(response.vencimento) || "-");
      $("#box_lancamento").text(formatarData(response.data_lancamento) || "-");
      const pagamentoTexto = formatarData(response.data_pagamento);
      $("#box_pagamento").text(pagamentoTexto || "Em aberto");
      $("#detalhe-linha-pagamento").toggleClass("concluido", !!pagamentoTexto);
      $("#box_competencia").text(response.competencia || "-");

      $("#box_valor").text(formatarValor(response.valor) || "-");
      $("#box_tipo_recebimento").text(formatarRelacionado(response.tipo_recebimento));
      $("#box_acrescimo").text(formatarValor(response.acrescimo) || "-");
      $("#box_juros").text(formatarValor(response.juros) || "-");
      $("#box_desconto").text(formatarValor(response.desconto) || "-");
      $("#box_valor_acrescimo").text(formatarValor(response.valor_acrescimo) || "-");

      const planoFinanceiro = response.plano_financeiro;
      $("#box_plano_financeiro").text(
        planoFinanceiro
          ? [planoFinanceiro.codigo, planoFinanceiro.nome, planoFinanceiro.cod_contabil].filter(Boolean).join(" / ")
          : "-"
      );
      $("#box_centro_custo").text(formatarRelacionado(response.centro_custo));
      $("#box_projeto").text(formatarRelacionado(response.id_projeto, "id_projeto", "descricao"));
      $("#box_atividade").text(formatarRelacionado(response.atividade));
      $("#box_historico").text(response.historico || "-");
    })
    .catch((error) => {
      console.error(error);
      $("#detalhe-loading").hide();
      $("#detalhe-erro").text("Não foi possível carregar os detalhes deste recebimento.").show();
    });
}

$(document).ready(function () {
  $("#datatable-list-receipt").html(datatable_html());
  dataTable = datatable();
  filters(dataTable, "/conta_receber/");

  // Adicionar evento de clique para os cards (quando em mobile)
  $(document).on('click', '.receipt-card', function(e) {
    // Evitar que clique nos botões de ação também ative o box
    if (!$(e.target).closest('.receipt-card-actions').length &&
        !$(e.target).is('a') &&
        !$(e.target).is('button')) {
      const codigo = $(this).find('.receipt-card-header strong').text().replace('Código: ', '');
      if (codigo) {
        Box(codigo);
      }
    }
  });

  // Abrir o detalhe ao clicar em qualquer parte da linha, exceto no checkbox e nas ações
  $("#datatable tbody").on("click", "tr", function (e) {
    if ($(e.target).closest("td:first-child, td:last-child, a, button, input").length) {
      return;
    }
    let codigo = $(this).find("td:nth-child(2)").text();
    Box(codigo, $(this));
  });

  $("#modalDetalheRecebimento").on("hidden.bs.modal", function () {
    $(".datatable-container table tbody tr").removeClass("linha-selecionada");
  });
});
