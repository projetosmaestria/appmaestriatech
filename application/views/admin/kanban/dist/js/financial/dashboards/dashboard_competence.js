import { api_get } from "../../helpers/api/get.js";

let isLoading = false;
let currentRequestId = 0;

function GetEmpresa() {
  return new Promise((resolve) => {
    api_get("/empresa/", { situacao: "A" })
      .then((response) => {
        const filialSelect = document.getElementById("filial");

        if (filialSelect && response && Array.isArray(response.data)) {
          filialSelect._defaultHTML =
            '<option value="">Todas as filiais</option>';
          filialSelect.innerHTML = filialSelect._defaultHTML;

          const empresasOrdenadas = [...response.data].sort(
            (a, b) => (a.codigo || 0) - (b.codigo || 0)
          );

          empresasOrdenadas.forEach((empresa) => {
            const option = new Option(empresa.nome_fantasia || empresa.razao_social, empresa.codigo, false, false);
            $(filialSelect).append(option);
          });

          $(filialSelect).trigger('change.select2');
        }
        resolve();
      })
      .catch((error) => {
        console.error("Erro ao carregar empresas:", error);
        resolve();
      });
  });
}

function GetProjeto() {
  return new Promise((resolve) => {
    api_get("/projeto/", { ativo: "S" })
      .then((response) => {
        const projetoSelect = document.getElementById("projeto");

        if (projetoSelect && response && Array.isArray(response.data)) {
          projetoSelect._defaultHTML =
            '<option value="">Todos os Projetos</option>';
          projetoSelect.innerHTML = projetoSelect._defaultHTML;

          const projetoOrdenados = [...response.data].sort(
            (a, b) => (a.id_projeto || 0) - (b.id_projeto || 0)
          );

          projetoOrdenados.forEach((projeto) => {
            const option = new Option(projeto.descricao, projeto.id_projeto, false, false);
            $(projetoSelect).append(option);
          });

          $(projetoSelect).trigger('change.select2');
        }
        resolve();
      })
      .catch((error) => {
        console.error("Erro ao carregar projetos:", error);
        resolve();
      });
  });
}

function showLoading() {
  isLoading = true;
  document.getElementById("loading").style.display = "block";
  document.body.classList.add("loading");
}

function hideLoading() {
  isLoading = false;
  document.getElementById("loading").style.display = "none";
  document.body.classList.remove("loading");
}

function CarregarDadosDashboard() {
  if (isLoading) {
    //console.log('Requisição já em andamento, ignorando...');
    return;
  }

  const requestId = ++currentRequestId;
  //console.log(`Iniciando requisição #${requestId}`);

  showLoading();

  const filial = document.getElementById("filial").value;
  const projeto = document.getElementById("projeto").value;
  const mes = document.getElementById("mes").value;
  const ano = document.getElementById("ano").value;
  const todosPlanos = document.getElementById("todos_planos").checked;
  const competenciaPagamento = document.getElementById("competencia_pagamento").checked; // NOVO PARÂMETRO

  //console.log('Carregando dados com filtros:', { filial, projeto, mes, ano });

  const loadingText = document.querySelector("#loading .text-center");
  if (loadingText) {
    loadingText.textContent = `Carregando dados para ${mes}/${ano}...`;
  }

  api_get("/dashboard_competence/", {
    filial,
    projeto,
    mes,
    ano,
    todos_planos: todosPlanos ? "S" : "N",
    competencia_pagamento: competenciaPagamento ? "S" : "N" // NOVO PARÂMETRO
  })
    .then((response) => {
      //console.log(`Resposta recebida para requisição #${requestId}:`, response);

      if (requestId !== currentRequestId) {
        //console.log(`Resposta #${requestId} ignorada (requisição mais recente: #${currentRequestId})`);
        return;
      }

      if (response.success) {
        AtualizarCards(response.data);
        PreencherTabelaPlanos(response.data.planos);
      } else {
        alert("Erro ao carregar dados: " + response.message);
      }
    })
    .catch((error) => {
      console.error("Erro na requisição:", error);

      if (requestId === currentRequestId) {
        alert(
          "Erro ao carregar dados do dashboard. Entre em contato com o suporte."
        );
      }
    })
    .finally(() => {
      if (requestId === currentRequestId) {
        hideLoading();
      }
    });
}

