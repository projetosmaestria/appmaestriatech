import { api_get } from "../../helpers/api/get.js";

let isLoading = false;
let currentRequestId = 0;

function GetEmpresa() {
  return new Promise((resolve) => {
    api_get("/empresa/", { situacao: "A" })
      .then((response) => {
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
          projetoSelect._defaultHTML = '<option value="">Todos os Projetos</option>';
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

function CarregarDadosAnuais() {
  if (isLoading) {
    return;
  }

  const requestId = ++currentRequestId;

  showLoading();

  const filial = document.getElementById("filial").value;
  const projeto = document.getElementById("projeto").value;
  const ano = document.getElementById("ano").value;
  const todosPlanos = document.getElementById("todos_planos").checked;
  const competenciaPagamento = document.getElementById("competencia_pagamento").checked; // NOVO PARÂMETRO

  // Atualizar header do ano
  atualizarHeaderAno(ano);

  const loadingText = document.querySelector("#loading .loading-text");
  if (loadingText) {
    loadingText.textContent = `Carregando dados para ${ano}...`;
  }

  // Buscar dados para todos os meses do ano selecionado
  const mesesParaBuscar = obterMesesParaBuscar(ano);
  
  // Array para armazenar todas as promessas
  const promises = mesesParaBuscar.map(mes => {
    return api_get("/dashboard_competence/", {
      filial,
      projeto,
      mes: mes.numero,
      ano,
      todos_planos: todosPlanos ? "S" : "N",
      competencia_pagamento: competenciaPagamento ? "S" : "N" // NOVO PARÂMETRO
    });
  });

  // Executar todas as requisições em paralelo
  Promise.all(promises)
    .then(responses => {
      if (requestId !== currentRequestId) {
        return;
      }

      // Processar todas as respostas
      const dadosAnuais = processarDadosAnuais(responses, mesesParaBuscar, ano);
      PreencherTabelaAnual(dadosAnuais);
    })
    .catch((error) => {
      console.error("Erro na requisição:", error);

      if (requestId === currentRequestId) {
        alert("Erro ao carregar dados do dashboard anual. Entre em contato com o suporte.");
      }
    })
    .finally(() => {
      if (requestId === currentRequestId) {
        hideLoading();
      }
    });
}

function atualizarHeaderAno(ano) {
  const anoHeader = document.getElementById('ano-header');
  if (anoHeader) {
    const anoAtual = new Date().getFullYear();
    const mesAtual = new Date().getMonth() + 1;
    
    if (parseInt(ano) === anoAtual) {
      const mesesAbreviados = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
      const mesesExibidos = mesesAbreviados.slice(0, mesAtual);
      const ultimoMes = mesesExibidos[mesesExibidos.length - 1];
      anoHeader.textContent = `Ano: ${ano} (Jan-${ultimoMes})`;
    } else {
      anoHeader.textContent = `Ano: ${ano} (Jan-Dez)`;
    }
  }
}

function obterMesesParaBuscar(ano) {
  const anoAtual = new Date().getFullYear();
  const mesAtual = new Date().getMonth() + 1;
  
  const todosMeses = [
    { numero: '01', nome: 'Jan' },
    { numero: '02', nome: 'Fev' },
    { numero: '03', nome: 'Mar' },
    { numero: '04', nome: 'Abr' },
    { numero: '05', nome: 'Mai' },
    { numero: '06', nome: 'Jun' },
    { numero: '07', nome: 'Jul' },
    { numero: '08', nome: 'Ago' },
    { numero: '09', nome: 'Set' },
    { numero: '10', nome: 'Out' },
    { numero: '11', nome: 'Nov' },
    { numero: '12', nome: 'Dez' }
  ];

  // Se for o ano atual, buscar apenas até o mês atual
  if (parseInt(ano) === anoAtual) {
    return todosMeses.slice(0, mesAtual);
  }
  
  // Se for ano anterior ou futuro, buscar todos os meses
  return todosMeses;
}

function processarDadosAnuais(responses, mesesConfig, ano) {
  const planosAgrupados = {};

  // Processar cada resposta (cada mês)
  responses.forEach((response, index) => {
    const mes = mesesConfig[index];
    
    if (response.success && response.data && response.data.planos) {
      response.data.planos.forEach(plano => {
        const codigo = plano.COD_CONTABIL;
        
        // Se é a primeira vez que vemos este plano, inicializar
        if (!planosAgrupados[codigo]) {
          planosAgrupados[codigo] = {
            COD_CONTABIL: codigo,
            NOME: plano.NOME,
            RECEITA_DESPESA: plano.RECEITA_DESPESA,
            totalAnual: 0 // Adicionar total anual para cada plano
          };
          
          // Inicializar todos os meses com zero
          mesesConfig.forEach(m => {
            planosAgrupados[codigo][`mes_${m.numero}`] = 0;
          });
        }
        
        // Adicionar o valor deste mês
        const saldo = parseFloat(plano.saldo) || 0;
        planosAgrupados[codigo][`mes_${mes.numero}`] = saldo;
        
        // Acumular no total anual do plano
        planosAgrupados[codigo].totalAnual += saldo;
      });
    }
  });

  return {
    ano: ano,
    planos: Object.values(planosAgrupados),
    mesesConfig: mesesConfig
  };
}

function PreencherTabelaAnual(dadosAnuais) {
  const tbody = document.querySelector(".table-contas tbody");
  const thead = document.querySelector(".table-contas thead tr");
  
  if (!tbody || !thead) return;

  // Limpar tabela
  tbody.innerHTML = "";
  
  // Manter apenas a coluna do plano financeiro no header
  thead.innerHTML = '<th style="width: 30%">Plano Financeiro</th>';

  if (!dadosAnuais || !dadosAnuais.planos || dadosAnuais.planos.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="${dadosAnuais.mesesConfig.length + 2}" class="text-center py-4">
          Nenhum plano financeiro encontrado para os filtros selecionados.
        </td>
      </tr>
    `;
    return;
  }

  // Configurar cabeçalho com meses e coluna de total anual
  adicionarCabecalhoMeses(thead, dadosAnuais.mesesConfig);

  // Mapa para controlar quais planos têm filhos
  const planosComFilhos = new Map();
  dadosAnuais.planos.forEach((plano) => {
    const partesCodigo = plano.COD_CONTABIL.split(".");
    const nivel = partesCodigo.length;
    
    // Verificar se este plano tem filhos
    const temFilhos = dadosAnuais.planos.some(
      (p) =>
        p.COD_CONTABIL.startsWith(plano.COD_CONTABIL + ".") &&
        p.COD_CONTABIL.split(".").length === nivel + 1
    );
    planosComFilhos.set(plano.COD_CONTABIL, temFilhos);
  });

  // Adicionar planos de nível 1
  const nivel1 = dadosAnuais.planos.filter((p) => p.COD_CONTABIL.split(".").length === 1);
  nivel1.forEach((plano) => adicionarLinhaAnual(plano, 1, dadosAnuais.planos, planosComFilhos, dadosAnuais.mesesConfig, tbody));
}

function adicionarCabecalhoMeses(thead, mesesConfig) {
  mesesConfig.forEach(mes => {
    const th = document.createElement("th");
    th.className = "mes-header";
    th.textContent = mes.nome;
    th.style.textAlign = "right";
    thead.appendChild(th);
  });

  // Adicionar coluna de Total Anual
  const thTotal = document.createElement("th");
  thTotal.className = "mes-header";
  thTotal.textContent = "Total Anual";
  thTotal.style.textAlign = "right";
  thTotal.style.fontWeight = "600";
  thTotal.style.backgroundColor = "#f8f9fa";
  thead.appendChild(thTotal);
}

function adicionarLinhaAnual(plano, nivel, todosPlanos, planosComFilhos, mesesConfig, tbody) {
  const tr = document.createElement("tr");
  const partesCodigo = plano.COD_CONTABIL.split(".");
  const nivelClass = `nivel-${nivel}`;
  const temFilhos = planosComFilhos.get(plano.COD_CONTABIL);
  const exibirInicialmente = nivel <= 2;

  // Determinar se é um totalizador (01, 01.01, 01.02, etc.)
  const isTotalizador = partesCodigo.length <= 2; // Nível 1 e 2 são totalizadores

  // Criar células
  let html = `
    <td>
      <div class="conta-nome ${nivelClass}">
  `;

  // Adicionar toggle button se tiver filhos
  if (temFilhos) {
    html += `<span class="toggle-btn" onclick="toggleContasAnual('${plano.COD_CONTABIL}', this)">+</span>`;
  } else {
    html += `<span class="toggle-btn" style="visibility: hidden;">+</span>`;
  }

  html += `
        <span class="conta-codigo ${isTotalizador ? 'conta-totalizadora' : 'conta-normal'}">${plano.COD_CONTABIL}</span>
        <span class="conta-texto ${isTotalizador ? 'conta-totalizadora' : 'conta-normal'}" ${temFilhos ? 'style="cursor: pointer;" onclick="toggleContasAnual(\'' + plano.COD_CONTABIL + '\', this.parentElement.querySelector(\'.toggle-btn\'))"' : ''}>
          ${plano.NOME}
        </span>
      </div>
    </td>
  `;

  // Adicionar células para cada mês
  mesesConfig.forEach(mes => {
    const valorMes = plano[`mes_${mes.numero}`] || 0;
    const saldo = parseFloat(valorMes);
    const isNegativo = saldo < 0;
    const valorClass = isNegativo ? "valor-negativo" : "valor-positivo";
    const sinal = isNegativo ? "-" : "";
    
    html += `
      <td class="valor-mes ${valorClass} ${isTotalizador ? 'conta-totalizadora' : 'conta-normal'}">
        ${sinal}R$ ${Math.abs(saldo).toLocaleString("pt-BR", { minimumFractionDigits: 2 })}
      </td>
    `;
  });

  // Adicionar célula do Total Anual para este plano
  const totalPlano = parseFloat(plano.totalAnual) || 0;
  const isTotalNegativo = totalPlano < 0;
  const totalClass = isTotalNegativo ? "valor-negativo" : "valor-positivo";
  const sinalTotal = isTotalNegativo ? "-" : "";
  
  html += `
    <td class="valor-mes ${totalClass} ${isTotalizador ? 'conta-totalizadora' : 'conta-normal'}" style="font-weight: ${isTotalizador ? '600' : '500'};">
      ${sinalTotal}R$ ${Math.abs(totalPlano).toLocaleString("pt-BR", { minimumFractionDigits: 2 })}
    </td>
  `;

  tr.innerHTML = html;
  
  // ATRIBUTOS DATA
  tr.id = `plano-${plano.COD_CONTABIL}`;
  tr.setAttribute("data-nivel", nivel.toString());
  tr.setAttribute("data-codigo", plano.COD_CONTABIL);

  // Ajustar a exibição inicial das linhas
  if (!exibirInicialmente && nivel > 2) {
    tr.style.display = "none";
  }

  tbody.appendChild(tr);

  // Adicionar filhos recursivamente
  if (temFilhos) {
    const filhos = todosPlanos.filter(
      (p) =>
        p.COD_CONTABIL.startsWith(plano.COD_CONTABIL + ".") &&
        p.COD_CONTABIL.split(".").length === nivel + 1
    );
    filhos.forEach((filho) => adicionarLinhaAnual(filho, nivel + 1, todosPlanos, planosComFilhos, mesesConfig, tbody));
  }
}

function toggleContasAnual(codigoPai, btn) {
  if (event) {
    event.stopPropagation();
  }

  const todasLinhas = document.querySelectorAll(".table-contas tbody tr");
  const estaExpandido = btn.textContent === '-';

  // Encontrar todas as linhas filhas diretas
  let algumFilhoEncontrado = false;
  
  for (let i = 0; i < todasLinhas.length; i++) {
    const tr = todasLinhas[i];
    const codigo = tr.getAttribute("data-codigo");
    const nivelAttr = tr.getAttribute("data-nivel");
    
    // Verificação robusta dos atributos
    if (!codigo || !nivelAttr) {
      continue; // Pula linhas que não têm os atributos necessários
    }
    
    const nivel = parseInt(nivelAttr);
    if (isNaN(nivel)) {
      continue; // Pula se não for um número válido
    }
    
    const nivelPai = codigoPai.split(".").length;
    
    // Verificar se é um filho direto (exatamente um nível abaixo)
    const isFilhoDireto = 
      typeof codigo === 'string' &&
      codigo.startsWith(codigoPai + ".") && 
      nivel === nivelPai + 1 &&
      codigo.split(".").length === nivelPai + 1;

    if (isFilhoDireto) {
      algumFilhoEncontrado = true;
      
      if (estaExpandido) {
        // Recolher: esconder esta linha e todos os seus filhos
        tr.style.display = "none";
        recolherFilhosRecursivamente(codigo);
      } else {
        // Expandir: mostrar apenas os filhos diretos
        tr.style.display = "";
      }
    }
  }

  // Atualizar o texto do botão
  if (algumFilhoEncontrado) {
    btn.textContent = estaExpandido ? "+" : "-";
  } else {
    console.log('Nenhum filho direto encontrado');
  }
}

function recolherFilhosRecursivamente(codigoPai) {
  const todasLinhas = document.querySelectorAll(".table-contas tbody tr");

  for (let i = 0; i < todasLinhas.length; i++) {
    const tr = todasLinhas[i];
    const codigo = tr.getAttribute("data-codigo");
    const nivelAttr = tr.getAttribute("data-nivel");
    
    // Verificação robusta dos atributos
    if (!codigo || !nivelAttr) {
      continue;
    }
    
    const nivel = parseInt(nivelAttr);
    if (isNaN(nivel)) {
      continue;
    }
    
    const nivelPai = codigoPai.split(".").length;

    // Verificar se é um descendente (qualquer nível abaixo)
    if (typeof codigo === 'string' && codigo.startsWith(codigoPai + ".") && nivel > nivelPai) {
      tr.style.display = "none";
      
      // Atualizar o botão do filho para "+" (recolhido)
      const toggleBtn = tr.querySelector(".toggle-btn");
      if (toggleBtn && toggleBtn.style.visibility !== "hidden") {
        toggleBtn.textContent = "+";
      }
      
      // Recolher os descendentes recursivamente
      recolherFilhosRecursivamente(codigo);
    }
  }
}

function ConfigurarEventListeners() {
  const filialSelect = document.getElementById("filial");
  const projetoSelect = document.getElementById("projeto");
  const anoSelect = document.getElementById("ano");
  const todosPlanosCheckbox = document.getElementById("todos_planos");
  const competenciaPagamentoCheckbox = document.getElementById("competencia_pagamento");
  const btnFiltrar = document.getElementById("btn-filtrar");
  const btnLimpar = document.getElementById("btn-limpar");

  if (anoSelect) {
    anoSelect.addEventListener("change", function () {
      atualizarHeaderAno(this.value);
    });
  }

  function executarFiltro() {
    if (anoSelect) atualizarHeaderAno(anoSelect.value);
    CarregarDadosAnuais();
  }

  if (btnFiltrar) {
    btnFiltrar.addEventListener("click", executarFiltro);
  }

  // Permite filtrar ao pressionar Enter nos filtros
  const filterElements = [filialSelect, projetoSelect, anoSelect, todosPlanosCheckbox, competenciaPagamentoCheckbox];
  filterElements.forEach((el) => {
    if (el) {
      el.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.keyCode === 13) {
          e.preventDefault();
          executarFiltro();
        }
      });
    }
  });

  if (btnLimpar) {
    btnLimpar.addEventListener("click", () => {
      if (filialSelect) $(filialSelect).val("").trigger('change.select2');
      if (projetoSelect) $(projetoSelect).val("").trigger('change.select2');
      if (anoSelect) anoSelect.value = new Date().getFullYear();
      if (todosPlanosCheckbox) todosPlanosCheckbox.checked = false;
      if (competenciaPagamentoCheckbox) competenciaPagamentoCheckbox.checked = false;

      atualizarHeaderAno(anoSelect.value);
      CarregarDadosAnuais();
    });
  }

  // Configura exportação Excel
  configurarExportacaoExcel();
}

// Função para exportar para Excel
function exportarParaExcel() {
    if (isLoading) {
        alert('Aguarde o carregamento dos dados antes de exportar.');
        return;
    }

    const filialSelect = document.getElementById("filial");
    const projetoSelect = document.getElementById("projeto");
    const anoSelect = document.getElementById("ano");
    
    const filialTexto = filialSelect.options[filialSelect.selectedIndex]?.text || 'Todas as filiais';
    const projetoTexto = projetoSelect.options[projetoSelect.selectedIndex]?.text || 'Todos os projetos';
    const anoTexto = anoSelect.value;

    // Obtem dados da tabela
    const tabela = document.getElementById('tabela-anual');
    if (!tabela) {
        alert('Nenhum dado disponível para exportar.');
        return;
    }

    // Cria workbook
    const wb = XLSX.utils.book_new();
    
    // Preparar dados para exportação
    const dadosExportacao = prepararDadosParaExportacao(tabela, filialTexto, projetoTexto, anoTexto);
    
    // Cria worksheet
    const ws = XLSX.utils.aoa_to_sheet(dadosExportacao);
    
    // Adiciona worksheet ao workbook
    XLSX.utils.book_append_sheet(wb, ws, 'Competência Anual');

    // Ajusta largura das colunas (autoSize)
    const colWidths = dadosExportacao.reduce((widths, row) => {
        row.forEach((cell, i) => {
            const value = cell ? cell.toString() : '';
            const length = typeof cell === 'number' ? value.length + 5 : value.length;
            widths[i] = Math.max(widths[i] || 0, length);
        });
        return widths;
    }, []);

    ws['!cols'] = colWidths.map(w => ({ wch: Math.min(Math.max(w + 2, 12), 50) }));
    
    // Gera nome do arquivo
    const fileName = `Relatorio_Competencia_Anual_${anoTexto}_${new Date().getTime()}.xlsx`;
    
    XLSX.writeFile(wb, fileName);
}

// Função para preparar dados para exportação
function prepararDadosParaExportacao(tabela, filialTexto, projetoTexto, anoTexto) {
    const dados = [];
    
    dados.push(['Relatório de Competência Anual']);
    dados.push([]);
    dados.push(['Filial:', filialTexto]);
    dados.push(['Projeto:', projetoTexto]);
    dados.push(['Ano:', anoTexto]);
    dados.push(['Data de Exportação:', new Date().toLocaleString('pt-BR')]);
    dados.push([]);
    
    // Obtem cabeçalhos da tabela
    const thead = tabela.querySelector('thead tr');
    const headers = ['Plano Financeiro'];
    
    if (thead) {
        const ths = thead.querySelectorAll('th.mes-header');
        ths.forEach(th => {
            headers.push(th.textContent.trim());
        });
    }
    
    dados.push(headers);
    
    // Obtem TODOS os dados do corpo da tabela
    const tbody = tabela.querySelector('tbody');
    const todasLinhas = tbody.querySelectorAll('tr');
    
    const dadosHierarquicos = [];
    
    todasLinhas.forEach(linha => {
        const linhaDados = {
            codigo: linha.getAttribute('data-codigo'),
            nivel: parseInt(linha.getAttribute('data-nivel') || '1'),
            dados: []
        };
        
        const contaNome = linha.querySelector('.conta-nome');
        if (contaNome) {
            const codigo = contaNome.querySelector('.conta-codigo')?.textContent || '';
            const nome = contaNome.querySelector('.conta-texto')?.textContent || '';
            linhaDados.dados.push(`${codigo} - ${nome}`.trim());
        } else {
            linhaDados.dados.push('');
        }
        
        const valores = linha.querySelectorAll('.valor-mes');
        valores.forEach(valor => {
            const texto = valor.textContent.trim();
            let valorNumerico = 0;
            
            if (texto) {
                const textoLimpo = texto.replace('R$', '').replace(/\s/g, '').replace(/\./g, '').replace(',', '.');
                valorNumerico = parseFloat(textoLimpo) || 0;
                
                if (texto.startsWith('-')) {
                    valorNumerico = -Math.abs(valorNumerico);
                }
            }
            
            linhaDados.dados.push(valorNumerico);
        });
        
        dadosHierarquicos.push(linhaDados);
    });
    
    dadosHierarquicos.sort((a, b) => {
        return a.codigo.localeCompare(b.codigo, undefined, { numeric: true });
    });
    
    dadosHierarquicos.forEach(linha => {
        dados.push(linha.dados);
    });
    
    return dados;
}

// Adiciona evento de clique ao botão de exportação
function configurarExportacaoExcel() {
    const btnExportar = document.getElementById('btn-exportar-excel');
    if (btnExportar) {
        btnExportar.addEventListener('click', exportarParaExcel);
    }
}

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

  fetch(api_url + '/dashboard_permissoes/competencia_anual', { method: 'GET', headers: headers })
  .then(async (response) => {
      if (!response.ok) {
          const data = await response.json();
          throw new Error(data.message || 'Sem permissão');
      }
      return response.json();
  })
  .then(() => {
      Swal.close();
      
      // Verifica se a biblioteca XLSX está carregada
      if (typeof XLSX === 'undefined') {
          console.error('Biblioteca XLSX não carregada. A exportação Excel não funcionará.');
      }

      Promise.all([GetEmpresa(), GetProjeto()])
        .then(() => {
          ConfigurarEventListeners();
          CarregarDadosAnuais();
        })
        .catch((error) => {
          console.error('Erro ao inicializar dashboard anual:', error);
          hideLoading();
        });
  })
  .catch((error) => {
      Swal.fire({ icon: 'error', title: 'Acesso Negado', html: error.message, allowOutsideClick: false })
      .then(() => { location.href = base_url + 'financial/dashboard'; });
  });
});

window.toggleContasAnual = toggleContasAnual;
