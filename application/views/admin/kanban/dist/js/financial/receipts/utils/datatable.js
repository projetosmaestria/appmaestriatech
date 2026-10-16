import { api_get } from "../../../helpers/api/get.js";

// Variável global para controle do modo
let isMobileMode = false;
let currentDataTable = null;
let currentPage = 1;
let pageSize = 10;
let totalRecords = 0;
let totalPages = 0;
let currentData = [];
let totalValorPaginaAtual = 0;
let valoresPorCodigo = {};

// Função para detectar se é mobile
function detectMobile() {
    return window.innerWidth <= 768;
}

// Função auxiliar para formatar datas
function formatarData(data) {
    if (!data || data === '0000-00-00' || data === '' || data === null || data === '1899-12-30') {
        return '';
    }
    
    const partes = data.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    
    return data;
}

// Função auxiliar para formatar valores monetários
function formatarValor(valor) {
    if (!valor && valor !== 0) return '';
    const num = parseFloat(valor);
    if (isNaN(num)) return valor;
    return 'R$ ' + num.toLocaleString('pt-BR', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });
}

// Função alternativa mais simples para determinar situação
function determinarSituacao(vencimento, dataPagamento) {
    // Se tem data de pagamento, está PAGO
    if (dataPagamento && dataPagamento !== '0000-00-00' && dataPagamento !== '' && 
        dataPagamento !== null && dataPagamento !== '1899-12-30') {
        return { 
            html: '<span class="badge badge-success">PAGO</span>',
            text: 'PAGO',
            class: 'success'
        };
    }
    
    // Se não tem vencimento, está SEM VENCIMENTO
    if (!vencimento || vencimento === '0000-00-00' || vencimento === '') {
        return { 
            html: '<span class="badge badge-secondary">SEM VENCIMENTO</span>',
            text: 'SEM VENCIMENTO',
            class: 'secondary'
        };
    }
    
    // Obter data atual no formato YYYY-MM-DD
    const hoje = new Date();
    const hojeStr = hoje.toISOString().split('T')[0];
    
    // Comparar strings diretamente (YYYY-MM-DD)
    if (vencimento < hojeStr) {
        return { 
            html: '<span class="badge badge-danger">VENCIDO</span>',
            text: 'VENCIDO',
            class: 'danger'
        };
    } else {
        return { 
            html: '<span class="badge badge-warning">À VENCER</span>',
            text: 'À VENCER',
            class: 'warning'
        };
    }
}

// Função auxiliar para montar as ações disponíveis conforme a situação do título
function montarAcoes(codigo, dataPagamento) {
    const pago = dataPagamento && dataPagamento !== '0000-00-00' && dataPagamento !== '' &&
        dataPagamento !== null && dataPagamento !== '1899-12-30';

    const itens = [];
    if (!pago) {
        itens.push(`<button type="button" class="dropdown-item" onclick="verificarAcesso('baixar', '${base_url}/financial/receipt/baixar/${codigo}')">Baixar</button>`);
    } else {
        itens.push(`<a class="dropdown-item" href="${base_url}/financial/receipt/estornar/${codigo}">Estornar</a>`);
    }
    itens.push(`<a class="dropdown-item" href="${base_url}/financial/receipt/boleto/${codigo}">Boleto</a>`);
    return itens;
}

