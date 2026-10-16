import { api_get } from "../../helpers/api/get.js";

function showLoading() {
  const loading = document.getElementById("loading");
  if (loading) {
    $(loading).fadeIn(200);
  }
  document.body.classList.add("loading");
}

function hideLoading() {
  const loading = document.getElementById("loading");
  if (loading) {
    $(loading).fadeOut(200);
  }
  document.body.classList.remove("loading");
}

function getFiliaisSelecionadas(filialVal) {
  if (filialVal && filialVal !== "") return filialVal;
  
  // Se for vazio ("Todas as filiais"), pega todos os options do select que possuem valor
  const options = $("#filial option").map(function() { return $(this).val(); }).get();
  const filiais = options.filter(val => val !== "");
  return filiais.length > 0 ? filiais : "";
}

function GetEmpresa() {
  return api_get("/empresa/", { situacao: "A" }).then((response) => {
    const filialSelect = document.getElementById("filial");

    if (filialSelect && response && Array.isArray(response.data)) {
      filialSelect._defaultHTML = '<option value="">Todas as filiais</option>';
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
  }).catch(err => console.error("Erro ao buscar empresas:", err));
}

function GetProjeto() {
  return api_get("/projeto/", { ativo: "S" }).then((response) => {
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
  }).catch(err => console.error("Erro ao buscar projetos:", err));
}

function formatarValor(valor) {
  const numero = parseFloat(valor) || 0;

  return numero.toLocaleString("pt-BR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function getExcludedAccountTypes() {
  const trazerTodasContas = document.getElementById('todas-contas').checked;
  return trazerTodasContas ? ['C'] : ['C', 'F'];
}

// Cards Informativos
function loadCardsData() {
  // Obter valores dos filtros
  const filial = $("#filial").val();
  const dataDe = $("#data-de").val();
  const dataAte = $("#data-ate").val();
  const projeto = $("#projeto").val();
  const excludedTypes = getExcludedAccountTypes();

  // Mostrar loading nos cards
  $(".card-value").html(
    '<div class="spinner-border spinner-border-sm" role="status"></div>'
  );
  $(".a-receber-value, .a-pagar-value, .previsao-saldo-value").html(
    '<div class="spinner-border spinner-border-sm" role="status"></div>'
  );

  // Primeiro, carregar os dados das contas a receber, a pagar, cheques a receber e a pagar
  Promise.all([
    // Dados das contas movimento
    api_get("/contas_movimento/", {
      data_inicial: dataDe,
      data_final: dataAte,
      filial: filial,
      tipo_conta__nin: excludedTypes,
      tipo_consulta: "conciliado"
    }),
    // Dados das contas a receber
    api_get("/contas_receber/", {
      data_pgto: "0000-00-00",
      valor_pago: 0,
      limit: -1,
      caucao: "0000-00-00",
      cartorio: "0000-00-00",
      agrup_destino: 0,
      incluir_transferencias: $("#incluir-transferencias-receber").is(":checked") ? "S" : "N",
      filial: filial,
      projeto: projeto,
      data_inicial: dataDe,
      data_final: dataAte
    }),
    // Dados das contas a pagar
    api_get("/contas_pagar/", {
      data_pgto: "0000-00-00,1899-12-30",
      valor_pago: 0,
      limit: -1,
      agrup_destino: 0,
      incluir_transferencias: $("#incluir-transferencias-pagar").is(":checked") ? "S" : "N",
      filial: filial,
      projeto: projeto,
      data_inicial: $("#filtrarPorDataInicial").is(":checked") ? dataDe : "",
      data_final: dataAte
    }),
    // Dados dos cheques a receber
    api_get("/cheque_receber/", {
      situacao: "A",
      data_baixa__ne: "0000-00-00, 1899-12-31",
      filial: filial,
      projeto: projeto,
      data_inicial: dataDe,
      data_final: dataAte
    }),
    // Dados dos cheques a pagar
    api_get("/cheque_pagar/", {
      situacao: "A",
      data_baixa: "0000-00-00, 1899-12-31",
      vencimento__ne: "0000-00-00, 1899-12-31",
      filial: filial,
      data_inicial: $("#filtrarPorDataInicial").is(":checked") ? dataDe : "",
      data_final: dataAte
    })
  ])
  .then(([contasResponse, receberResponse, pagarResponse, chequesReceberResponse, chequesPagarResponse]) => {
    // Processar dados das contas movimento
    if (contasResponse.data && contasResponse.data.length > 0) {
      let totalSaldoAnterior = 0;
      let totalEntradas = 0;
      let totalSaidas = 0;
      let totalFluxo = 0;
      let hasMovements = false;

      contasResponse.data.forEach((conta) => {
        if (
          conta.movimentos &&
          (parseFloat(conta.movimentos.saldo_anterior || 0) !== 0 ||
          parseFloat(conta.movimentos.entradas || 0) !== 0 ||
          parseFloat(conta.movimentos.saidas || 0) !== 0)
        ) {
          hasMovements = true;
        }

        totalSaldoAnterior += parseFloat(conta.movimentos?.saldo_anterior || 0);
        totalEntradas += parseFloat(conta.movimentos?.entradas || 0);
        totalSaidas += parseFloat(conta.movimentos?.saidas || 0);
        totalFluxo += parseFloat(conta.movimentos?.saldo || 0);
      });

      if (hasMovements) {
        // Atualiza os cards com os valores calculados
        $(".total-card .card-value").text(`R$ ${formatarValor(totalSaldoAnterior)}`);
        $(".receita-card .card-value").text(`R$ ${formatarValor(totalEntradas)}`);
        $(".despesa-card .card-value").text(`R$ ${formatarValor(totalSaidas)}`);
        $(".fluxo-card .card-value").text(`R$ ${formatarValor(totalFluxo)}`);

        $(".receita-card .card-text").text(`${formatarData(dataDe)} a ${formatarData(dataAte)}`);
        $(".despesa-card .card-text").text(`${formatarData(dataDe)} a ${formatarData(dataAte)}`);

        const diferenca = totalEntradas - totalSaidas;
        const percentual = totalSaidas > 0 ? ((diferenca / totalSaidas) * 100).toFixed(1) : 100;

        if (diferenca >= 0) {
          $(".fluxo-card .card-footer").html(
            `<i class="fas fa-arrow-up positive"></i> ${percentual}% positivo no período`
          );
        } else {
          $(".fluxo-card .card-footer").html(
            `<i class="fas fa-arrow-down negative"></i> ${Math.abs(percentual)}% negativo no período`
          );
        }
      } else {
        // Mostra mensagem de nenhuma movimentação
        mostrarMensagemSemDados();
      }
    } else {
      // Caso não haja contas
      mostrarMensagemSemDados();
    }

    // Processar dados das contas a receber e cheques a receber
    let totalAReceber = 0;
    
    // Somar contas a receber
    if (receberResponse.data && receberResponse.data.length > 0) {
      receberResponse.data.forEach((conta) => {
        totalAReceber += parseFloat(conta.valor || 0);
      });
    }
    
    // Somar cheques a receber
    if (chequesReceberResponse.data && chequesReceberResponse.data.length > 0) {
      chequesReceberResponse.data.forEach((cheque) => {
        totalAReceber += parseFloat(cheque.valor || 0);
      });
    }
    
    $(".a-receber-value").text(`R$ ${formatarValor(totalAReceber)}`);

    // Processar dados das contas a pagar e cheques a pagar
    let totalAPagar = 0;
    
    // Somar contas a pagar
    if (pagarResponse.data && pagarResponse.data.length > 0) {
      pagarResponse.data.forEach((conta) => {
        totalAPagar += parseFloat(conta.valor || 0);
      });
    }
    
    // Somar cheques a pagar
    if (chequesPagarResponse.data && chequesPagarResponse.data.length > 0) {
      chequesPagarResponse.data.forEach((cheque) => {
        totalAPagar += parseFloat(cheque.valor || 0);
      });
    }
    
    $(".a-pagar-value").text(`R$ ${formatarValor(totalAPagar)}`);

    // Calcular previsão de saldo (saldo anterior + a receber - a pagar)
    const saldoAnterior = parseFloat($(".total-card .card-value").text().replace("R$", "").replace(".", "").replace(",", ".")) || 0;
    const previsaoSaldo = saldoAnterior + totalAReceber - totalAPagar;
    $(".previsao-saldo-value").text(`R$ ${formatarValor(previsaoSaldo)}`);

  })
  .catch((error) => {
    console.error("Erro ao carregar dados dos cards:", error);
    $(".card-value, .a-receber-value, .a-pagar-value, .previsao-saldo-value").text("Erro ao carregar").addClass("empty");
    $(".card-text").text("Erro ao carregar dados");
  });
}

function mostrarMensagemSemDados() {
  $(".total-card .card-value").text("Nenhuma movimentação").addClass("empty");
  $(".receita-card .card-value").text("Nenhuma movimentação").addClass("empty");
  $(".despesa-card .card-value").text("Nenhuma movimentação").addClass("empty");
  $(".fluxo-card .card-value").text("Nenhuma movimentação").addClass("empty");

  $(".receita-card .card-text, .despesa-card .card-text").text("Sem dados no período");
  $(".fluxo-card .card-footer").html('<i class="fas fa-info-circle"></i> Sem dados para comparação');
  
  $(".a-receber-value, .a-pagar-value, .previsao-saldo-value").text("R$ 0,00");
}

// Contas Bancárias 
function loadContasBancarias(filial = "", dataDe = "", dataAte = "", projeto = "") {
  // Verifica se há projeto selecionado
  const projetoSelecionado = projeto && projeto !== "";

  // Adiciona ou remover mensagem de aviso
  const contasBancariasCard = document.querySelector(
    '.card-header span[class="flex-grow-1"]'
  );
  if (contasBancariasCard) {
    if (projetoSelecionado) {
      // Adiciona ícone de alerta e mensagem
      if (!contasBancariasCard.querySelector(".projeto-warning")) {
        const warningSpan = document.createElement("span");
        warningSpan.className = "projeto-warning text-warning ms-2 ml-2";
        warningSpan.innerHTML =
          '<i class="fas fa-exclamation-triangle"></i> O Filtro de projetos não é aplicado para estas informações';
        contasBancariasCard.appendChild(warningSpan);
      }
    } else {
      // Remove a mensagem se existir
      const warningElement =
        contasBancariasCard.querySelector(".projeto-warning");
      if (warningElement) {
        warningElement.remove();
      }
    }
  }

  // Carrega dados normais
  loadContasNormais(filial, dataDe, dataAte);

  // Carrega dados conciliados
  loadContasConciliadas(filial, dataDe, dataAte);
}

// Contas Bancárias tipo normal
function loadContasNormais(filial = "", dataDe = "", dataAte = "") {
  $("#contasMvtoTable tbody").html(`
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
            </td>
        </tr>
    `);

  const params = {
    tipo_conta__ne: "C",
    ativa: "S",
    tipo_consulta: "normal",
  };

  if (filial) params.filial = filial;

  if (dataDe) {
    const dataDeObj = new Date(dataDe);
    if (!isNaN(dataDeObj.getTime())) {
      params.data_inicial = dataDeObj.toISOString().split("T")[0];
    }
  }

  if (dataAte) {
    const dataAteObj = new Date(dataAte);
    if (!isNaN(dataAteObj.getTime())) {
      params.data_final = dataAteObj.toISOString().split("T")[0];
    }
  }

  api_get("/contas_movimento/", params)
    .then((response) => {
      const tbody = $("#contasMvtoTable tbody");
      const footerTotal = $("#contasMvtoTable tfoot .saldo-total");

      tbody.empty();

      let saldoTotal = 0;

      if (response.data && response.data.length > 0) {
        const contasFiltradas = filial
          ? response.data.filter((conta) => Array.isArray(filial) ? filial.includes(String(conta.filial)) : conta.filial == filial)
          : response.data;

        const contasOrdenadas = contasFiltradas.sort((a, b) => {
          if (a.codigo === 1) return -1;
          if (b.codigo === 1) return 1;
          return a.codigo - b.codigo;
        });

        contasOrdenadas.forEach((conta) => {
          const saldoAnterior = parseFloat(
            conta.movimentos?.saldo_anterior || 0
          );
          const entradas = parseFloat(conta.movimentos?.entradas || 0);
          const saidas = parseFloat(conta.movimentos?.saidas || 0);
          const saldo = parseFloat(conta.saldo_sistema || 0);

          saldoTotal += saldo;

          const saldoAnteriorClass = saldoAnterior >= 0 ? "" : "text-danger";
          const entradasClass = entradas >= 0 ? "text-info" : "text-danger";
          const saidasClass = saidas >= 0 ? "text-danger" : "text-danger";
          const saldoClass = saldo >= 0 ? "text-success" : "text-danger";

          tbody.append(`
                        <tr>
                            <td>${conta.codigo || "--"}</td>
                            <td>${conta.nome || "Sem nome"}</td>
                            <td class="text-end ${saldoAnteriorClass}">R$ ${formatarValor(
            saldoAnterior
          )}</td>
                            <td class="text-end ${entradasClass}">R$ ${formatarValor(
            entradas
          )}</td>
                            <td class="text-end ${saidasClass}">R$ ${formatarValor(
            saidas
          )}</td>
                            <td class="text-end saldo-conta ${saldoClass}">R$ ${formatarValor(
            saldo
          )}</td>
                        </tr>
                    `);
        });
        const saldoTotalClass =
          saldoTotal >= 0 ? "text-success" : "text-danger";
        footerTotal
          .text(`R$ ${formatarValor(saldoTotal)}`)
          .addClass(saldoTotalClass);
      } else {
        tbody.html(`
                    <tr>
                        <td colspan="6" class="text-center">Nenhuma conta bancária encontrada</td>
                    </tr>
                `);
        footerTotal.text("R$ 0,00");
      }
    })
    .catch((error) => {
      console.error("Erro ao carregar contas bancárias:", error);
      $("#contasMvtoTable tbody").html(`
                <tr>
                    <td colspan="6" class="text-center text-danger">Erro ao carregar dados</td>
                </tr>
            `);
    });
}

// Contas Bancárias tipo conciliada
function loadContasConciliadas(filial = "", dataDe = "", dataAte = "") {
  const trazerTodasContas = document.getElementById('todas-contas').checked;
  
  $("#contasConciliadoTable tbody").html(`
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
            </td>
        </tr>
    `);

  const params = {
    ativa: "S",
    tipo_consulta: "conciliado"
  };

  if (trazerTodasContas) {
    // Quando marcado: exclui apenas contas do tipo 'C'
    params.tipo_conta__nin = ["C"];
  } else {
    // Quando desmarcado: exclui contas dos tipos 'C' e 'F'
    params.tipo_conta__nin = ["C", "F"];
  }

  if (filial) params.filial = filial;

  if (dataDe) {
    const dataDeObj = new Date(dataDe);
    if (!isNaN(dataDeObj.getTime())) {
      params.data_inicial = dataDeObj.toISOString().split("T")[0];
    }
  }

  if (dataAte) {
    const dataAteObj = new Date(dataAte);
    if (!isNaN(dataAteObj.getTime())) {
      params.data_final = dataAteObj.toISOString().split("T")[0];
    }
  }

  api_get("/contas_movimento/", params)
    .then((response) => {
      const tbody = $("#contasConciliadoTable tbody");
      const footerTotal = $(".saldo-total-conciliado");

      tbody.empty();

      let saldoTotalConciliado = 0;

      if (response.data && response.data.length > 0) {
        const contasFiltradas = filial
          ? response.data.filter((conta) => Array.isArray(filial) ? filial.includes(String(conta.filial)) : conta.filial == filial)
          : response.data;

        const contasOrdenadas = contasFiltradas.sort((a, b) => {
          if (a.codigo === 1) return -1;
          if (b.codigo === 1) return 1;
          return a.codigo - b.codigo;
        });

        contasOrdenadas.forEach((conta) => {
          const saldoAnterior = parseFloat(
            conta.movimentos?.saldo_anterior || 0
          );
          const entradas = parseFloat(conta.movimentos?.entradas || 0);
          const saidas = parseFloat(conta.movimentos?.saidas || 0);
          const saldoConciliado = parseFloat(conta.saldo_conciliado || 0);

          saldoTotalConciliado += saldoConciliado;

          const saldoAnteriorClass = saldoAnterior >= 0 ? "" : "text-danger";
          const entradasClass = entradas >= 0 ? "text-info" : "text-danger";
          const saidasClass = saidas >= 0 ? "text-danger" : "text-danger";
          const saldoClass =  saldoConciliado >= 0 ? "text-success" : "text-danger";

          tbody.append(`
                        <tr>
                            <td>${conta.codigo || "--"}</td>
                            <td>${conta.nome || "Sem nome"}</td>
                            <td class="text-end ${saldoAnteriorClass}">R$ ${formatarValor(saldoAnterior)}</td>
                            <td class="text-end ${entradasClass}">R$ ${formatarValor(entradas)}</td>
                            <td class="text-end ${saidasClass}">R$ ${formatarValor(saidas)}</td>
                            <td class="text-end saldo-conciliado ${saldoClass}">R$ ${formatarValor(saldoConciliado)}</td>
                        </tr>
                    `);
        });

        const saldoTotalConciliadoClass = saldoTotalConciliado >= 0 ? "text-success" : "text-danger";

        footerTotal.text(`R$ ${formatarValor(saldoTotalConciliado)}`).addClass(saldoTotalConciliadoClass);
      } else {
        tbody.html(`
                    <tr>
                        <td colspan="6" class="text-center">Nenhuma conta bancária encontrada</td>
                    </tr>
                `);
        footerTotal.text("R$ 0,00");
      }
    })
    .catch((error) => {
      console.error("Erro ao carregar contas bancárias conciliadas:", error);
      $("#contasConciliadoTable tbody").html(`
                <tr>
                    <td colspan="6" class="text-center text-danger">Erro ao carregar dados</td>
                </tr>
            `);
    });
}

// Constas a Receber
function loadContasReceber(filial = "", dataDe = "", dataAte = "", projeto = "") {
   $("#contasReceberTable tbody").html(`
        <tr>
            <td colspan="4" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
            </td>
        </tr>
    `);
    const params = {
        data_pgto: "0000-00-00",
        valor_pago: 0,
        limit: -1,
        caucao: "0000-00-00",
        cartorio: "0000-00-00",
        agrup_destino: 0,
        incluir_transferencias: $("#incluir-transferencias-receber").is(":checked") ? "S" : "N"
    };
    if (filial) params.filial = filial;
    if (projeto) params.projeto = projeto;
    if (dataDe) {
        const dataDeObj = new Date(dataDe);
        if (!isNaN(dataDeObj.getTime())) {
            params.data_inicial = dataDeObj.toISOString().split("T")[0];
        }
    }
    if (dataAte) {
        const dataAteObj = new Date(dataAte);
        if (!isNaN(dataAteObj.getTime())) {
            params.data_final = dataAteObj.toISOString().split("T")[0];
        }
    }

  api_get("/contas_receber/", params)
    .then((response) => {
      const tbody = $("#contasReceberTable tbody");
      const footerTotal = $(".table-footer .valor-receber");

      tbody.empty();

      let total = 0;

      if (response.data && response.data.length > 0) {
        // Ordenar por vencimento (do mais recente para o mais antigo)
        const contasOrdenadas = response.data.sort((a, b) => {
          const dateA = a.vencimento ? new Date(a.vencimento) : new Date(0);
          const dateB = b.vencimento ? new Date(b.vencimento) : new Date(0);
          return dateA - dateB;
        });

        contasOrdenadas.forEach((conta) => {
          const vencimento = conta.vencimento
            ? moment(conta.vencimento).format("DD/MM/YYYY")
            : "--/--/----";

          const valor = parseFloat(conta.valor || 0);
          total += valor;

          tbody.append(`
                    <tr>
                        <td>${
                          conta.descricao || conta.documento || "Sem descrição"
                        }</td>
                        <td>${conta.cliente.nome || "--"}</td>
                        <td class="text-end">${vencimento}</td>
                        <td class="text-end valor-receber">R$ ${formatarValor(
                          valor
                        )}</td>
                    </tr>
                `);
        });

        footerTotal.text(`R$ ${formatarValor(total)}`);
      } else {
        tbody.html(`
                <tr>
                    <td colspan="4" class="text-center">Nenhuma conta a receber encontrada</td>
                </tr>
            `);
        $(".table-footer .valor-receber").text("R$ 0,00");
      }
    })
    .catch((error) => {
      console.error("Erro ao carregar contas a receber:", error);
      $("#contasReceberTable tbody").html(`
            <tr>
                <td colspan="4" class="text-center text-danger">Erro ao carregar dados</td>
            </tr>
        `);
    });
}

// Contas a Pagar
function loadContasPagar(filial = "", dataDe = "", dataAte = "", projeto = "") {
  const filtrarPorData = document.getElementById('filtrarPorDataInicial').checked;
  
  $("#contasPagarTable tbody").html(`
        <tr>
            <td colspan="4" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
            </td>
        </tr>
    `);

  const params = {
    data_pgto: "0000-00-00,1899-12-30",
    valor_pago: 0,
    limit: -1,
    agrup_destino: 0,
    incluir_transferencias: $("#incluir-transferencias-pagar").is(":checked") ? "S" : "N"
  };

  if (filial) params.filial = filial;
  if (projeto) params.projeto = projeto;

  // Só aplica data inicial se o checkbox estiver marcado
  if (filtrarPorData && dataDe) {
    const dataDeObj = new Date(dataDe);
    if (!isNaN(dataDeObj.getTime())) {
      params.data_inicial = dataDeObj.toISOString().split("T")[0];
    }
  }

  // Data final sempre é aplicada
  if (dataAte) {
    const dataAteObj = new Date(dataAte);
    if (!isNaN(dataAteObj.getTime())) {
      params.data_final = dataAteObj.toISOString().split("T")[0];
    }
  }

  api_get("/contas_pagar/", params)
    .then((response) => {
      const tbody = $("#contasPagarTable tbody");
      const footerTotal = $(".table-footer .valor-pagar");

      tbody.empty();

      let total = 0;

      if (response.data && response.data.length > 0) {
        // Ordenar por vencimento (do mais recente para o mais antigo)
        const contasOrdenadas = response.data.sort((a, b) => {
          const dateA = a.vencimento ? new Date(a.vencimento) : new Date(0);
          const dateB = b.vencimento ? new Date(b.vencimento) : new Date(0);
          return dateA - dateB;
        });

        contasOrdenadas.forEach((conta) => {
          const vencimento = conta.vencimento
            ? moment(conta.vencimento).format("DD/MM/YYYY")
            : "--/--/----";

          const valor = parseFloat(conta.valor || 0);
          total += valor;

          tbody.append(`
                    <tr>
                        <td>${
                          conta.descricao || conta.documento || "Sem descrição"
                        }</td>
                        <td>${conta.fornecedor?.razao_social || "--"}</td>
                        <td class="text-end">${vencimento}</td>
                        <td class="text-end valor-pagar">R$ ${formatarValor(
                          valor
                        )}</td>
                    </tr>
                `);
        });

        footerTotal.text(`R$ ${formatarValor(total)}`);
      } else {
        tbody.html(`
                <tr>
                    <td colspan="4" class="text-center">Nenhuma conta a pagar encontrada</td>
                </tr>
            `);
        footerTotal.text("R$ 0,00");
      }
    })
    .catch((error) => {
      console.error("Erro ao carregar contas a pagar:", error);
      $("#contasPagarTable tbody").html(`
            <tr>
                <td colspan="4" class="text-center text-danger">Erro ao carregar dados</td>
            </tr>
        `);
    });
}

// Cheque a Receber
function loadChequeReceber(
  filial = "",
  dataDe = "",
  dataAte = "",
  projeto = ""
) {
  $("#chequesReceberTable tbody").html(`
        <tr>
            <td colspan="5" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
            </td>
        </tr>
    `);

  const params = {
    situacao: "A",
    data_baixa__ne: "0000-00-00, 1899-12-31",
  };

  if (filial) params.filial = filial;
  if (projeto) params.projeto = projeto;

  // Validar e formatar datas
  if (dataDe) {
    const dataDeObj = new Date(dataDe);
    if (!isNaN(dataDeObj.getTime())) {
      params.data_inicial = dataDeObj.toISOString().split("T")[0];
    }
  }

  if (dataAte) {
    const dataAteObj = new Date(dataAte);
    if (!isNaN(dataAteObj.getTime())) {
      params.data_final = dataAteObj.toISOString().split("T")[0];
    }
  }

  api_get("/cheque_receber/", params)
    .then((response) => {
      const tbody = $("#chequesReceberTable tbody");
      const footerTotal = $("#chequesReceberTab .table-footer .cheque-receber");

      tbody.empty();

      let total = 0;

      if (response.data && response.data.length > 0) {
        // Ordenar por vencimento (do mais recente para o mais antigo)
        const chequesOrdenados = response.data.sort((a, b) => {
          const dateA = a.vencimento ? new Date(a.vencimento) : new Date(0);
          const dateB = b.vencimento ? new Date(b.vencimento) : new Date(0);
          return dateA - dateB;
        });

        chequesOrdenados.forEach((cheque) => {
          const vencimento = cheque.vencimento
            ? moment(cheque.vencimento).format("DD/MM/YYYY")
            : "--/--/----";

          const valor = parseFloat(cheque.valor || 0);
          total += valor;

          tbody.append(`
                    <tr>
                        <td>${cheque.conta_detalhes?.agencia || "--"}</td>
                        <td>${cheque.conta_detalhes?.nome || "--"}</td>
                        <td class="text-end">${cheque.num_cheque || "--"}</td>
                        <td>${cheque.fornecedor?.razao_social || "--"}</td>
                        <td class="text-end">${vencimento}</td>
                        <td class="text-end cheque-receber">R$ ${formatarValor(
                          valor
                        )}</td>
                    </tr>
                `);
        });

        footerTotal.text(`R$ ${formatarValor(total)}`);
      } else {
        tbody.html(`
                <tr>
                    <td colspan="5" class="text-center">Nenhum cheque a receber encontrado</td>
                </tr>
            `);
        footerTotal.text("R$ 0,00");
      }
    })
    .catch((error) => {
      console.error("Erro ao carregar cheques a receber:", error);
      $("#chequesReceberTable tbody").html(`
            <tr>
                <td colspan="5" class="text-center text-danger">Erro ao carregar dados</td>
            </tr>
        `);
    });
}

// Cheque a Pagar
function loadChequePagar(filial = "", dataDe = "", dataAte = "", projeto = "") {
  const filtrarPorData = document.getElementById('filtrarPorDataInicial').checked;
  
  $("#chequesPagarTable tbody").html(`
        <tr>
            <td colspan="5" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
            </td>
        </tr>
    `);

  const params = {
    situacao: "A",
    data_baixa: "0000-00-00, 1899-12-31",
    vencimento__ne: "0000-00-00, 1899-12-31"
  };

  if (filial) params.filial = filial;
  
  // Verifica se há projeto selecionado
  const projetoSelecionado = projeto && projeto !== "";

  // Adiciona ou remover mensagem de aviso
  const contasBancariasCard = document.querySelector(
    '.card-header span[class="flex-grow-1"]'
  );
  if (contasBancariasCard) {
    if (projetoSelecionado) {
      // Adiciona ícone de alerta e mensagem
      if (!contasBancariasCard.querySelector(".projeto-warning")) {
        const warningSpan = document.createElement("span");
        warningSpan.className = "projeto-warning text-warning ms-2 ml-2";
        warningSpan.innerHTML =
          '<i class="fas fa-exclamation-triangle"></i> O Filtro de projetos não é aplicado para estas informações';
        contasBancariasCard.appendChild(warningSpan);
      }
    } else {
      // Remove a mensagem se existir
      const warningElement =
        contasBancariasCard.querySelector(".projeto-warning");
      if (warningElement) {
        warningElement.remove();
      }
    }
  }
  
  // Só aplica data inicial se o checkbox estiver marcado
  if (filtrarPorData && dataDe) {
    const dataDeObj = new Date(dataDe);
    if (!isNaN(dataDeObj.getTime())) {
      params.data_inicial = dataDeObj.toISOString().split("T")[0];
    }
  }

  // Data final sempre é aplicada
  if (dataAte) {
    const dataAteObj = new Date(dataAte);
    if (!isNaN(dataAteObj.getTime())) {
      params.data_final = dataAteObj.toISOString().split("T")[0];
    }
  }

  api_get("/cheque_pagar/", params)
    .then((response) => {
      const tbody = $("#chequesPagarTable tbody");
      const footerTotal = $("#chequesPagarTab .table-footer .tcheque-pagar");

      tbody.empty();

      let total = 0;

      if (response.data && response.data.length > 0) {
        // Ordenar por vencimento (do mais recente para o mais antigo)
        const chequesOrdenados = response.data.sort((a, b) => {
          const dateA = a.vencimento ? new Date(a.vencimento) : new Date(0);
          const dateB = b.vencimento ? new Date(b.vencimento) : new Date(0);
          return dateA - dateB;
        });

        chequesOrdenados.forEach((cheque) => {
          const vencimento = cheque.vencimento
            ? moment(cheque.vencimento).format("DD/MM/YYYY")
            : "--/--/----";

          const valor = parseFloat(cheque.valor || 0);
          total += valor;

          tbody.append(`
                    <tr>
                        <td>${cheque.conta_detalhes?.agencia || "--"}</td>
                        <td>${cheque.conta_detalhes?.nome || "--"}</td>
                        <td class="text-end">${cheque.num_cheque || "--"}</td>
                        <td>${cheque.fornecedor?.razao_social || "--"}</td>
                        <td class="text-end">${vencimento}</td>
                        <td class="text-end cheque-pagar">R$ ${formatarValor(
                          valor
                        )}</td>
                    </tr>
                `);
        });

        footerTotal.text(`R$ ${formatarValor(total)}`);
      } else {
        tbody.html(`
                <tr>
                    <td colspan="6" class="text-center">Nenhum cheque a pagar encontrado</td>
                </tr>
            `);
        footerTotal.text("R$ 0,00");
      }
    })
    .catch((error) => {
      console.error("Erro ao carregar cheques a pagar:", error);
      $("#chequesPagarTable tbody").html(`
            <tr>
                <td colspan="5" class="text-center text-danger">Erro ao carregar dados</td>
            </tr>
        `);
    });
}

// Cartões a Receber
function loadCartaoReceber(filial = "", dataDe = "", dataAte = "", projeto = "") {
  $("#cartaoReceberTable tbody").html(`
        <tr>
            <td colspan="8" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
            </td>
        </tr>
    `);

  const params = {
    baixado__ne: "N",
  };

  if (filial) params.filial = filial;
  if (projeto) params.projeto = projeto;

  // Validar e formatar datas
  if (dataDe) {
    const dataDeObj = new Date(dataDe);
    if (!isNaN(dataDeObj.getTime())) {
      params.data_inicial = dataDeObj.toISOString().split("T")[0];
    }
  }

  if (dataAte) {
    const dataAteObj = new Date(dataAte);
    if (!isNaN(dataAteObj.getTime())) {
      params.data_final = dataAteObj.toISOString().split("T")[0];
    }
  }

  api_get("/cartao_receber/", params)
    .then((response) => {
      const tbody = $("#cartaoReceberTable tbody");
      const footerTotal = $(".table-footer .tcartao-receber");

      tbody.empty();

      let total = 0;

      if (response.data && response.data.length > 0) {
        // Ordenar por data de crédito (do mais recente para o mais antigo)
        const cartaoOrdenado = response.data.sort((a, b) => {
          const dateA = a.data_credito ? new Date(a.data_credito) : new Date(0);
          const dateB = b.data_credito ? new Date(b.data_credito) : new Date(0);
          return dateA - dateB;
        });

        cartaoOrdenado.forEach((cartao) => {
          const data_credito = cartao.data_credito
            ? moment(cartao.data_credito).format("DD/MM/YYYY")
            : "--/--/----";

          const valor = parseFloat(cartao.valor_bruto || 0);
          total += valor;

          tbody.append(`
                    <tr>
                        <td>${cartao.doc_cartao || "--"}</td>
                        <td>${cartao.conta?.nome || "--"}</td>
                        <td>${cartao.cartao?.nome || "--"}</td>
                        <td class="text-end">${data_credito}</td>
                        <td class="text-end">${cartao.parcela || "--"}</td>
                        <td>${cartao.cliente?.nome || "--"}</td>
                        <td class="text-end">${cartao.juros}</td>
                        <td class="text-end tcartao-receber">R$ ${formatarValor(
                          valor
                        )}</td>
                    </tr>
                `);
        });

        footerTotal.text(`R$ ${formatarValor(total)}`);
      } else {
        tbody.html(`
                <tr>
                    <td colspan="8" class="text-center">Nenhum cartão a receber encontrado</td>
                </tr>
            `);
        footerTotal.text("R$ 0,00");
      }
    })
    .catch((error) => {
      console.error("Erro ao carregar cheques a pagar:", error);
      $("#cartaoReceberTable tbody").html(`
            <tr>
                <td colspan="5" class="text-center text-danger">Erro ao carregar dados</td>
            </tr>
        `);
    });
}

// Gráficos de Fluxo
function loadCashFlowChart(filial = "", dataDe = "", dataAte = "") {
    try {
        const startDate = moment(dataDe, "YYYY-MM-DD").toDate();
        const endDate = moment(dataAte, "YYYY-MM-DD").toDate();
        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
            throw new Error("Datas inválidas");
        }

        const params = {};
        if (filial) params.filial = filial;
        if (dataDe) params.data_inicial = dataDe;
        if (dataAte) params.data_final = dataAte;

        const canvas = document.getElementById("cashFlowChart");
        const loading = document.createElement("div");
        loading.className = "text-center p-4";
        loading.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
        canvas.parentNode.insertBefore(loading, canvas);
        canvas.style.display = "none";

        api_get("/contas_movimento/", params)
            .then((contasResponse) => {
                const promises = [
                    api_get("/contas_receber/", {
                        filial: filial,
                        data_inicial: dataDe,
                        data_final: dataAte,
                        data_pgto: "0000-00-00",
                        valor_pago: 0,
                        limit: -1,
                    }),
                    api_get("/contas_pagar/", {
                        filial: filial,
                        data_inicial: dataDe,
                        data_final: dataAte,
                        data_pgto: "0000-00-00,1899-12-30",
                        valor_pago: 0,
                        limit: -1,
                    }),
                    api_get("/cheque_receber/", {
                        filial: filial,
                        data_inicial: dataDe,
                        data_final: dataAte,
                        situacao: "A",
                        data_baixa__ne: "0000-00-00, 1899-12-31",
                    }),
                    api_get("/cheque_pagar/", {
                        filial: filial,
                        data_inicial: dataDe,
                        data_final: dataAte,
                        situacao: "A",
                        data_baixa__ne: "0000-00-00, 1899-12-31",
                    }),
                    api_get("/cartao_receber/", {
                        filial: filial,
                        data_inicial: dataDe,
                        data_final: dataAte,
                        baixado__ne: "N",
                    }),
                ];

                return Promise.all(promises).then(
                    ([
                        receberResponse,
                        pagarResponse,
                        chequeReceberResponse,
                        chequePagarResponse,
                        cartaoReceberResponse,
                    ]) => {
                        return {
                            contas: contasResponse.data || [],
                            contasReceber: receberResponse.data || [],
                            contasPagar: pagarResponse.data || [],
                            chequesReceber: chequeReceberResponse.data || [],
                            chequesPagar: chequePagarResponse.data || [],
                            cartoesReceber: cartaoReceberResponse.data || [],
                        };
                    }
                );
            })
            .then(
                ({
                    contas,
                    contasReceber,
                    contasPagar,
                    chequesReceber,
                    chequesPagar,
                    cartoesReceber,
                }) => {
                    canvas.style.display = "block";
                    if (loading.parentNode) loading.parentNode.removeChild(loading);

                    destroyChart("cashFlowChart");
                    const ctx = canvas.getContext("2d");

                    const saldoAnteriorTotal = contas.reduce((sum, conta) => {
                        return sum + parseFloat(conta.movimentos?.saldo_anterior || 0);
                    }, 0);

                    const entradasTotal = contas.reduce((sum, conta) => {
                        return sum + parseFloat(conta.movimentos?.entradas || 0);
                    }, 0);

                    const saidasTotal = contas.reduce((sum, conta) => {
                        return sum + parseFloat(conta.movimentos?.saidas || 0);
                    }, 0);

                    const dateArray = [];
                    const currentDate = new Date(startDate);
                    while (currentDate <= endDate) {
                        dateArray.push(new Date(currentDate));
                        currentDate.setDate(currentDate.getDate() + 1);
                    }

                    const labels = dateArray.map((date) => moment(date).format("DD/MM"));
                    const saldoReal = [];
                    let saldoAcumuladoReal = saldoAnteriorTotal;
                    const entradasDiarias = entradasTotal / dateArray.length;
                    const saidasDiarias = saidasTotal / dateArray.length;

                    dateArray.forEach((date, index) => {
                        saldoAcumuladoReal += entradasDiarias - saidasDiarias;
                        saldoReal.push(saldoAcumuladoReal);
                    });

                    const saldoPrevisto = [];
                    let saldoAcumuladoPrevisto = saldoAnteriorTotal;

                    const receberPorDia = {};
                    contasReceber.forEach((conta) => {
                        if (!conta.vencimento) return;
                        const vencimento = moment(conta.vencimento).format("YYYY-MM-DD");
                        const valor = parseFloat(conta.valor || 0);
                        if (!receberPorDia[vencimento]) {
                            receberPorDia[vencimento] = 0;
                        }
                        receberPorDia[vencimento] += valor;
                    });

                    const pagarPorDia = {};
                    contasPagar.forEach((conta) => {
                        if (!conta.vencimento) return;
                        const vencimento = moment(conta.vencimento).format("YYYY-MM-DD");
                        const valor = parseFloat(conta.valor || 0);
                        if (!pagarPorDia[vencimento]) {
                            pagarPorDia[vencimento] = 0;
                        }
                        pagarPorDia[vencimento] += valor;
                    });

                    const chequesReceberPorDia = {};
                    chequesReceber.forEach((cheque) => {
                        if (!cheque.vencimento) return;
                        const vencimento = moment(cheque.vencimento).format("YYYY-MM-DD");
                        const valor = parseFloat(cheque.valor || 0);
                        if (!chequesReceberPorDia[vencimento]) {
                            chequesReceberPorDia[vencimento] = 0;
                        }
                        chequesReceberPorDia[vencimento] += valor;
                    });

                    const chequesPagarPorDia = {};
                    chequesPagar.forEach((cheque) => {
                        if (!cheque.vencimento) return;
                        const vencimento = moment(cheque.vencimento).format("YYYY-MM-DD");
                        const valor = parseFloat(cheque.valor || 0);
                        if (!chequesPagarPorDia[vencimento]) {
                            chequesPagarPorDia[vencimento] = 0;
                        }
                        chequesPagarPorDia[vencimento] += valor;
                    });

                    const cartoesReceberPorDia = {};
                    cartoesReceber.forEach((cartao) => {
                        if (!cartao.data_credito) return;
                        const dataCredito = moment(cartao.data_credito).format("YYYY-MM-DD");
                        const valor = parseFloat(cartao.valor_bruto || 0);
                        if (!cartoesReceberPorDia[dataCredito]) {
                            cartoesReceberPorDia[dataCredito] = 0;
                        }
                        cartoesReceberPorDia[dataCredito] += valor;
                    });

                    dateArray.forEach((date, index) => {
                        const dateStr = moment(date).format("YYYY-MM-DD");
                        const receberHoje = receberPorDia[dateStr] || 0;
                        const pagarHoje = pagarPorDia[dateStr] || 0;
                        const chequesReceberHoje = chequesReceberPorDia[dateStr] || 0;
                        const chequesPagarHoje = chequesPagarPorDia[dateStr] || 0;
                        const cartoesReceberHoje = cartoesReceberPorDia[dateStr] || 0;

                        if (index === 0) {
                            saldoAcumuladoPrevisto = saldoAnteriorTotal
                                + receberHoje
                                - pagarHoje
                                - chequesPagarHoje
                                + chequesReceberHoje
                                + cartoesReceberHoje;
                        } else {
                            saldoAcumuladoPrevisto = saldoPrevisto[index - 1]
                                + receberHoje
                                - pagarHoje
                                - chequesPagarHoje
                                + chequesReceberHoje
                                + cartoesReceberHoje;
                        }
                        saldoPrevisto.push(saldoAcumuladoPrevisto);
                    });

                    window.cashFlowChart = new Chart(ctx, {
                        type: "line",
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: "Fluxo Real",
                                    data: saldoReal,
                                    backgroundColor: "rgba(46, 204, 113, 0.2)",
                                    borderColor: "rgba(46, 204, 113, 1)",
                                    borderWidth: 2,
                                    tension: 0.1,
                                    fill: true,
                                },
                                {
                                    label: "Fluxo Previsto",
                                    data: saldoPrevisto,
                                    backgroundColor: "rgba(241, 196, 15, 0.2)",
                                    borderColor: "rgba(241, 196, 15, 1)",
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    tension: 0.1,
                                    fill: true,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: "top",
                                    labels: {
                                        font: {
                                            size: 12,
                                        },
                                    },
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            return `${context.dataset.label}: R$ ${formatarValor(
                                                context.raw
                                            )}`;
                                        },
                                        title: function (context) {
                                            return `Dia ${context[0].label}`;
                                        },
                                    },
                                },
                                title: {
                                    display: true,
                                    text: "Fluxo de Caixa Diário",
                                    font: {
                                        size: 16,
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    title: {
                                        display: true,
                                        text: "Valores (R$)",
                                        font: {
                                            size: 14,
                                        },
                                    },
                                    ticks: {
                                        callback: function (value) {
                                            return "R$ " + formatarValor(value);
                                        },
                                        font: {
                                            size: 10,
                                        },
                                    },
                                    grid: {
                                        color: "rgba(0, 0, 0, 0.1)",
                                    },
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: "Dias do Período",
                                        font: {
                                            size: 14,
                                        },
                                    },
                                    grid: {
                                        color: "rgba(0, 0, 0, 0.1)",
                                    },
                                    ticks: {
                                        font: {
                                            size: 10,
                                        },
                                    },
                                },
                            },
                            interaction: {
                                intersect: false,
                                mode: "index",
                            },
                            elements: {
                                point: {
                                    radius: 3,
                                    hoverRadius: 5,
                                },
                            },
                        },
                    });
                }
            )
            .catch((error) => {
                console.error("Erro ao carregar fluxo de caixa:", error);
                if (loading.parentNode) loading.parentNode.removeChild(loading);
                canvas.innerHTML =
                    '<div class="text-center text-danger p-4">Erro ao carregar dados do gráfico</div>';
            });
    } catch (error) {
        console.error("Erro ao carregar fluxo de caixa:", error);
    }
}

