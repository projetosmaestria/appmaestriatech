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

// Função auxiliar para formatar data
function formatarData(dataString) {
  const data = new Date(dataString);
  const dia = data.getDate().toString().padStart(2, '0');
  const mes = (data.getMonth() + 1).toString().padStart(2, '0');
  const ano = data.getFullYear();
  return `${dia}/${mes}/${ano}`;
}

function CarregarDadosDashboard() {
  if (isLoading) {
    return;
  }

  const requestId = ++currentRequestId;
  showLoading();

  const filial = document.getElementById("filial").value;
  const projeto = document.getElementById("projeto").value;
  const dataInicial = document.getElementById("data-inicial").value;
  const dataFinal = document.getElementById("data-final").value;
  const todosPlanos = document.getElementById("todos_planos").checked;

  console.log('Filtros enviados para API:', {
    filial,
    projeto,
    dataInicial,
    dataFinal
  });

  // Validação das datas
  if (!dataInicial || !dataFinal) {
    alert("Por favor, selecione ambas as datas: inicial e final.");
    hideLoading();
    return;
  }

  if (new Date(dataInicial) > new Date(dataFinal)) {
    alert("A data inicial não pode ser maior que a data final.");
    hideLoading();
    return;
  }

  const loadingText = document.querySelector("#loading .loading-text");
  if (loadingText) {
    // Usar a nova função de formatação
    loadingText.textContent = `Carregando dados de ${formatarDataParaExibicao(dataInicial)} até ${formatarDataParaExibicao(dataFinal)}...`;
  }

  api_get("/dashboard_caixa/", {
    filial,
    projeto,
    data_inicial: dataInicial,
    data_final: dataFinal,
    todos_planos: todosPlanos ? "S" : "N"
  })
    .then((response) => {
      if (requestId !== currentRequestId) {
        return;
      }

      console.log('Resposta da API:', response);

      if (response.success) {
        AtualizarCards(response.data, dataInicial, dataFinal);
        PreencherTabelaPlanos(response.data.planos);
      } else {
        alert("Erro ao carregar dados: " + response.message);
      }
    })
    .catch((error) => {
      console.error("Erro na requisição:", error);
      if (requestId === currentRequestId) {
        alert("Erro ao carregar dados do dashboard. Entre em contato com o suporte.");
      }
    })
    .finally(() => {
      if (requestId === currentRequestId) {
        hideLoading();
      }
    });
}

function AtualizarCards(data, dataInicial, dataFinal) {
  const { totalReceitas, totalDespesas } = data;
  
  // Converter strings para float
  const totalReceitasFloat = parseFloat(totalReceitas) || 0;
  const totalDespesasFloat = parseFloat(totalDespesas) || 0;
  const resultadoOperacional = totalReceitasFloat - totalDespesasFloat;

  // USAR AS DATAS DOS FILTROS DIRETAMENTE, sem conversão problemática
  const periodoInicial = formatarDataParaExibicao(dataInicial);
  const periodoFinal = formatarDataParaExibicao(dataFinal);
  const periodoTexto = periodoInicial === periodoFinal 
    ? periodoInicial 
    : `${periodoInicial} a ${periodoFinal}`;

  console.log('Atualizando cards com período:', periodoTexto);
  console.log('Valores - Receitas:', totalReceitasFloat, 'Despesas:', totalDespesasFloat, 'Resultado:', resultadoOperacional);

  // Atualizar card de Receitas
  const receitaCard = document.querySelector(".receita-card");
  if (receitaCard) {
    receitaCard.querySelector(".card-value").textContent = 
      `R$ ${totalReceitasFloat.toLocaleString("pt-BR", { minimumFractionDigits: 2 })}`;
    receitaCard.querySelector(".card-text").textContent = 
      `Período ${periodoTexto}`;
  }

  // Atualizar card de Despesas
  const despesaCard = document.querySelector(".despesa-card");
  if (despesaCard) {
    despesaCard.querySelector(".card-value").textContent = 
      `R$ ${totalDespesasFloat.toLocaleString("pt-BR", { minimumFractionDigits: 2 })}`;
    despesaCard.querySelector(".card-text").textContent = 
      `Período ${periodoTexto}`;
  }

  // Atualizar card de Resultado Operacional
  const resultadoCard = document.querySelector(".resultado-card");
  if (resultadoCard) {
    const sinal = resultadoOperacional < 0 ? "-" : "";
    const valorAbsoluto = Math.abs(resultadoOperacional);
    resultadoCard.querySelector(".card-value").textContent = 
      `${sinal}R$ ${valorAbsoluto.toLocaleString("pt-BR", { minimumFractionDigits: 2 })}`;
    resultadoCard.querySelector(".card-text").textContent = 
      `Período ${periodoTexto}`;
    
    resultadoCard.querySelector(".card-value").className = 'card-value';
  }
}