// Função para criar cards
function criarCardsRecebimentos(data, paginaAtual, totalPaginas, totalRegistros) {
    const container = document.getElementById('receipts-cards');
    if (!container) return;
    
    // Limpar container principal
    container.innerHTML = '';

    // Total da página atual
    const totalValorPagina = (data || []).reduce((soma, item) => soma + (parseFloat(item.valor) || 0), 0);
    const totalDiv = document.createElement('div');
    totalDiv.className = 'text-right font-weight-bold py-2';
    totalDiv.textContent = `Total desta página: ${formatarValor(totalValorPagina)}`;
    container.appendChild(totalDiv);

    // Criar div principal para os cards
    const cardsContainer = document.createElement('div');
    cardsContainer.id = 'cards-content';
    
    // Adicionar cards
    if (!data || data.length === 0) {
        cardsContainer.innerHTML = `
            <div class="text-center py-4">
                <p class="text-muted">Nenhum registro encontrado</p>
            </div>
        `;
    } else {
        data.forEach(item => {
            const situacao = determinarSituacao(item.vencimento, item.data_pagamento);
            const clienteTexto = item.cliente ? `${item.cliente.codigo || ''} ${item.cliente.nome ? '/ ' + item.cliente.nome : ''}`.trim() : '-';
            const filialTexto = item.filial ? `${item.filial.codigo || ''} ${item.filial.nome_reduz ? '/ ' + item.filial.nome_reduz : ''}`.trim() : '-';
            
            const card = document.createElement('div');
            card.className = 'receipt-card';
            card.innerHTML = `
                <div class="receipt-card-header bg-primary">
                    <div>
                        <strong>Código: ${item.codigo}</strong>
                        <div style="font-size: 0.85rem;">Documento: ${item.documento || '-'}</div>
                    </div>
                    <div>
                        <span class="receipt-card-badge receipt-card-badge-${situacao.class}">
                            ${situacao.text}
                        </span>
                    </div>
                </div>
                <div class="receipt-card-body">
                    <div class="receipt-card-row">
                        <div class="receipt-card-label">Filial:</div>
                        <div class="receipt-card-value">${filialTexto}</div>
                    </div>
                    <div class="receipt-card-row">
                        <div class="receipt-card-label">Núm. Bloqueto:</div>
                        <div class="receipt-card-value">${item.num_bloqueto || '-'}</div>
                    </div>
                    <div class="receipt-card-row">
                        <div class="receipt-card-label">Cliente:</div>
                        <div class="receipt-card-value">${clienteTexto}</div>
                    </div>
                    <div class="receipt-card-row">
                        <div class="receipt-card-label">Valor:</div>
                        <div class="receipt-card-value">${formatarValor(item.valor)}</div>
                    </div>
                    <div class="receipt-card-row">
                        <div class="receipt-card-label">Vencimento:</div>
                        <div class="receipt-card-value">${formatarData(item.vencimento)}</div>
                    </div>
                    <div class="receipt-card-row">
                        <div class="receipt-card-label">Lançamento:</div>
                        <div class="receipt-card-value">${formatarData(item.data_lancamento)}</div>
                    </div>
                    <div class="receipt-card-row">
                        <div class="receipt-card-label">Pagamento:</div>
                        <div class="receipt-card-value">${formatarData(item.data_pagamento)}</div>
                    </div>
                    <div class="receipt-card-actions">
                        <button type="button" onclick="verificarAcesso('editar', '${base_url}/financial/receipts/edit/${item.codigo}')" class="btn btn-sm btn-default">Editar</button>
                        ${montarAcoes(item.codigo, item.data_pagamento).map(a => a.replace('dropdown-item', 'btn btn-sm btn-default')).join('')}
                    </div>
                </div>
            `;
            cardsContainer.appendChild(card);
        });
    }
    
    container.appendChild(cardsContainer);
    
    // Criar paginação para mobile
    const paginationDiv = document.createElement('div');
    paginationDiv.className = 'receipts-cards-pagination';
    
    if (totalRegistros > 0) {
        const inicio = ((paginaAtual - 1) * pageSize) + 1;
        const fim = Math.min(paginaAtual * pageSize, totalRegistros);
        
        paginationDiv.innerHTML = `
            <button class="btn btn-sm btn-default ${paginaAtual === 1 ? 'disabled' : ''}" id="prevPageBtn" ${paginaAtual === 1 ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i> Anterior
            </button>
            <span class="page-info">${inicio}-${fim} de ${totalRegistros}</span>
            <button class="btn btn-sm btn-default ${paginaAtual === totalPaginas ? 'disabled' : ''}" id="nextPageBtn" ${paginaAtual === totalPaginas ? 'disabled' : ''}>
                Próxima <i class="fas fa-chevron-right"></i>
            </button>
        `;
        
        // Adicionar eventos de clique na paginação
        setTimeout(() => {
            const prevBtn = document.getElementById('prevPageBtn');
            const nextBtn = document.getElementById('nextPageBtn');
            
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    if (paginaAtual > 1) {
                        currentPage--;
                        recarregarCards();
                    }
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    if (paginaAtual < totalPaginas) {
                        currentPage++;
                        recarregarCards();
                    }
                });
            }
        }, 100);
    } else {
        paginationDiv.innerHTML = '<span class="text-muted">Nenhum registro encontrado</span>';
    }
    
    container.appendChild(paginationDiv);
}