function AtualizarCards(data) {
  const { competencia, totalReceitas, totalDespesas } = data;
  // Converter strings para float
  const totalReceitasFloat = parseFloat(totalReceitas) || 0;
  const totalDespesasFloat = parseFloat(totalDespesas) || 0;
  const resultadoOperacional = totalReceitasFloat + totalDespesasFloat; // Despesas já vêm como negativas
  // Atualizar card de Receitas
  const receitaCard = document.querySelector(".receita-card");
  if (receitaCard) {
    receitaCard.querySelector(
      ".card-value"
    ).textContent = `R$ ${totalReceitasFloat.toLocaleString("pt-BR", {
      minimumFractionDigits: 2,
    })}`;
    receitaCard.querySelector(
      ".card-text"
    ).textContent = `Competência ${competencia}`;
  }
  // Atualizar card de Despesas
  const despesaCard = document.querySelector(".despesa-card");
  if (despesaCard) {
    despesaCard.querySelector(
      ".card-value"
    ).textContent = `R$ ${totalDespesasFloat.toLocaleString("pt-BR", {
      minimumFractionDigits: 2,
    })}`;
    despesaCard.querySelector(
      ".card-text"
    ).textContent = `Competência ${competencia}`;
  }
  // Atualizar card de Resultado Operacional
  const resultadoCard = document.querySelector(".resultado-card");
  if (resultadoCard) {
    const sinal = resultadoOperacional < 0 ? "-" : "";
    resultadoCard.querySelector(
      ".card-value"
    ).textContent = `${sinal}R$ ${Math.abs(resultadoOperacional).toLocaleString(
      "pt-BR",
      { minimumFractionDigits: 2 }
    )}`;
    resultadoCard.querySelector(
      ".card-text"
    ).textContent = `Competência ${competencia}`;
  }
}