// função para formatar data para exibição
function formatarDataParaExibicao(dataString) {
  // Dividir a string YYYY-MM-DD diretamente
  const partes = dataString.split('-');
  if (partes.length === 3) {
    const ano = partes[0];
    const mes = partes[1];
    const dia = partes[2];
    return `${dia}/${mes}/${ano}`;
  }
  
  // Fallback: usar o método anterior se o formato for diferente
  const data = new Date(dataString + 'T00:00:00-03:00');
  const dia = data.getDate().toString().padStart(2, '0');
  const mes = (data.getMonth() + 1).toString().padStart(2, '0');
  const ano = data.getFullYear();
  return `${dia}/${mes}/${ano}`;
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
    const valorClass = isNegativo ? "valor-negativo" : "valor-positivo";
    const sinal = isNegativo ? "-" : "";

    // Iniciar sempre com '+' (mais) para planos que têm filhos
    const toggleState = "+"; // Sempre começa com +

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

    // Ajustar a exibição inicial - esconder todos os filhos
    if (nivel > 2) {
      tr.style.display = "none";
    }

    tbody.appendChild(tr);

    // Adicionar filhos recursivamente (sempre escondidos inicialmente)
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
              // Recolher: esconder os filhos
              tr.style.display = "none";
              // Também recolher quaisquer netos que estejam expandidos
              const toggleBtn = tr.querySelector(".toggle-btn");
              if (toggleBtn && toggleBtn.textContent === '-') {
                  toggleContas(codigo, toggleBtn);
              }
          } else {
              // Expandir: mostrar os filhos diretos
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

function ConfigurarEventListeners() {
  const filialSelect = document.getElementById("filial");
  const projetoSelect = document.getElementById("projeto");
  const dataInicialInput = document.getElementById("data-inicial");
  const dataFinalInput = document.getElementById("data-final");
  const todosPlanosCheckbox = document.getElementById("todos_planos");
  const btnFiltrar = document.getElementById("btn-filtrar");
  const btnLimpar = document.getElementById("btn-limpar");

  if (btnFiltrar) {
    btnFiltrar.addEventListener("click", CarregarDadosDashboard);
  }

  // Permite filtrar ao pressionar Enter nos filtros
  const filterElements = [filialSelect, projetoSelect, dataInicialInput, dataFinalInput, todosPlanosCheckbox];
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

  if (btnLimpar) {
    btnLimpar.addEventListener("click", () => {
      // Obter primeiro e último dia do mês atual
      const hoje = new Date();
      const primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
      const ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
      
      // Formatando para YYYY-MM-DD
      const formatarParaInput = (data) => {
        return data.toISOString().split('T')[0];
      };

      if (filialSelect) $(filialSelect).val("").trigger('change.select2');
      if (projetoSelect) $(projetoSelect).val("").trigger('change.select2');
      if (dataInicialInput) dataInicialInput.value = formatarParaInput(primeiroDia);
      if (dataFinalInput) dataFinalInput.value = formatarParaInput(ultimoDia);
      if (todosPlanosCheckbox) todosPlanosCheckbox.checked = false;

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

  fetch(api_url + '/dashboard_permissoes/caixa', { method: 'GET', headers: headers })
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
          console.error('Erro ao inicializar dashboard:', error);
          hideLoading();
        });
  })
  .catch((error) => {
      Swal.fire({ icon: 'error', title: 'Acesso Negado', html: error.message, allowOutsideClick: false })
      .then(() => { location.href = base_url + 'financial/dashboard'; });
  });
});