// Função para carregar dados (usada tanto para tabela quanto para cards)
async function carregarDados(params_get, isForCards = false) {
    try {
        const response = await api_get("/contas_receber/", params_get);
        const totalValorPagina = (response.data || []).reduce((soma, value) => soma + (parseFloat(value.valor) || 0), 0);

        if (isForCards) {
            return {
                data: response.data,
                total: response.total,
                totalValorPagina
            };
        }

        return {
            objs: response.data.map(value => {
                const situacao = determinarSituacao(value.vencimento, value.data_pagamento);
                const clienteTexto = value.cliente ? `${value.cliente.codigo || ''} ${value.cliente.nome ? '/ ' + value.cliente.nome : ''}`.trim() : '-';
                const filialTexto = value.filial ? `${value.filial.codigo || ''} ${value.filial.nome_reduz ? '/ ' + value.filial.nome_reduz : ''}`.trim() : '-';

                return [
                    value.codigo,
                    value.codigo,
                    filialTexto,
                    value.documento,
                    value.num_bloqueto,
                    clienteTexto,
                    formatarValor(value.valor),
                    formatarData(value.vencimento),
                    formatarData(value.data_lancamento),
                    formatarData(value.data_pagamento),
                    situacao.html,
                    `<div class="btn-group">
                        <button type="button" onclick="verificarAcesso('editar', '${base_url}/financial/receipts/edit/${value.codigo}')" class="btn btn-sm btn-default">Editar</button>
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" role="menu">
                            ${montarAcoes(value.codigo, value.data_pagamento).join('')}
                        </div>
                    </div>`
                ];
            }),
            data: response.data,
            total: response.total,
            totalValorPagina
        };
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
        return isForCards ? { data: [], total: 0, totalValorPagina: 0 } : { objs: [], data: [], total: 0, totalValorPagina: 0 };
    }
}

// Função auxiliar para obter parâmetros do filtro
function obterParametrosFiltro() {
    var situacao = $("#situacao").val();
    var data_inicial = $("#data_inicial").val();
    var data_final = $("#data_final").val();
    var filtrar_pendentes = !situacao || situacao === "pendentes";

    return {
        situacao: situacao,
        data_inicial: data_inicial,
        data_final: data_final,
        filtrar_pendentes: filtrar_pendentes,
        filtrar_vencimento_inicial: !!data_inicial,
        filtrar_vencimento_final: !!data_final
    };
}

// Atualiza o badge com a quantidade de filtros ativos além do padrão (situação = pendentes)
function atualizarBadgeFiltrosAtivos() {
    const situacao = $("#situacao").val();
    const dataInicial = $("#data_inicial").val();
    const dataFinal = $("#data_final").val();
    const texto = $("input[name=qtxt]").val();

    let ativos = 0;
    if (situacao && situacao !== 'pendentes') ativos++;
    if (dataInicial) ativos++;
    if (dataFinal) ativos++;
    if (texto) ativos++;

    const $badge = $("#badgeFiltrosAtivos");
    if (ativos > 0) {
        $badge.text(ativos).show();
    } else {
        $badge.hide();
    }
}

// Marca como ativa a pill de situação correspondente ao valor selecionado no filtro
function sincronizarPillsSituacao() {
    const situacao = $("#situacao").val() || 'pendentes';
    $('.filtro-situacao').removeClass('active');
    $('.filtro-situacao[data-situacao="' + situacao + '"]').addClass('active');
}