// Receita vs Despesa
function loadIncomeExpenseChart(filial = "", dataDe = "", dataAte = "") {
  const trazerTodasContas = document.getElementById('todas-contas').checked;
  
  const params = {
    tipo_consulta: "conciliado"
  };

  // Aplica o filtro de tipo de conta conforme o checkbox
  if (trazerTodasContas) {
    // Quando marcado: exclui apenas contas do tipo 'C'
    params.tipo_conta__nin = ["C"];
  } else {
    // Quando desmarcado: exclui contas dos tipos 'C' e 'F'
    params.tipo_conta__nin = ["C", "F"];
  }

  if (filial) params.filial = filial;
  if (dataDe) params.data_inicial = dataDe;
  if (dataAte) params.data_final = dataAte;

  const canvas = document.getElementById("incomeExpenseChart");
  const loading = document.createElement("div");
  loading.className = "text-center p-4";
  loading.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
  canvas.parentNode.insertBefore(loading, canvas);
  canvas.style.display = "none";

  api_get("/contas_movimento/", params)
    .then((response) => {
      canvas.style.display = "block";
      if (loading.parentNode) loading.parentNode.removeChild(loading);

      destroyChart("incomeExpenseChart");

      const ctx = canvas.getContext("2d");
      const contas = response.data || [];
      const labels = contas.map(
        (conta) => conta.nome || `Conta ${conta.codigo}`
      );
      const entradas = contas.map((conta) =>
        parseFloat(conta.movimentos?.entradas || 0)
      );
      const saidas = contas.map((conta) =>
        parseFloat(conta.movimentos?.saidas || 0)
      );

      window.incomeExpenseChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Receitas",
              data: entradas,
              backgroundColor: "rgba(46, 204, 113, 0.7)",
              borderColor: "rgba(46, 204, 113, 1)",
              borderWidth: 1,
            },
            {
              label: "Despesas",
              data: saidas,
              backgroundColor: "rgba(231, 76, 60, 0.7)",
              borderColor: "rgba(231, 76, 60, 1)",
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "top",
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  return `${context.dataset.label}: R$ ${formatarValor(
                    context.raw
                  )}`;
                },
              },
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return "R$ " + formatarValor(value);
                },
              },
            },
          },
        },
      });
    })
    .catch((error) => {
      console.error("Erro ao carregar receitas vs despesas:", error);
      if (loading.parentNode) loading.parentNode.removeChild(loading);
      canvas.innerHTML =
        '<div class="text-center text-danger p-4">Erro ao carregar dados do gráfico</div>';
    });
}