function PreencherTabelaPlanos(planos) {
  const tbody = document.querySelector(".table-contas tbody");
  if (!tbody) return;
  tbody.innerHTML = "";
  if (!planos || planos.length === 0) {
    tbody.innerHTML = `
          <tr>
              <td colspan="3" class="text-center py-4">
                  Nenhum plano financeiro encontrado para os filtros selecionados.
              </td>
          </tr>
      `;
    return;
  }

  // Mapa para controlar quais planos têm filhos
  const planosComFilhos = new Map();
  const planosPorCodigo = new Map();
  planos.forEach((plano) => {
    const partesCodigo = plano.COD_CONTABIL.split(".");
    const nivel = partesCodigo.length;
    // Verificar se este plano tem filhos
    const temFilhos = planos.some(
      (p) =>
        p.COD_CONTABIL.startsWith(plano.COD_CONTABIL + ".") &&
        p.COD_CONTABIL.split(".").length === nivel + 1
    );
    planosComFilhos.set(plano.COD_CONTABIL, temFilhos);
    planosPorCodigo.set(plano.COD_CONTABIL, plano);
  });

  // Função para determinar se um plano deve ser exibido inicialmente
  function deveExibirInicialmente(codigo) {
    return codigo.split(".").length <= 2; // Exibe apenas os dois primeiros níveis
  }

  // Agrupar planos por nível
  const planosPorNivel = {};
  planos.forEach((plano) => {
    const nivel = plano.COD_CONTABIL.split(".").length;
    if (!planosPorNivel[nivel]) planosPorNivel[nivel] = [];
    planosPorNivel[nivel].push(plano);
  });

  // Função recursiva para adicionar linhas
  function adicionarLinha(plano, nivel) {
    const tr = document.createElement("tr");
    const partesCodigo = plano.COD_CONTABIL.split(".");
    const nivelClass = `nivel-${nivel}`;
    const temFilhos = planosComFilhos.get(plano.COD_CONTABIL);
    const exibirInicialmente = deveExibirInicialmente(plano.COD_CONTABIL);

    // Calcular valores e estilos
    const saldo = parseFloat(plano.saldo);
    const isNegativo = saldo < 0;
    const valorClass = isNegativo ? "valor-negativo" : "";
    const sinal = isNegativo ? "-" : "";

    // Definir o estado inicial do toggle
    const toggleState = "+";

    tr.innerHTML = `
        <td>
            <div class="conta-nome ${nivelClass}" ${
      temFilhos
        ? `onclick="toggleContas('${plano.COD_CONTABIL}', this.querySelector('.toggle-btn'))" style="cursor: pointer;"`
        : ""
    }>
                ${
                  temFilhos
                    ? `<span class="toggle-btn">${toggleState}</span>`
                    : `<span class="toggle-btn" style="visibility: hidden;">+</span>`
                }
                <span class="conta-codigo">${plano.COD_CONTABIL}</span>
                ${plano.NOME}
            </div>
        </td>
        <td class="valor-conta ${valorClass}">${sinal}R$ ${Math.abs(
      saldo
    ).toLocaleString("pt-BR", { minimumFractionDigits: 2 })}</td>
        <td class="text-center">
            <span class="badge badge-tipo ${
              plano.RECEITA_DESPESA === "R" ? "badge-success" : "badge-danger"
            }">
                ${plano.RECEITA_DESPESA === "R" ? "Receita" : "Despesa"}
            </span>
        </td>
    `;

    tr.id = `plano-${plano.COD_CONTABIL}`;
    tr.setAttribute("data-nivel", nivel);
    tr.setAttribute("data-codigo", plano.COD_CONTABIL);

    // Ajustar a exibição inicial das linhas
    if (!exibirInicialmente && nivel > 2) {
      tr.style.display = "none";
    }

    tbody.appendChild(tr);

    // Adicionar filhos recursivamente
    if (temFilhos) {
      const filhos = planos.filter(
        (p) =>
          p.COD_CONTABIL.startsWith(plano.COD_CONTABIL + ".") &&
          p.COD_CONTABIL.split(".").length === nivel + 1
      );
      filhos.forEach((filho) => adicionarLinha(filho, nivel + 1));
    }
  }

  // Adicionar planos de nível 1
  const nivel1 = planos.filter((p) => p.COD_CONTABIL.split(".").length === 1);
  nivel1.forEach((plano) => adicionarLinha(plano, 1));
}

// Função para expandir/recolher todos os planos filhos
function toggleContas(codigoPai, btn) {
  if (event) {
      event.stopPropagation();
  }

  const todasLinhas = document.querySelectorAll(".table-contas tbody tr");
  const linhaPai = document.getElementById(`plano-${codigoPai}`);
  if (!linhaPai) return;

  const nivelPai = parseInt(linhaPai.getAttribute("data-nivel"));
  const estaExpandido = btn.textContent === '-';

  todasLinhas.forEach((tr) => {
      const codigo = tr.getAttribute("data-codigo");
      const nivel = parseInt(tr.getAttribute("data-nivel"));
      if (codigo.startsWith(codigoPai + ".") && nivel === nivelPai + 1) {
          if (estaExpandido) {
              tr.style.display = "none";
              toggleContasRecursivamente(codigo, true);
          } else {
              tr.style.display = "";
          }
      }
  });

  // Atualizar o texto do botão
  btn.textContent = estaExpandido ? "+" : "-";
}

function toggleContasRecursivamente(codigoPai, esconder) {
  const todasLinhas = document.querySelectorAll(".table-contas tbody tr");

  todasLinhas.forEach((tr) => {
    const codigo = tr.getAttribute("data-codigo");
    const nivel = parseInt(tr.getAttribute("data-nivel"));
    const nivelPai = codigoPai.split(".").length;

    if (codigo.startsWith(codigoPai + ".") && nivel === nivelPai + 1) {
      if (esconder) {
        tr.style.display = "none";
        toggleContasRecursivamente(codigo, true);
      } else {
        tr.style.display = "";
      }

      const toggleBtn = tr.querySelector(".toggle-btn");
      if (toggleBtn && toggleBtn.style.visibility !== "hidden") {
        toggleBtn.textContent = esconder ? "+" : "-";
      }
    }
  });
}