// Recalcula e exibe o total dos registros marcados (checkbox) na página atual
function atualizarTotalSelecionados() {
    let totalSelecionado = 0;
    $('#datatable tbody input.dt-checkboxes:checked').each(function() {
        const codigo = $(this).closest('tr').find('td:nth-child(2)').text().trim();
        totalSelecionado += valoresPorCodigo[codigo] || 0;
    });
    $("#total-selecionados-valor").text(formatarValor(totalSelecionado));
}

// Move os botões de exportação gerados pelo DataTables Buttons para o dropdown "Outras Opções"
function moverBotoesExportacaoParaDropdown() {
    if (!currentDataTable || !currentDataTable.buttons) return;
    const $container = $('#exportButtonsContainer');
    $container.empty();
    currentDataTable.buttons().nodes().each(function() {
        $(this).addClass('dropdown-item');
        $container.append(this);
    });
}

// Função para recarregar cards
async function recarregarCards() {
    const filtros = obterParametrosFiltro();
    const params_get = {
        offset: (currentPage - 1) * pageSize,
        limit: pageSize,
        ordem: JSON.stringify([{ campo: "codigo", ordem: "desc" }]),
        texto: $("input[name=qtxt]").val(),
        ...filtros
    };
    
    const resultado = await carregarDados(params_get, true);
    totalRecords = resultado.total;
    totalPages = Math.ceil(totalRecords / pageSize);
    currentData = resultado.data;
    
    criarCardsRecebimentos(currentData, currentPage, totalPages, totalRecords);
}

function get_params_modal() {
  var params = {
    responsive: true,
    lengthChange: true,
    pageLength: 10,
    autoWidth: false,
    ordering: true,
    language: {
      url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json",
    },
    searching: true,
    processing: true,
    serverSide: true,
    columnDefs: [
      { responsivePriority: 1, targets: 0 },
      { responsivePriority: 2, targets: 1 },
      { responsivePriority: 3, targets: 2 },
      { responsivePriority: 4, targets: 3 },
      { responsivePriority: 5, targets: 4 },
      { responsivePriority: 6, targets: 5 },
      { 
        responsivePriority: 7, 
        targets: 6,
        className: "text-right"
      },
      { responsivePriority: 8, targets: 7 },
      { responsivePriority: 9, targets: 8 },
      { responsivePriority: 10, targets: 9 },
    ],
    lengthMenu: [
      [10, 25, 50],
      [10, 25, 50],
    ],
    aaSorting: [[0, "desc"]],
    ajax: async (data, callback) => {
      var column_to_order = "";
      if (data.order[0].column == 0) column_to_order = "codigo";
      if (data.order[0].column == 1) column_to_order = "filial";
      if (data.order[0].column == 2) column_to_order = "documento";
      if (data.order[0].column == 3) column_to_order = "num_bloqueto";
      if (data.order[0].column == 4) column_to_order = "situacao";
      if (data.order[0].column == 5) column_to_order = "cliente";
      if (data.order[0].column == 6) column_to_order = "valor";
      if (data.order[0].column == 7) column_to_order = "vencimento";
      if (data.order[0].column == 8) column_to_order = "data_lancamento";
      if (data.order[0].column == 9) column_to_order = "data_pagamento";

      var params_get = {
        offset: data.start,
        limit: data.length,
        ordem: JSON.stringify([
          { campo: column_to_order, ordem: data.order[0].dir },
        ]),
        texto: data.search.value,
      };

      api_get("/contas_receber/", params_get).then((response) => {
        const objs = [];

        $.each(response.data, function (index, value) {
          let obj = [];
          obj.push(value.codigo);
          const filialTexto = value.filial ? `${value.filial.codigo || ''} ${value.filial.nome_reduz ? '/ ' + value.filial.nome_reduz : ''}`.trim() : '-';
          obj.push(filialTexto);
          obj.push(value.documento);
          obj.push(value.num_bloqueto);
          const clienteTexto = value.cliente ? `${value.cliente.codigo || ''} ${value.cliente.nome ? '/ ' + value.cliente.nome : ''}`.trim() : '-';
          obj.push(clienteTexto);
          obj.push(formatarValor(value.valor));
          obj.push(formatarData(value.vencimento));
          obj.push(formatarData(value.data_lancamento));
          obj.push(formatarData(value.data_pagamento));
          const situacao = determinarSituacao(value.vencimento, value.data_pagamento);
          obj.push(situacao.html);
          objs.push(obj);
        });
        var json_data = {
          draw: data.draw,
          recordsTotal: response.total,
          recordsFiltered: response.total,
          data: objs,
        };
        callback(json_data);
      });
    },
  };
  return params;
}