// Composição das Despesas
function loadExpensePieChart(filial = "", dataDe = "", dataAte = "", projeto = "") {
  const filtrarPorData = document.getElementById('filtrarPorDataInicial').checked;
  const projetoSelecionado = projeto && projeto !== "";
  const expensePieCard = document.querySelector(".card-header i.fa-chart-pie").parentNode;

  if (expensePieCard) {
    if (projetoSelecionado) {
      if (!expensePieCard.querySelector(".projeto-warning")) {
        const warningSpan = document.createElement("span");
        warningSpan.className = "projeto-warning text-warning ms-2 ml-2";
        warningSpan.innerHTML = '<i class="fas fa-exclamation-triangle"></i> O Filtro de projetos não é aplicado para estas informações';
        expensePieCard.appendChild(warningSpan);
      }
    } else {
      const warningElement = expensePieCard.querySelector(".projeto-warning");
      if (warningElement) {
        warningElement.remove();
      }
    }
  }

  const params = {
    data_pgto: "0000-00-00,1899-12-30",
    valor_pago: 0,
    limit: -1,
    agrup_destino: 0,
    incluir_transferencias: $("#incluir-transferencias-pagar").is(":checked") ? "S" : "N"
  };

  if (filial) params.filial = filial;
  if (projeto) params.projeto = projeto;

  // Só aplica data inicial se o checkbox estiver marcado
  if (filtrarPorData && dataDe) {
    const dataDeObj = new Date(dataDe);
    if (!isNaN(dataDeObj.getTime())) {
      params.data_inicial = dataDeObj.toISOString().split("T")[0];
    }
  }

  // Data final sempre é aplicada
  if (dataAte) {
    const dataAteObj = new Date(dataAte);
    if (!isNaN(dataAteObj.getTime())) {
      params.data_final = dataAteObj.toISOString().split("T")[0];
    }
  }

  const canvas = document.getElementById("expensePieChart");
  const loading = document.createElement("div");
  loading.className = "text-center p-4";
  loading.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
  canvas.parentNode.insertBefore(loading, canvas);
  canvas.style.display = "none";

  api_get("/contas_pagar/", params)
    .then((response) => {
      if (!response.data || response.data.length === 0) {
        throw new Error("Nenhuma conta a pagar encontrada");
      }

      const centroCustosIds = [...new Set(response.data.map((item) => item.centro_custo).filter(Boolean))];
      if (centroCustosIds.length === 0) {
        throw new Error("Nenhum centro de custo encontrado nas contas");
      }

      return api_get("/plano_financeiro/", {
        codigo: centroCustosIds.join(","),
        limit: -1,
      }).then((planoResponse) => {
        const planosMap = {};
        if (planoResponse.data && planoResponse.data.length > 0) {
          planoResponse.data.forEach((plano) => {
            planosMap[plano.codigo] = plano;
          });
        }

        const grupos = {};
        response.data.forEach((conta) => {
          if (!conta.centro_custo) return;
          const plano = planosMap[conta.centro_custo];
          if (!plano) return;
          const grupoKey = plano.cod_contabil ? plano.cod_contabil.substring(0, 9) : "outros";
          if (!grupos[grupoKey]) {
            let grupoNome = "Outros";
            const planoGrupo = Object.values(planosMap).find((p) => p.cod_contabil && p.cod_contabil.startsWith(grupoKey));
            if (planoGrupo) {
              grupoNome = planoGrupo.nome || `Grupo ${grupoKey}`;
            }
            grupos[grupoKey] = {
              codigo: grupoKey,
              nome: grupoNome,
              valor: 0,
            };
          }
          grupos[grupoKey].valor += parseFloat(conta.valor || 0);
        });

        return Object.values(grupos).filter((item) => item.valor > 0).sort((a, b) => b.valor - a.valor);
      });
    })
    .then((grupos) => {
      canvas.style.display = "block";
      if (loading.parentNode) loading.parentNode.removeChild(loading);
      destroyChart("expensePieChart");

      if (!grupos || grupos.length === 0) {
        canvas.innerHTML = '<div class="text-center text-muted p-4">Nenhuma despesa agrupada encontrada</div>';
        return;
      }

      const ctx = canvas.getContext("2d");
      const isSmallScreen = window.innerWidth <= 1366;
      const fontSize = isSmallScreen ? 10 : 12;

      if (grupos.length <= 8) {
        grupos.sort((a, b) => b.valor - a.valor);
        const labels = grupos.map((item) => item.nome);
        const valores = grupos.map((item) => item.valor);
        const cores = gerarCoresPersonalizadas(grupos.length);
        const total = valores.reduce((a, b) => a + b, 0);

        window.expensePieChart = new Chart(ctx, {
          type: "bar",
          data: {
            labels: labels,
            datasets: [{
              label: "Valor (R$)",
              data: valores,
              backgroundColor: cores.map((c) => c.fundo),
              borderColor: cores.map((c) => c.borda),
              borderWidth: 1,
            }],
          },
          options: {
            indexAxis: "y",
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false,
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const label = context.dataset.label || "";
                    const value = context.raw || 0;
                    const percentage = ((value / total) * 100).toFixed(1);
                    return `${label}: R$ ${formatarValor(value)} (${percentage}%)`;
                  },
                },
              },
              title: {
                display: true,
                text: "Composição das Despesas por Grupo",
                font: {
                  size: isSmallScreen ? 12 : 14,
                },
              },
              datalabels: {
                display: false,
              },
            },
            scales: {
              x: {
                beginAtZero: true,
                ticks: {
                  callback: function(value) {
                    return "R$ " + formatarValor(value);
                  },
                },
                title: {
                  display: true,
                  text: "Valor (R$)",
                  font: {
                    size: fontSize,
                  },
                },
              },
              y: {
                ticks: {
                  autoSkip: false,
                  font: {
                    size: fontSize,
                  },
                },
              },
            },
            annotation: {
              annotations: grupos.map((item, index) => ({
                type: "label",
                xValue: item.valor,
                yValue: index,
                content: [
                  `R$ ${formatarValor(item.valor)}`,
                  `${((item.valor / total) * 100).toFixed(1)}%`,
                ],
                font: {
                  size: isSmallScreen ? 9 : 11,
                },
                backgroundColor: "rgba(255, 255, 255, 0.8)",
                borderColor: "rgba(0, 0, 0, 0.1)",
                borderWidth: 1,
                borderRadius: 4,
                padding: 4,
                textAlign: "left",
                xAdjust: 10,
              })),
            },
          },
        });
      } else {
        const labels = grupos.map((item) => item.nome);
        const valores = grupos.map((item) => item.valor);
        const cores = gerarCoresPersonalizadas(grupos.length);

        window.expensePieChart = new Chart(ctx, {
          type: "pie",
          data: {
            labels: labels,
            datasets: [{
              data: valores,
              backgroundColor: cores.map((c) => c.fundo),
              borderColor: cores.map((c) => c.borda),
              borderWidth: 1,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: "right",
                labels: {
                  font: {
                    size: fontSize,
                  },
                  padding: 10,
                  generateLabels: function(chart) {
                    const data = chart.data;
                    if (data.labels.length && data.datasets.length) {
                      return data.labels.map(function(label, i) {
                        const value = data.datasets[0].data[i];
                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        return {
                          text: `${label}: R$ ${formatarValor(value)} (${percentage}%)`,
                          fillStyle: data.datasets[0].backgroundColor[i],
                          hidden: false,
                          index: i,
                        };
                      });
                    }
                    return [];
                  },
                },
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const label = context.label || "";
                    const value = context.raw || 0;
                    const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                    const percentage = Math.round((value / total) * 100);
                    return `${label}: R$ ${formatarValor(value)} (${percentage}%)`;
                  },
                },
              },
              title: {
                display: false,
                text: "Composição das Despesas por Grupo",
                font: {
                  size: isSmallScreen ? 12 : 14,
                },
              },
            },
            layout: {
              padding: {
                top: isSmallScreen ? 10 : 20,
                bottom: isSmallScreen ? 10 : 20,
              },
            },
          },
        });
      }
    })
    .catch((error) => {
      console.error("Erro ao carregar composição das despesas:", error);
      if (loading.parentNode) loading.parentNode.removeChild(loading);
      canvas.innerHTML = '<div class="text-center text-danger p-4">' +
        (error.message === "Nenhuma conta a pagar encontrada" ||
        error.message === "Nenhum centro de custo encontrado nas contas" ?
        error.message : "Erro ao carregar dados do gráfico") + '</div>';
    });
}