window.toggleContas = toggleContas;

function toggleTodasContas() {
  const btn = document.getElementById("btnExpandirRecolher");
  if (!btn) return;

  const isExpandindo = btn.textContent.trim() === "Expandir Todos";
  const todasLinhas = document.querySelectorAll(".table-contas tbody tr");

  todasLinhas.forEach((tr) => {
    const nivel = parseInt(tr.getAttribute("data-nivel"));
    const toggleBtn = tr.querySelector(".toggle-btn");

    if (isExpandindo) {
      tr.style.display = "";
      if (toggleBtn && toggleBtn.style.visibility !== "hidden") {
        toggleBtn.textContent = "-";
      }
    } else {
      if (nivel > 2) {
        tr.style.display = "none";
      } else {
        tr.style.display = "";
      }
      if (toggleBtn && toggleBtn.style.visibility !== "hidden") {
        toggleBtn.textContent = "+";
      }
    }
  });

  btn.textContent = isExpandindo ? "Recolher Todos" : "Expandir Todos";
}

window.toggleTodasContas = toggleTodasContas;

function ConfigurarEventListeners() {
  const filialSelect = document.getElementById("filial");
  const projetoSelect = document.getElementById("projeto");
  const mesSelect = document.getElementById("mes");
  const anoSelect = document.getElementById("ano");
  const todosPlanosCheckbox = document.getElementById("todos_planos");
  const competenciaPagamentoCheckbox = document.getElementById("competencia_pagamento");
  const btnFiltrar = document.getElementById("btn-filtrar");
  const btnLimpar = document.getElementById("btn-limpar");
  const btnExpandirRecolher = document.getElementById("btnExpandirRecolher");

  if (btnFiltrar) {
    btnFiltrar.addEventListener("click", CarregarDadosDashboard);
  }

  // Permite filtrar ao pressionar Enter nos filtros
  const filterElements = [filialSelect, projetoSelect, mesSelect, anoSelect, todosPlanosCheckbox, competenciaPagamentoCheckbox];
  filterElements.forEach((el) => {
    if (el) {
      el.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.keyCode === 13) {
          e.preventDefault();
          CarregarDadosDashboard();
        }
      });
    }
  });

  if (btnExpandirRecolher) {
    btnExpandirRecolher.addEventListener("click", toggleTodasContas);
  }

  if (btnLimpar) {
    btnLimpar.addEventListener("click", () => {
      if (filialSelect) $(filialSelect).val("").trigger('change.select2');
      if (projetoSelect) $(projetoSelect).val("").trigger('change.select2');
      if (mesSelect) mesSelect.value = new Date().getMonth() + 1;
      if (anoSelect) anoSelect.value = new Date().getFullYear();
      if (todosPlanosCheckbox) todosPlanosCheckbox.checked = false;
      if (competenciaPagamentoCheckbox) competenciaPagamentoCheckbox.checked = false;

      CarregarDadosDashboard();
    });
  }
}

window.toggleContas = toggleContas;

$(document).ready(function () {
  $('.select2').select2({
    theme: 'bootstrap4',
    width: '100%'
  });

  Swal.fire({
      title: 'Verificando...',
      text: 'Aguarde enquanto verificamos sua permissão.',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
  });

  fetch(api_url + '/dashboard_permissoes/competencia', { method: 'GET', headers: headers })
  .then(async (response) => {
      if (!response.ok) {
          const data = await response.json();
          throw new Error(data.message || 'Sem permissão');
      }
      return response.json();
  })
  .then(() => {
      Swal.close();
      Promise.all([GetEmpresa(), GetProjeto()])
        .then(() => {
          ConfigurarEventListeners();
          CarregarDadosDashboard();
        })
        .catch((error) => {
          hideLoading();
        });
  })
  .catch((error) => {
      Swal.fire({ icon: 'error', title: 'Acesso Negado', html: error.message, allowOutsideClick: false })
      .then(() => { location.href = base_url + 'financial/dashboard'; });
  });
});