function get_params_list() {
  // Configuração base para desktop
  var baseParams = {
    responsive: true,
    lengthChange: true,
    pageLength: 10,
    autoWidth: false,
    ordering: true,
    language: {
      url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json",
    },
    searching: false,
    processing: true,
    serverSide: true,
    columnDefs: [
      {responsivePriority: 1, targets: 0, checkboxes: { selectRow: true }, orderable: false},
      { responsivePriority: 2, targets: 1 },
      { responsivePriority: 3, targets: 2 },
      { responsivePriority: 4, targets: 3 },
      { responsivePriority: 5, targets: 4 },
      { responsivePriority: 6, targets: 5 },
      { responsivePriority: 7, targets: 6 },
      { responsivePriority: 8, targets: 7, className: "text-right"},
      { responsivePriority: 9, targets: 8 },
      { responsivePriority: 10, targets: 9 },
      { responsivePriority: 11, targets: 10 },
      { responsivePriority: 12, targets: -1 },
    ],
    select: { style: "multi" },
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "Todos"],
    ],
    sDom: "Brtipl",
    buttons: [
      {
        extend: "excelHtml5",
        exportOptions: {
          columns: [":visible:not(:eq(0), :eq(-1))"],
        },
      },
      {
        extend: "csvHtml5",
        exportOptions: {
          columns: [":visible:not(:eq(0), :eq(-1))"],
        },
      },
      {
        extend: "pdfHtml5",
        exportOptions: {
          columns: [":visible:not(:eq(0), :eq(-1))"],
        },
      },
    ],
    aaSorting: [[7, "asc"]],
    ajax: async (data, callback) => {
      var column_to_order = "";
      if (data.order[0].column == 0) column_to_order = "codigo";
      if (data.order[0].column == 1) column_to_order = "codigo";
      if (data.order[0].column == 2) column_to_order = "filial";
      if (data.order[0].column == 3) column_to_order = "documento";
      if (data.order[0].column == 4) column_to_order = "num_bloqueto";
      if (data.order[0].column == 5) column_to_order = "cliente";
      if (data.order[0].column == 6) column_to_order = "valor";
      if (data.order[0].column == 7) column_to_order = "vencimento";
      if (data.order[0].column == 8) column_to_order = "data_lancamento";
      if (data.order[0].column == 9) column_to_order = "data_pagamento";
      if (data.order[0].column == 10) column_to_order = "situacao";

      // Obter valores dos filtros
      var situacao = $("#situacao").val();
      var agrupamento = $("#agrupamento").val();
      var data_inicial = $("#data_inicial").val();
      var data_final = $("#data_final").val();
      var filtrar_pendentes = !situacao || situacao === "pendentes";

      var params_get = {
        offset: data.start,
        limit: data.length,
        ordem: JSON.stringify([
          { campo: column_to_order, ordem: data.order[0].dir },
        ]),
        texto: $("input[name=qtxt]").val(),
        situacao: situacao,
        agrupamento: agrupamento,
        data_inicial: data_inicial,
        data_final: data_final,
        filtrar_pendentes: filtrar_pendentes,
        filtrar_vencimento_inicial: !!data_inicial,
        filtrar_vencimento_final: !!data_final
      };

      const resultado = await carregarDados(params_get, false);
      totalValorPaginaAtual = resultado.totalValorPagina;
      $("#total-pagina-valor").text(formatarValor(totalValorPaginaAtual));

      valoresPorCodigo = {};
      (resultado.data || []).forEach(value => {
        valoresPorCodigo[value.codigo] = parseFloat(value.valor) || 0;
      });
      $("#total-selecionados-valor").text(formatarValor(0));

      callback({
        draw: data.draw,
        recordsTotal: resultado.total,
        recordsFiltered: resultado.total,
        data: resultado.objs
      });
    },
    initComplete: function(settings, json) {
      currentDataTable = this.api();

      // Recalcula o total dos registros selecionados a cada (des)marcação do checkbox
      $('#datatable').off('change.totalSelecionados').on('change.totalSelecionados', 'input.dt-checkboxes', function() {
        atualizarTotalSelecionados();
      });

      // Verificar se é mobile inicialmente
      const isMobile = detectMobile();
      
      // Aplicar configurações baseadas no dispositivo
      if (isMobile) {
        // Remover botões de exportação e paginação do DataTables para mobile
        if (currentDataTable.buttons) {
          currentDataTable.buttons().destroy();
        }
        
        // Ocultar elementos de paginação do DataTables
        $('.dataTables_length').hide();
        $('.dataTables_info').hide();
        $('.dataTables_paginate').hide();
        
        isMobileMode = true;
        currentPage = 1;
        recarregarCards();
      } else {
        // Mostrar elementos de paginação do DataTables
        $('.dataTables_length').show();
        $('.dataTables_info').show();
        $('.dataTables_paginate').show();

        moverBotoesExportacaoParaDropdown();

        isMobileMode = false;
      }
      
      // Recarrega a listagem (tabela ou cards) e atualiza indicadores de filtro
      function recarregarListagem() {
        currentPage = 1; // Resetar para primeira página

        if (detectMobile()) {
          recarregarCards();
        } else {
          currentDataTable.ajax.reload();
        }

        atualizarBadgeFiltrosAtivos();
        sincronizarPillsSituacao();
      }

      // Adicionar evento ao botão Buscar
      $('#btnBuscar').on('click', function() {
        recarregarListagem();
      });

      // Adicionar evento ao botão Limpar
      $('#btnLimparBusca').on('click', function() {
        $("input[name=qtxt]").val('');
        $("#data_inicial").val('');
        $("#data_final").val('');
        $("#situacao").val('pendentes');

        if ($("#agrupamento").val() === "originadores") {
            $("#agrupamento").val("gerado");
        }

        recarregarListagem();
      });

      // Pills de filtro rápido por situação
      $('.filtro-situacao').on('click', function() {
        $("#situacao").val($(this).data('situacao'));
        recarregarListagem();
      });

      atualizarBadgeFiltrosAtivos();
      sincronizarPillsSituacao();

      // Detectar redimensionamento da tela
      let resizeTimer;
      $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
          const agoraMobile = detectMobile();
          
          if (agoraMobile && !isMobileMode) {
            // Se mudou para mobile
            isMobileMode = true;
            currentPage = 1;
            
            // Remover botões de exportação
            if (currentDataTable.buttons) {
              currentDataTable.buttons().destroy();
            }
            
            // Ocultar elementos de paginação do DataTables
            $('.dataTables_length').hide();
            $('.dataTables_info').hide();
            $('.dataTables_paginate').hide();
            
            // Carregar cards
            recarregarCards();
          } else if (!agoraMobile && isMobileMode) {
            // Se mudou para desktop
            isMobileMode = false;
            
            // Mostrar elementos de paginação do DataTables
            $('.dataTables_length').show();
            $('.dataTables_info').show();
            $('.dataTables_paginate').show();
            
            // Recarregar tabela com configurações completas
            currentDataTable.destroy();

            // Recriar DataTable com configurações completas (já inclui botões de exportação)
            const newParams = get_params_list();
            currentDataTable = $("#datatable").DataTable(newParams);
            currentDataTable.ajax.reload();
          }
        }, 250);
      });
    },
    drawCallback: function(settings) {
      // Se for mobile, garantir que a tabela esteja escondida
      if (detectMobile()) {
        $('#datatable').hide();
        $('#receipts-cards').show();
        
        // Ocultar elementos de paginação do DataTables
        $('.dataTables_length').hide();
        $('.dataTables_info').hide();
        $('.dataTables_paginate').hide();
      } else {
        $('#datatable').show();
        $('#receipts-cards').hide();
        
        // Mostrar elementos de paginação do DataTables
        $('.dataTables_length').show();
        $('.dataTables_info').show();
        $('.dataTables_paginate').show();
      }
    }
  };
  
  return baseParams;
}