function gerarCoresPersonalizadas(quantidade) {
  const coresBase = [
    { fundo: "rgba(231, 76, 60, 0.7)", borda: "rgba(231, 76, 60, 1)" },
    { fundo: "rgba(155, 89, 182, 0.7)", borda: "rgba(155, 89, 182, 1)" },
    { fundo: "rgba(52, 152, 219, 0.7)", borda: "rgba(52, 152, 219, 1)" },
    { fundo: "rgba(26, 188, 156, 0.7)", borda: "rgba(26, 188, 156, 1)" },
    { fundo: "rgba(241, 196, 15, 0.7)", borda: "rgba(241, 196, 15, 1)" },
    { fundo: "rgba(230, 126, 34, 0.7)", borda: "rgba(230, 126, 34, 1)" },
  ];

  const cores = [];
  for (let i = 0; i < quantidade; i++) {
    if (i < coresBase.length) {
      cores.push(coresBase[i]);
    } else {
      // Gerar cores aleatórias para itens extras
      const hue = Math.floor(Math.random() * 360);
      cores.push({
        fundo: `hsla(${hue}, 70%, 60%, 0.7)`,
        borda: `hsl(${hue}, 70%, 50%)`,
      });
    }
  }
  return cores;
}

// Função auxiliar para formatar data (DD/MM)
function formatarData(dataString) {
  if (!dataString) return "--/--";
  const [ano, mes, dia] = dataString.split("-");
  return `${dia}/${mes}`;
}

