import { api_get_id } from "../../helpers/api/get_id.js";
import { api_post } from "../../helpers/api/post.js";
import { api_delete } from "../../helpers/api/delete.js";
import {
  render_permissoes,
  get_permissoes_selecionadas,
  get_diferenca_permissoes,
  atualizar_permissoes_iniciais,
} from "./utils/permissions.js";

// Dados do colaborador carregado
let colaborador = {};

$(function () {
  // Carrega os dados e permissões do colaborador
  api_get_id("/colaborador/", $("#id").val()).then((response) => {
    colaborador = response;
    $("#nome-colaborador").text(response.nome);
    render_permissoes(response.permissoes);
  });

  // Salva as permissões após confirmação
  $("#form-permissoes").on("submit", function (e) {
    e.preventDefault();

    Swal.fire({
      title: "Salvar permissões?",
      text: "Deseja realmente salvar as alterações de permissões deste colaborador?",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sim, salvar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
    }).then(async (result) => {
      if (result.isConfirmed) {
        const idColaborador = $("#id").val();
        const { adicionar, remover } = get_diferenca_permissoes();

        // Se não houver alterações a serem persistidas
        if (adicionar.length === 0 && remover.length === 0) {
          Swal.fire({
            title: "Sem alterações",
            text: "Nenhuma alteração de permissão foi detectada.",
            icon: "info",
            timer: 2000,
            showConfirmButton: false,
          });
          return;
        }

        try {
          // Cria os registros das permissões concedidas (POST)
          if (adicionar.length > 0) {
            const bodyAdicionar = JSON.stringify({
              vendedor: idColaborador,
              permissoes: adicionar,
            });
            await api_post("/permissoes", bodyAdicionar);
          }

          // Exclui os registros das permissões desmarcadas (DELETE)
          if (remover.length > 0) {
            const responseDelete = await api_delete(
              "/permissoes/",
              idColaborador + "/" + remover.join(","),
            );

            if (
              typeof responseDelete === "string" &&
              responseDelete.startsWith("HTTP error")
            ) {
              throw new Error(responseDelete);
            }
          }

          // Sincroniza o estado inicial com as permissões salvas
          atualizar_permissoes_iniciais(get_permissoes_selecionadas());

          Swal.fire({
            title: "Sucesso!",
            text:
              "Permissões do colaborador #" +
              idColaborador +
              " atualizadas com sucesso.",
            icon: "success",
            timer: 2000,
            showConfirmButton: false,
          }).then(() => {
            const urlLista =
              $("#url-colaboradores").val() || "/general/collaborators";
            window.location.href = urlLista;
          });
        } catch (erro) {
          Swal.fire({
            title: "Erro!",
            text: "Ocorreu um erro ao salvar as permissões.",
            icon: "error",
          });
          console.error("Erro ao salvar permissões:", erro);
        }
      }
    });
  });
});