function get_html_list() {
  return (
    '<div class="receipts-cards-container" id="receipts-cards">' +
    '    <!-- Cards serão carregados aqui para dispositivos móveis -->' +
    '</div>' +
    '<table id="datatable" class="table table-striped">' +
    "<thead>" +
    "<tr>" +
    "<th></th>" +
    "<th>Cód.</th>" +
    "<th>Filial</th>" +
    "<th>Documento</th>" +
    "<th>Num. Bloqueto</th>" +
    "<th>Cliente</th>" +
    "<th>Valor</th>" +
    "<th>Vencimento</th>" +
    "<th>Lançamento</th>" +
    "<th>Pagamento</th>" +
    "<th>Situação</th>" +
    "<th>Ações</th>" +
    "</tr>" +
    "</thead>" +
    "<tbody></tbody>" +
    "<tfoot>" +
    "<tr>" +
    '<th colspan="6" class="text-right">Total desta página:</th>' +
    '<th id="total-pagina-valor" class="text-right"></th>' +
    "<th></th><th></th><th></th><th></th><th></th>" +
    "</tr>" +
    "<tr>" +
    '<th colspan="6" class="text-right">Total selecionados:</th>' +
    '<th id="total-selecionados-valor" class="text-right">R$ 0,00</th>' +
    "<th></th><th></th><th></th><th></th><th></th>" +
    "</tr>" +
    "</tfoot>" +
    "</table>"
  );
}