function destroyChart(chartVarName) {
  if (
    window[chartVarName] &&
    typeof window[chartVarName].destroy === "function"
  ) {
    window[chartVarName].destroy();
  }
  window[chartVarName] = null;
}

function formatDate(date) {
  const d = new Date(date);
  let month = "" + (d.getMonth() + 1);
  let day = "" + d.getDate();
  const year = d.getFullYear();

  if (month.length < 2) month = "0" + month;
  if (day.length < 2) day = "0" + day;

  return [year, month, day].join("-");
}

// Função para atualizar datas de e até ao alterar o mês por Período
function updateDatesFromMonthYear() {
  const month = $("#mes").val();
  const year = $("#ano").val();

  if (month && year) {
    const monthFormatted = month.padStart(2, "0");
    const firstDay = `${year}-${monthFormatted}-01`;

    const lastDay = new Date(year, month, 0);
    const lastDayFormatted = `${year}-${monthFormatted}-${lastDay
      .getDate()
      .toString()
      .padStart(2, "0")}`;

    const currentDataDe = $("#data-de").val();
    const currentDataAte = $("#data-ate").val();

    if (currentDataDe !== firstDay || currentDataAte !== lastDayFormatted) {
      $("#data-de").val(firstDay);
      $("#data-ate").val(lastDayFormatted);
    }
  }
}

