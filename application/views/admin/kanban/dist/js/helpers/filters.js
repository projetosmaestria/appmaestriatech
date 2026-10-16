import { api_delete } from "./api/delete.js";

function filters(dataTable, endpoint) {
  $("body").keydown(function (e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode == 119) $("#modalFiltroList").modal("show");
    if (keyCode == 13) $("#btnBuscar").click();
  });

  $("#modalFiltroList").on("shown.bs.modal", function (e) {
    $('input[name="qtxt"]').focus();
  });

  $(".input-group.date").datepicker({
    format: "dd/mm/yyyy",
    todayBtn: "linked",
    language: "pt-BR",
    autoclose: true,
    todayHighlight: true,
  });

  $("#btnBuscar").click(function () {
    dataTable.clear().draw();
  });

  $("#btnLimparBusca").click(function () {
    $("#modalFiltroList")
      .find("input:text:not(.no-clear), input:password:not(.no-clear), input:file:not(.no-clear), select:not(.no-clear), textarea:not(.no-clear)")
      .each(function () {
        $(this).val("");
      });
    dataTable.clear().draw();
  });

  $(".toggle-vis").on("click", function (e) {
    var column = dataTable.column($(this).attr("data-column"));
    column.visible(!column.visible());
  });

  $("#btnRemover").click(function () {
    var rows_selected = dataTable.column(0).checkboxes.selected();
    var ids = rows_selected.join(",");
  
    if (ids.length === 0) {
      toastr["warning"]("Selecione ao menos um item para remover.", "Atenção");
      return;
    }
  
    $("#btnRemover").prop("disabled", true).text("Removendo...");
  
    api_delete(endpoint, ids)
      .then((response) => {
        if (typeof response === 'object') {
          if (response.success) {
            toastr["success"](response.message || "Dados removidos com sucesso.", "Sucesso");
          } else {
            toastr["error"](response.message || "Erro ao remover os dados.", "Erro");
          }
        } 
        else if (typeof response === 'string') {
          try {
            const jsonResponse = JSON.parse(response);
            if (jsonResponse.success) {
              toastr["success"](jsonResponse.message || "Dados removidos com sucesso.", "Sucesso");
            } else {
              toastr["error"](jsonResponse.message || "Erro ao remover os dados.", "Erro");
            }
          } catch (e) {
            if (response.trim() === "") {
              toastr["success"]("Dados removidos com sucesso.", "Sucesso");
            } else {
              toastr["success"](response, "Sucesso");
            }
          }
        }
        else if (!response) {
          toastr["success"]("Dados removidos com sucesso.", "Sucesso");
        }
        
        setTimeout(() => {
          location.reload();
        }, 1000);
        dataTable.clear().draw();
      })
      .catch((error) => {
        console.error("Erro na requisição:", error);
        toastr["error"](error.message || "Erro ao remover os dados.", "Erro");
      })
      .finally(() => {
        $("#btnRemover").prop("disabled", false).text("Remover");
      });
  });
}

export { filters };