function get_html_modal() {
  return (
    '<div class="modal fade" id="modal-dt-receipt_type" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">' +
    '<div class="modal-dialog modal-xl" role="document">' +
    '<div class="modal-content">' +
    '<div class="modal-header">' +
    '<h5 class="modal-title">Tipo de Recebimento</h5>' +
    '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
    '<span aria-hidden="true">&times;</span>' +
    "</button>" +
    "</div>" +
    '<div class="modal-body">' +
    '<table id="datatable" class="table table-striped" style="cursor:pointer">' +
    "<thead>" +
    "<tr>" +
    "<th>Cód.</th>" +
    "<th>Filial</th>" +
    "<th>Documento</th>" +
    "<th>Num. Bloqueto</th>" +
    "<th>Cliente</th>" +
    "<th>Valor</th>" +
    "<th>Vencimento</th>" +
    "<th>Lançamento</th>" +
    "<th>Pagamento</th>" +
    "<th>Situação</th>" +
    "</tr>" +
    "</thead>" +
    "<tbody></tbody>" +
    "</table>" +
    "</div>" +
    '<div class="modal-footer">' +
    '<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>' +
    "</div>" +
    "</div>" +
    "</div>" +
    "</div>"
  );
}

function datatable_html(is_modal = false) {
  /* gerando html para listagem em tela */
  if (is_modal == false) {
    var html = get_html_list();
  } else {
  /* gerando html para listagem em modal */
    var html = get_html_modal();
  }
  return html;
}

function datatable(is_modal = false) {
  /* gerando parâmetros para listagem em tela */
  if (is_modal == false) {
    var params = get_params_list();
  } else {
  /* gerando parâmetros para listagem em modal */
    var params = get_params_modal();
  }

  return $("#datatable").DataTable(params);
}

export { datatable_html, datatable, recarregarCards, formatarData, formatarValor, determinarSituacao, montarAcoes };