function loadInitialData() {
  const initialFilial = getFiliaisSelecionadas($("#filial").val());
  const initialDataDe = $("#data-de").val();
  const initialDataAte = $("#data-ate").val();
  const initialProjeto = $("#projeto").val();

  loadCardsData(initialFilial, initialDataDe, initialDataAte);
  loadContasBancarias(initialFilial, initialDataDe, initialDataAte);

  // Para as primeiras cargas, não considerar dataDe
  Promise.all([
    loadContasReceber(
      initialFilial,
      initialDataDe,
      initialDataAte,
      initialProjeto
    ),
    loadContasPagar(
      initialFilial,
      "", // Não passa dataDe inicialmente
      initialDataAte,
      initialProjeto
    ),
    loadChequePagar(
      initialFilial,
      "", // Não passa dataDe inicialmente
      initialDataAte,
      initialProjeto
    ),
    loadChequeReceber(
      initialFilial,
      initialDataDe,
      initialDataAte,
      initialProjeto
    ),
    loadCartaoReceber(initialFilial, initialDataDe, initialDataAte, initialProjeto),
  ]).then(() => {
    loadCashFlowChart(initialFilial, initialDataDe, initialDataAte);
    loadIncomeExpenseChart(initialFilial, initialDataDe, initialDataAte);
    loadExpensePieChart(
      initialFilial,
      initialDataDe,
      initialDataAte,
      initialProjeto
    );
  });
}

// Executa a busca e atualização dos dados do dashboard com base nos filtros
function aplicarFiltros() {
  showLoading();
  const filial = getFiliaisSelecionadas($("#filial").val());
  const dataDe = $("#data-de").val();
  const dataAte = $("#data-ate").val();
  const projeto = $("#projeto").val();
  const filtrarPorData = $("#filtrarPorDataInicial").is(":checked");

  loadCardsData(filial, dataDe, dataAte);
  loadContasBancarias(filial, dataDe, dataAte, projeto);
  loadContasReceber(filial, dataDe, dataAte, projeto);

  loadContasPagar(
    filial,
    filtrarPorData ? dataDe : "",
    dataAte,
    projeto
  );

  if ($("#chequesPagarTab").hasClass("active")) {
    loadChequePagar(
      filial,
      filtrarPorData ? dataDe : "",
      dataAte,
      projeto
    );
  } else {
    loadChequeReceber(filial, dataDe, dataAte, projeto);
  }

  loadCartaoReceber(filial, dataDe, dataAte, projeto);
  loadCashFlowChart(filial, dataDe, dataAte);
  loadIncomeExpenseChart(filial, dataDe, dataAte);
  loadExpensePieChart(filial, dataDe, dataAte, projeto);

  setTimeout(hideLoading, 800);
}

document.addEventListener('DOMContentLoaded', function() {
  var tabElms = [].slice.call(document.querySelectorAll('a[data-bs-toggle="tab"]'));
  tabElms.forEach(function(tabElm) {
    tabElm.addEventListener('shown.bs.tab', function(e) {
      const target = e.target.getAttribute('href');
      const filial = document.getElementById('filial').value;
      const dataDe = document.getElementById('data-de').value;
      const dataAte = document.getElementById('data-ate').value;
      const projeto = document.getElementById('projeto').value;

      if (target === '#chequesPagarTab') {
        loadChequePagar(filial, dataDe, dataAte, projeto);
      } else if (target === '#chequesReceberTab') {
        loadChequeReceber(filial, dataDe, dataAte, projeto);
      } else if (target === '#fluxoCaixa') {
        loadCashFlowChart(filial, dataDe, dataAte);
      } else if (target === '#receitaDespesa') {
        loadIncomeExpenseChart(filial, dataDe, dataAte);
      }

      showLoading();
      loadCardsData();
      hideLoading();
    });
  });
});

$("#mes, #ano").change(updateDatesFromMonthYear);

$("#data-de, #data-ate").change(function () {
  const dataDe = $("#data-de").val();
  const dataAte = $("#data-ate").val();

  if (dataDe && dataAte) {
    const deDate = new Date(dataDe);
    const ateDate = new Date(dataAte);

    if (
      deDate.getFullYear() === ateDate.getFullYear() &&
      deDate.getMonth() === ateDate.getMonth()
    ) {
      const firstDay = new Date(deDate.getFullYear(), deDate.getMonth(), 1);
      const lastDay = new Date(deDate.getFullYear(), deDate.getMonth() + 1, 0);

      if (
        formatDate(deDate) === formatDate(firstDay) &&
        formatDate(dataAte) === formatDate(lastDay)
      ) {
        const currentMes = $("#mes").val();
        const currentAno = $("#ano").val();
        const expectedMes = ("0" + (deDate.getMonth() + 1)).slice(-2);
        const expectedAno = deDate.getFullYear();

        if (currentMes !== expectedMes || currentAno !== expectedAno) {
          $("#mes").val(expectedMes);
          $("#ano").val(expectedAno);
        }
      }
    }
  }
});

// Botão Filtrar
$("#btn-filtrar").click(function () {
  aplicarFiltros();
});

// Permite filtrar ao pressionar Enter em qualquer campo de filtro
$("#filial, #projeto, #data-de, #data-ate, #mes, #ano, #todas-contas, #filtrarPorDataInicial").on("keydown", function (e) {
  if (e.key === "Enter" || e.keyCode === 13) {
    e.preventDefault();
    aplicarFiltros();
  }
});

// Botão limpar filtros
$("#btn-limpar").click(function () {
  const today = new Date();
  const firstDay = formatDate(
    new Date(today.getFullYear(), today.getMonth(), 1)
  );
  const lastDay = formatDate(new Date(today.getFullYear(), today.getMonth() + 1, 0));

  $("#data-de").val(firstDay);
  $("#data-ate").val(lastDay);
  $("#mes").val(("0" + (today.getMonth() + 1)).slice(-2));
  $("#ano").val(today.getFullYear());
  $("#projeto").val("").trigger('change.select2');
  $("#filial").val("").trigger('change.select2');
  $("#filtrarPorDataInicial").prop("checked", false);
  $("#todas-contas").prop("checked", false);

  aplicarFiltros();
});

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

  fetch(api_url + '/dashboard_permissoes/financeiro', { method: 'GET', headers: headers })
  .then(async (response) => {
      if (!response.ok) {
          const data = await response.json();
          throw new Error(data.message || 'Sem permissão');
      }
      return response.json();
  })
  .then(() => {
      Swal.close();
      const today = new Date();
      const firstDay = formatDate(
        new Date(today.getFullYear(), today.getMonth(), 1)
      );
      const lastDay = formatDate(
        new Date(today.getFullYear(), today.getMonth() + 1, 0)
      );

      $("#data-de").val(firstDay);
      $("#data-ate").val(lastDay);

      updateDatesFromMonthYear();

      loadInitialData();

      // Carregamento inicial com loading
      showLoading();
      Promise.all([
        GetEmpresa(),
        GetProjeto()
      ]).finally(() => {
        setTimeout(hideLoading, 1000);
      });
  })
  .catch((error) => {
      Swal.fire({ icon: 'error', title: 'Acesso Negado', html: error.message, allowOutsideClick: false })
      .then(() => { location.href = base_url + 'financial/dashboard'; });
  });
});
