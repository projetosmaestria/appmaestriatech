import { api_get } from "../../helpers/api/get.js";

document.addEventListener("DOMContentLoaded", function () {
    if (!sessionStorage.getItem('dashboard_recarregado')) {
        sessionStorage.setItem('dashboard_recarregado', 'true');
        window.location.reload();
    } else {
        sessionStorage.removeItem('dashboard_recarregado');
    }
});

let chartGaugeInstance = null;
let chartSparklineInstance = null;
let chartEvolucaoMensalInstance = null;
let chartFatDepartamentoInstance = null;
let chartFatVendedorInstance = null;

// Exibe a tela de carregamento com animacao de transicao
function showLoading() {
    const loading = document.getElementById("loading");
    if (loading) {
        $(loading).fadeIn(200);
    }
    document.body.classList.add("loading");
}

// Oculta a tela de carregamento
function hideLoading() {
    const loading = document.getElementById("loading");
    if (loading) {
        $(loading).fadeOut(200);
    }
    document.body.classList.remove("loading");
}

// Renderiza o medidor semiderivado tipo Termometro de Meta
function renderGaugeMeta(percent) {
    const ctx = document.getElementById('chartGaugeMeta');
    if (!ctx) return;

    if (chartGaugeInstance) {
        chartGaugeInstance.destroy();
    }

    const cappedPercent = Math.min(Math.max(percent, 0), 100);
    const remaining = 100 - cappedPercent;

    chartGaugeInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [cappedPercent, remaining],
                backgroundColor: ['#00c853', '#e9ecef'],
                borderWidth: 0,
                borderRadius: 4
            }]
        },
        options: {
            rotation: -90,
            circumference: 180,
            cutout: '78%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });
}

// Renderiza o gráfico de tendência do mês selecionado (reta de 0 à meta e evolução do faturamento)
function renderSparklineTrend(labels, dataMeta, dataFaturamento, valorMetaMax, nomeMes) {
    const ctx = document.getElementById('chartSparklineTrend');
    if (!ctx) return;

    if (chartSparklineInstance) {
        chartSparklineInstance.destroy();
    }

    const maxFatVal = Math.max(...dataFaturamento.filter(v => v !== null), 0);
    const maxEscala = Math.max(valorMetaMax || 0, maxFatVal, 100);

    chartSparklineInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Meta',
                    data: dataMeta,
                    borderColor: '#00d27a', // Linha Verde viva = Linha direta da Meta
                    borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#00d27a',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 2,
                    tension: 0, // Reta direta que sobe de 0 à meta
                    fill: false
                },
                {
                    label: 'Faturamento',
                    data: dataFaturamento,
                    borderColor: '#2c3e50', // Linha Azul/Cinza Escura = Faturamento acumulado
                    borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#2c3e50',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 2,
                    tension: 0.15,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 4,
                    right: 8,
                    top: 6,
                    bottom: 2
                }
            },
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 8,
                        boxHeight: 8,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 10 },
                        color: '#7f8c8d',
                        padding: 6
                    }
                },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#ffffff',
                    titleColor: '#1a252f',
                    bodyColor: '#333333',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    padding: 10,
                    boxPadding: 4,
                    usePointStyle: true,
                    titleFont: { weight: 'bold', size: 12 },
                    bodyFont: { size: 11 },
                    callbacks: {
                        title: function(context) {
                            if (!context || !context.length) return '';
                            const label = context[0].label;
                            if (label === '0' || label === 'Início') return 'Início do Mês';
                            return `Dia ${label}${nomeMes ? ' de ' + nomeMes : ''}`;
                        },
                        label: function(context) {
                            let label = context.dataset.label || '';
                            const val = context.parsed.y;
                            if (val === null || val === undefined) return '';
                            const valStr = val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                            return ` ${label}:  ${valStr}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: { display: false, drawBorder: false },
                    ticks: {
                        font: { size: 9 },
                        color: '#95a5a6',
                        maxTicksLimit: 8,
                        callback: function(val, index) {
                            const lbl = this.getLabelForValue(val);
                            return lbl === '0' ? '' : lbl;
                        }
                    }
                },
                y: {
                    display: true,
                    min: 0,
                    max: Math.ceil(maxEscala * 1.05),
                    grid: {
                        color: '#f8fafc',
                        drawBorder: false
                    },
                    ticks: {
                        font: { size: 9 },
                        color: '#95a5a6',
                        maxTicksLimit: 5,
                        callback: function(val) {
                            if (val >= 1000000) return 'R$ ' + (val / 1000000).toFixed(1).replace('.0', '') + 'M';
                            if (val >= 1000) return 'R$ ' + (val / 1000).toFixed(0) + 'k';
                            return 'R$ ' + val;
                        }
                    }
                }
            }
        }
    });
}

// Renderiza o grafico de evolucao mensal (Faturamento Realizado, Faltante para Meta e Teto da Meta)
function renderChartEvolucaoMensal(labels, faturamentos, metas, faltantes) {
    const ctx = document.getElementById('chartEvolucaoMensal');
    if (!ctx) return;

    if (chartEvolucaoMensalInstance) {
        chartEvolucaoMensalInstance.destroy();
    }

    chartEvolucaoMensalInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Faturamento Realizado',
                    data: faturamentos,
                    backgroundColor: '#00c853',
                    stack: 'Stack 0',
                    borderRadius: 4
                },
                {
                    label: 'Faltante para Meta',
                    data: faltantes,
                    backgroundColor: 'rgba(255, 112, 67, 0.45)',
                    borderColor: '#ff7043',
                    borderWidth: 1,
                    stack: 'Stack 0',
                    borderRadius: 4
                },
                {
                    label: 'Teto da Meta',
                    data: metas,
                    type: 'line',
                    borderColor: '#34495e',
                    borderWidth: 3,
                    borderDash: [6, 6],
                    pointBackgroundColor: '#34495e',
                    pointRadius: 4,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        font: { weight: 'bold' }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false }
                },
                y: {
                    stacked: true,
                    ticks: {
                        callback: function(value) {
                            return (value / 1000).toLocaleString('pt-BR') + 'k';
                        }
                    }
                }
            }
        }
    });
}

// Renderiza o grafico de barras para faturamento por departamento (horizontal se todos, vertical se um especifico)
function renderChartFatDepartamento(labels, dataValues) {
    const ctx = document.getElementById('chartFatDepartamento');
    if (!ctx) return;

    if (chartFatDepartamentoInstance) {
        chartFatDepartamentoInstance.destroy();
    }

    const isSingleDepartment = labels.length === 1;

    const backgroundColors = [
        '#516480', '#2ecc71', '#9b59b6', '#f39c12',
        '#e67e22', '#3498db', '#1abc9c', '#e74c3c'
    ];

    chartFatDepartamentoInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Faturamento',
                data: dataValues,
                backgroundColor: backgroundColors.slice(0, Math.max(labels.length, 1)),
                borderRadius: 6,
                borderWidth: 0
            }]
        },
        options: {
            indexAxis: isSingleDepartment ? 'x' : 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const val = isSingleDepartment ? (context.parsed.y || 0) : (context.parsed.x || 0);
                            return ' Faturamento: ' + val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                        }
                    }
                }
            },
            scales: isSingleDepartment ? {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: '600' } }
                },
                y: {
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: function(value) {
                            return (value / 1000).toLocaleString('pt-BR') + 'k';
                        }
                    }
                }
            } : {
                x: {
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: function(value) {
                            return (value / 1000).toLocaleString('pt-BR') + 'k';
                        }
                    }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: '600' } }
                }
            }
        }
    });
}

// Renderiza o gráfico de barras verticais para faturamento por vendedor
function renderChartFatVendedor(labels, dataValues, isVendorSelected = false) {
    const ctx = document.getElementById('chartFatVendedor');
    if (!ctx) return;

    const wrapper = document.getElementById('wrapperFatVendedor');
    if (wrapper) {
        // Define a largura mínima do container para rolagem horizontal caso existam múltiplos vendedores
        if (!isVendorSelected && labels.length > 5) {
            wrapper.style.width = (labels.length * 40) + 'px';
        } else {
            wrapper.style.width = '100%';
        }
    }

    if (chartFatVendedorInstance) {
        chartFatVendedorInstance.destroy();
    }

    chartFatVendedorInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Faturamento',
                data: dataValues,
                backgroundColor: '#2ecc71',
                borderRadius: 6,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            if (!context || !context.length) return '';
                            return context[0].label || '';
                        },
                        label: function(context) {
                            const val = context.parsed.y || 0;
                            return ' Faturamento: ' + val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        display: isVendorSelected,
                        font: { size: 10, weight: '600' },
                        maxRotation: 45,
                        minRotation: 0
                    }
                },
                y: {
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: function(value) {
                            return (value / 1000).toLocaleString('pt-BR') + 'k';
                        }
                    }
                }
            }
        }
    });
}

// Carrega as filiais no filtro
function GetEmpresa() {
    api_get("/empresa/", { situacao: "A" }).then((response) => {
        const filialSelect = document.getElementById("filial");
        if (filialSelect && response && Array.isArray(response.data)) {
            filialSelect._defaultHTML = '<option value="">Todas as filiais</option>';
            filialSelect.innerHTML = filialSelect._defaultHTML;

            const empresasOrdenadas = [...response.data].sort((a, b) => (a.codigo || 0) - (b.codigo || 0));
            empresasOrdenadas.forEach((empresa) => {
                const option = new Option(empresa.nome_fantasia || empresa.razao_social, empresa.codigo, false, false);
                $(filialSelect).append(option);
            });
            $(filialSelect).trigger('change.select2');
        }
    }).catch(err => console.error("Erro ao buscar empresas:", err));
}

// Obtém o departamento do usuário logado caso não tenha vindo na sessão
async function obterDeptoUsuarioLogado(userId) {
    if (!userId) return null;
    try {
        const res = await api_get(`/colaborador/${userId}`);
        if (res) {
            const d = res.departamento_id || res.depart || res.centro_custo || res.setor;
            if (d) return String(d);
        }
    } catch (e) {
        console.warn("Aviso ao buscar detalhes do colaborador:", e);
    }
    try {
        const resList = await api_get("/colaborador/", { limit: -1 });
        if (resList && Array.isArray(resList.data)) {
            const me = resList.data.find(c => String(c.id || c.codigo) === String(userId));
            if (me) {
                const d = me.departamento_id || me.depart || me.centro_custo || me.setor;
                if (d) return String(d);
            }
        }
    } catch (e) {
        console.warn("Aviso ao buscar lista de colaboradores:", e);
    }
    return null;
}

// Carrega os departamentos no filtro respeitando as permissões
function GetDepartamento(meuDeptoTravado = null) {
    return api_get("/geral/departamento/", { ativo: "-1" }).then((response) => {
        const departamentoSelect = document.getElementById("departamento");
        if (!departamentoSelect || !response || !Array.isArray(response.data)) return;

        const user = window.currentUser || {};
        const canSelectAllDepartments = user.canSelectAllDepartments === true;

        $(departamentoSelect).empty();

        const deptosFiltrados = response.data.filter(depto => String(depto.ativo) === '-1');
        const deptosOrdenados = [...deptosFiltrados].sort((a, b) =>
            (a.descricao || '').localeCompare(b.descricao || '', 'pt-BR', { sensitivity: 'base' })
        );

        if (canSelectAllDepartments) {
            // Permissão 017.83 ou Supervisor: pode selecionar todos os departamentos
            departamentoSelect.innerHTML = '<option value="">Todos os departamentos</option>';
            deptosOrdenados.forEach((depto) => {
                const id = depto.id || depto.codigo;
                const option = new Option(depto.descricao, id, false, false);
                $(departamentoSelect).append(option);
            });
            $(departamentoSelect).prop("disabled", false);
            $(departamentoSelect).val("").trigger('change.select2');
        } else {
            // Permissão 017.13 / 001.18 ou restrito: NÃO consegue selecionar outro departamento
            const deptoId = meuDeptoTravado || user.departamentoId || window._meuDeptoTravado;
            let deptoEncontrado = null;
            if (deptoId) {
                deptoEncontrado = deptosOrdenados.find(d => String(d.id || d.codigo) === String(deptoId));
            }

            if (deptoEncontrado) {
                const opt = new Option(deptoEncontrado.descricao, String(deptoEncontrado.id || deptoEncontrado.codigo), true, true);
                $(departamentoSelect).append(opt);
            } else if (deptoId) {
                const opt = new Option(`Departamento ${deptoId}`, String(deptoId), true, true);
                $(departamentoSelect).append(opt);
            } else {
                const opt = new Option("Meu Departamento", "", true, true);
                $(departamentoSelect).append(opt);
            }

            $(departamentoSelect).prop("disabled", true);
            $(departamentoSelect).trigger('change.select2');
        }
    }).catch(err => console.error("Erro ao buscar departamentos:", err));
}

// Carrega os vendedores no filtro filtrando por departamento e ordenados alfabeticamente
function GetVendedor(departamentoId = "") {
    const user = window.currentUser || {};
    const canSelectAllDepartments = user.canSelectAllDepartments === true;
    const canSelectOtherSellers = user.canSelectOtherSellers === true;
    const currentUserId = user.id ? String(user.id) : null;

    // Se o usuário não tem permissão de selecionar todos os departamentos (ex: permissão 017.13 / 001.18), restringe ao seu departamento
    let deptoFiltro = departamentoId;
    if (!canSelectAllDepartments) {
        deptoFiltro = user.departamentoId || window._meuDeptoTravado || departamentoId;
    }

    const params = { limit: -1, ativo: "S", tipo: "V" };
    if (deptoFiltro) {
        params.departamento_id = deptoFiltro;
    }

    return api_get("/colaborador/", params).then((response) => {
        const vendedorSelect = document.getElementById("vendedor");
        if (!vendedorSelect) return;

        // Sem permissão de ver outros vendedores (não tem 017.83, 017.13 nem 001.18)
        if (!canSelectOtherSellers && currentUserId) {
            vendedorSelect.innerHTML = '';
            let vNome = "Meu Faturamento";
            let vCodigo = currentUserId;
            if (response && Array.isArray(response.data)) {
                const me = response.data.find(v => String(v.id || v.codigo) === currentUserId);
                if (me) {
                    vNome = me.apelido || me.nome || me.razao_social || vNome;
                    vCodigo = me.codigo || me.id || currentUserId;
                }
            }
            const labelMeuFaturamento = (vCodigo && vNome) ? `${vCodigo} / ${vNome}` : (vNome || String(vCodigo));
            const option = new Option(labelMeuFaturamento, currentUserId, true, true);
            $(vendedorSelect).append(option);
            $(vendedorSelect).prop("disabled", true);
            $(vendedorSelect).trigger('change.select2');
            return;
        }

        // Permissão 017.83 ou 017.13 / 001.18: usuário pode selecionar outros vendedores
        if (response && Array.isArray(response.data)) {
            const valorAtual = $(vendedorSelect).val();
            vendedorSelect._defaultHTML = '<option value="">Todos os vendedores</option>';
            vendedorSelect.innerHTML = vendedorSelect._defaultHTML;

            let vendedoresFiltrados = response.data.filter(vend => 
                String(vend.tipo || '').toUpperCase() === 'V' && 
                String(vend.ativo || '').toUpperCase() === 'S'
            );

            // Se for 017.13 / 001.18, assegura que só carregue vendedores do departamento vinculado ao usuário logado
            if (!canSelectAllDepartments && deptoFiltro) {
                vendedoresFiltrados = vendedoresFiltrados.filter(vend => {
                    const vendDepto = vend.departamento_id || vend.depart || vend.centro_custo || vend.setor;
                    return String(vendDepto) === String(deptoFiltro);
                });
            }

            const vendedoresOrdenados = [...vendedoresFiltrados].sort((a, b) => {
                const nomeA = a.apelido || a.nome || a.razao_social || '';
                const nomeB = b.apelido || b.nome || b.razao_social || '';
                return nomeA.localeCompare(nomeB, 'pt-BR', { sensitivity: 'base' });
            });

            vendedoresOrdenados.forEach((vend) => {
                const id = vend.id || vend.codigo;
                const codigo = vend.codigo || vend.id || '';
                const nome = vend.apelido || vend.nome || vend.razao_social || '';
                const label = (codigo && nome) ? `${codigo} / ${nome}` : (nome || String(codigo));
                const option = new Option(label, id, false, false);
                $(vendedorSelect).append(option);
            });

            $(vendedorSelect).prop("disabled", false);
            $(vendedorSelect).val(valorAtual || "");
            $(vendedorSelect).trigger('change.select2');
        }
    }).catch(err => console.error("Erro ao buscar vendedores:", err));
}

// Busca os faturamentos e metas e atualiza os cards e graficos do dashboard
function carregarDashboard() {
    showLoading();

    const user = window.currentUser || {};
    const canSelectAllDepartments = user.canSelectAllDepartments === true;
    const canSelectOtherSellers = user.canSelectOtherSellers === true;
    const currentUserId = user.id ? String(user.id) : null;

    const filial = $("#filial").val();
    let departamento = $("#departamento").val();
    let vendedor = $("#vendedor").val();

    // Se o usuário não puder selecionar todos os departamentos (permissão 017.13 / 001.18 ou restrito), fixa no seu departamento
    if (!canSelectAllDepartments) {
        departamento = user.departamentoId || window._meuDeptoTravado || departamento;
    }

    // Se o usuário não tiver permissão para ver outros vendedores, força seu próprio ID
    if (!canSelectOtherSellers && currentUserId) {
        vendedor = currentUserId;
    }

    const mes = $("#mes").val() || String(new Date().getMonth() + 1).padStart(2, '0');
    const ano = $("#ano").val() || String(new Date().getFullYear());

    $("#label-ano-grafico").text(`Ano ${ano}`);

    const mesFormatado = String(mes).padStart(2, '0');
    const dataInicial = `${ano}-${mesFormatado}-01`;
    const ultimoDia = new Date(parseInt(ano, 10), parseInt(mes, 10), 0).getDate();
    const dataFinal = `${ano}-${mesFormatado}-${String(ultimoDia).padStart(2, '0')}`;

    const anoAnterior = parseInt(ano, 10) - 1;
    const dataInicialAnoAnterior = `${anoAnterior}-${mesFormatado}-01`;
    const ultimoDiaAnoAnterior = new Date(anoAnterior, parseInt(mes, 10), 0).getDate();
    const dataFinalAnoAnterior = `${anoAnterior}-${mesFormatado}-${String(ultimoDiaAnoAnterior).padStart(2, '0')}`;

    // Obtem a lista de IDs dos vendedores a considerar no filtro
    let getVendedoresPromise;
    if (!canSelectOtherSellers && currentUserId) {
        getVendedoresPromise = Promise.resolve([currentUserId]);
    } else if (vendedor) {
        getVendedoresPromise = Promise.resolve([String(vendedor)]);
    } else if (departamento) {
        getVendedoresPromise = api_get("/colaborador/", { departamento_id: departamento, ativo: "S", limit: -1 })
            .then(res => (res && Array.isArray(res.data) ? res.data : []).map(v => String(v.id || v.codigo)))
            .catch(() => []);
    } else {
        getVendedoresPromise = Promise.resolve(null);
    }

    return getVendedoresPromise.then((vendedoresIds) => {
        // Parâmetros otimizados utilizando agregação direta no banco de dados
        const paramsYearFaturamento = { data_inicial: `${ano}-01-01`, data_final: `${ano}-12-31`, gerar_bi: 'S', resumo_mensal: 'S', cli_for: 'C' };
        if (filial) paramsYearFaturamento.filial = filial;
        if (vendedoresIds !== null) {
            const vendVal = vendedoresIds.length > 0 ? vendedoresIds.join(',') : '-1';
            paramsYearFaturamento.vend1 = vendVal;
            paramsYearFaturamento.vendedor = vendVal;
        }

        const paramsYearMeta = { data_inicial: `${ano}-01-01`, data_final: `${ano}-12-31` };
        if (filial) paramsYearMeta.filial = filial;
        if (vendedoresIds !== null) {
            paramsYearMeta.vendedor = vendedoresIds.length > 0 ? vendedoresIds.join(',') : '-1';
        }

        const paramsPrevYear = { data_inicial: `${anoAnterior}-01-01`, data_final: `${anoAnterior}-12-31`, gerar_bi: 'S', resumo_mensal: 'S', cli_for: 'C' };
        if (filial) paramsPrevYear.filial = filial;
        if (vendedoresIds !== null) {
            const vendVal = vendedoresIds.length > 0 ? vendedoresIds.join(',') : '-1';
            paramsPrevYear.vend1 = vendVal;
            paramsPrevYear.vendedor = vendVal;
        }

        const paramsMonthVendFat = { data_inicial: dataInicial, data_final: dataFinal, gerar_bi: 'S', resumo_vendedor: 'S', cli_for: 'C' };
        if (filial) paramsMonthVendFat.filial = filial;
        if (vendedoresIds !== null) {
            const vendVal = vendedoresIds.length > 0 ? vendedoresIds.join(',') : '-1';
            paramsMonthVendFat.vend1 = vendVal;
            paramsMonthVendFat.vendedor = vendVal;
        }

        const paramsMonthClientFat = { data_inicial: dataInicial, data_final: dataFinal, gerar_bi: 'S', resumo_cliente: 'S', cli_for: 'C' };
        if (filial) paramsMonthClientFat.filial = filial;
        if (vendedoresIds !== null) {
            const vendVal = vendedoresIds.length > 0 ? vendedoresIds.join(',') : '-1';
            paramsMonthClientFat.vend1 = vendVal;
            paramsMonthClientFat.vendedor = vendVal;
        }

        const paramsMonthDailyFat = { data_inicial: dataInicial, data_final: dataFinal, gerar_bi: 'S', resumo_diario: 'S', cli_for: 'C' };
        if (filial) paramsMonthDailyFat.filial = filial;
        if (vendedoresIds !== null) {
            const vendVal = vendedoresIds.length > 0 ? vendedoresIds.join(',') : '-1';
            paramsMonthDailyFat.vend1 = vendVal;
            paramsMonthDailyFat.vendedor = vendVal;
        }

        const paramsSaldoAtual = { };
        if (filial) paramsSaldoAtual.filial = filial;
        if (vendedoresIds !== null) paramsSaldoAtual.vendedor = vendedoresIds.length > 0 ? vendedoresIds.join(',') : '-1';

        const mesInt = parseInt(mes, 10);
        let anoPrevMes = ano;
        let prevMes = mesInt - 1;
        if (prevMes === 0) {
            prevMes = 12;
            anoPrevMes = anoAnterior;
        }
        const ultimoDiaPrevMes = new Date(anoPrevMes, prevMes, 0).getDate();
        const dataFinalPrevMes = `${anoPrevMes}-${String(prevMes).padStart(2, '0')}-${String(ultimoDiaPrevMes).padStart(2, '0')}`;
        
        const paramsSaldoPrevMes = { data_final: dataFinalPrevMes };
        if (filial) paramsSaldoPrevMes.filial = filial;
        if (vendedoresIds !== null) paramsSaldoPrevMes.vendedor = vendedoresIds.length > 0 ? vendedoresIds.join(',') : '-1';

        const paramsSaldoPrevAno = { data_final: dataFinalAnoAnterior };
        if (filial) paramsSaldoPrevAno.filial = filial;
        if (vendedoresIds !== null) paramsSaldoPrevAno.vendedor = vendedoresIds.length > 0 ? vendedoresIds.join(',') : '-1';

        const paramsCarteira = { limit: 1 };
        if (vendedoresIds !== null) {
            paramsCarteira.vendedores = vendedoresIds.length > 0 ? vendedoresIds.join(',') : '-1';
        }

        const anoInt = parseInt(ano, 10);
        let proxMesNum = mesInt + 1;
        let proxAnoNum = anoInt;
        if (proxMesNum > 12) {
            proxMesNum = 1;
            proxAnoNum += 1;
        }
        const dataProxMes = `${proxAnoNum}-${String(proxMesNum).padStart(2, '0')}-01`;

        const paramsPedidos = { 
            data_inicial: dataInicial, 
            data_final: dataProxMes,
            mes: mesFormatado,
            ano: ano
        };
        if (filial) paramsPedidos.filial = filial;
        if (vendedoresIds !== null) paramsPedidos.vendedor = vendedoresIds.length > 0 ? vendedoresIds.join(',') : '-1';

        // Executa as chamadas otimizadas em paralelo
        return Promise.all([
            api_get('/faturamentos/', paramsYearFaturamento).catch(() => ({ data: [] })),
            api_get('/metas/', paramsYearMeta).catch(() => ({ data: [], total_meta: 0 })),
            api_get('/faturamentos/', paramsPrevYear).catch(() => ({ data: [] })),
            api_get('/geral/departamento/').catch(() => ({ data: [] })),
            api_get('/colaborador/', { limit: -1, ativo: 'S' }).catch(() => ({ data: [] })),
            api_get('/faturamentos/', paramsMonthVendFat).catch(() => ({ data: [] })),
            api_get('/faturamentos/', paramsMonthClientFat).catch(() => ({ data: [] })),
            api_get('/faturamentos/', paramsMonthDailyFat).catch(() => ({ data: [] })),
            api_get('/lancamento_conta_corrente/saldo', paramsSaldoAtual).catch(() => ({ saldo: 0 })),
            api_get('/lancamento_conta_corrente/saldo', paramsSaldoPrevMes).catch(() => ({ saldo: 0 })),
            api_get('/lancamento_conta_corrente/saldo', paramsSaldoPrevAno).catch(() => ({ saldo: 0 })),
            api_get('/cliente/', paramsCarteira).catch(() => ({ total: 0 })),
            api_get('/pedido/dashboard', paramsPedidos).catch(() => ({ total_pedidos: 0, total_geral: 0, valor_total: 0, produtos_vendidos: 0, positivacao: 0 }))
        ]).then(([resYearFat, resYearMeta, resPrevYear, resDeptos, resColabs, resMonthVendFat, resMonthClientFat, resMonthDailyFat, resSaldoAtual, resSaldoPrevMes, resSaldoPrevAno, resCarteira, resPedidos]) => {
            try {
                const getDataArray = (res) => (res && Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : []));

                const faturamentosYear = getDataArray(resYearFat);
                const metasYear = getDataArray(resYearMeta);
                const faturamentosPrevYear = getDataArray(resPrevYear);
                const deptosList = getDataArray(resDeptos);
                const colabsList = getDataArray(resColabs);
                const vendFatList = getDataArray(resMonthVendFat);
                const clientFatList = getDataArray(resMonthClientFat);
                const dailyFatList = getDataArray(resMonthDailyFat);

                // 1. Processamento do Mês Atual obtido da busca sumarizada
                let somaTotalNfActual = 0;
                const mesChaveAtual = `${ano}-${mesFormatado}`;
                const itemMesAtual = faturamentosYear.find(item => item.mes === mesChaveAtual);
                if (itemMesAtual) {
                    somaTotalNfActual = parseFloat(itemMesAtual.total_nf || 0) || 0;
                }

                // Calcula a Meta Total do Mês Selecionado (soma os vendedores filtrados ou todos caso nenhum seja filtrado)
                let valorMeta = 0;
                metasYear.forEach((meta) => {
                    if (meta.data_inicial <= dataFinal && meta.data_final >= dataInicial) {
                        if (meta.vendedores && meta.vendedores.length > 0) {
                            meta.vendedores.forEach(v => {
                                if (vendedoresIds === null || vendedoresIds.includes(String(v.lig_id_vendedor))) {
                                    valorMeta += parseFloat(v.valor_meta || 0);
                                }
                            });
                        }
                    }
                });

                // 2. Processamento do Ano Anterior para o mesmo mês selecionado
                let somaTotalNfPrevYear = 0;
                const mesChavePrevYear = `${anoAnterior}-${mesFormatado}`;
                const itemMesPrevYear = faturamentosPrevYear.find(item => item.mes === mesChavePrevYear);
                if (itemMesPrevYear) {
                    somaTotalNfPrevYear = parseFloat(itemMesPrevYear.total_nf || 0) || 0;
                }

                // Atualiza textos do card principal
                const formatBRL = (val) => val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                $("#card-faturamento-total").text(formatBRL(somaTotalNfActual));
                $("#card-meta-valor").text(formatBRL(valorMeta));

                const percentMeta = valorMeta > 0 ? (somaTotalNfActual / valorMeta) * 100 : 0;
                $("#card-meta-percent").text(`${percentMeta.toFixed(2)}%`);

                let percentVsPrev = 0;
                if (somaTotalNfPrevYear > 0) {
                    percentVsPrev = ((somaTotalNfActual - somaTotalNfPrevYear) / somaTotalNfPrevYear) * 100;
                } else if (somaTotalNfActual > 0) {
                    percentVsPrev = 100;
                }
                
                const textVsPrev = `${percentVsPrev >= 0 ? '+' : ''}${percentVsPrev.toFixed(2)}%`;
                $("#card-meta-vs-ano")
                    .text(textVsPrev)
                    .removeClass("text-success text-danger")
                    .addClass(percentVsPrev >= 0 ? "text-success" : "text-danger");

                // 3. Processamento do Card Clientes Atendidos (Contagem única de clientes)
                let countClientesAtual = itemMesAtual ? (parseInt(itemMesAtual.total_clientes || itemMesAtual.TOTAL_CLIENTES || 0, 10) || 0) : 0;
                if (countClientesAtual === 0 && clientFatList.length > 0) {
                    countClientesAtual = clientFatList.length;
                }
                $("#card-clientes-total").text(countClientesAtual.toLocaleString('pt-BR'));

                // Comparativo de Clientes vs. Mês Anterior
                const mesInt = parseInt(mes, 10);
                let itemMesAnterior = null;
                if (mesInt > 1) {
                    const mesAntNum = String(mesInt - 1).padStart(2, '0');
                    itemMesAnterior = faturamentosYear.find(item => item.mes === `${ano}-${mesAntNum}`);
                } else {
                    itemMesAnterior = faturamentosPrevYear.find(item => item.mes === `${anoAnterior}-12`);
                }
                const countClientesMesAnterior = itemMesAnterior ? (parseInt(itemMesAnterior.total_clientes || itemMesAnterior.TOTAL_CLIENTES || 0, 10) || 0) : 0;

                let percentClientesVsMes = 0;
                if (countClientesMesAnterior > 0) {
                    percentClientesVsMes = ((countClientesAtual - countClientesMesAnterior) / countClientesMesAnterior) * 100;
                } else if (countClientesAtual > 0) {
                    percentClientesVsMes = 100;
                }
                $("#card-clientes-vs-mes")
                    .text(`${percentClientesVsMes >= 0 ? '+' : ''}${percentClientesVsMes.toFixed(2)}%`)
                    .removeClass("text-success text-danger")
                    .addClass(percentClientesVsMes >= 0 ? "text-success" : "text-danger");

                // Comparativo de Clientes vs. Ano Anterior (Mesmo Mês)
                const countClientesAnoAnterior = itemMesPrevYear ? (parseInt(itemMesPrevYear.total_clientes || itemMesPrevYear.TOTAL_CLIENTES || 0, 10) || 0) : 0;
                let percentClientesVsAno = 0;
                if (countClientesAnoAnterior > 0) {
                    percentClientesVsAno = ((countClientesAtual - countClientesAnoAnterior) / countClientesAnoAnterior) * 100;
                } else if (countClientesAtual > 0) {
                    percentClientesVsAno = 100;
                }
                $("#card-clientes-vs-ano")
                    .text(`${percentClientesVsAno >= 0 ? '+' : ''}${percentClientesVsAno.toFixed(2)}%`)
                    .removeClass("text-success text-danger")
                    .addClass(percentClientesVsAno >= 0 ? "text-success" : "text-danger");

                // 3.2. Processamento do Card % Carteira
                const totalClientesCarteira = resCarteira ? (parseInt(resCarteira.total || 0, 10)) : 0;
                
                let percCarteiraAtual = 0;
                if (totalClientesCarteira > 0) {
                    percCarteiraAtual = (countClientesAtual / totalClientesCarteira) * 100;
                }
                $("#card-carteira-total").text(`${percCarteiraAtual.toFixed(2)}%`);

                let percCarteiraMesAnterior = 0;
                if (totalClientesCarteira > 0) {
                    percCarteiraMesAnterior = (countClientesMesAnterior / totalClientesCarteira) * 100;
                }
                
                let diffCarteiraVsMes = percCarteiraAtual - percCarteiraMesAnterior;
                $("#card-carteira-vs-mes")
                    .text(`${diffCarteiraVsMes >= 0 ? '+' : ''}${diffCarteiraVsMes.toFixed(2)}%`)
                    .removeClass("text-success text-danger")
                    .addClass(diffCarteiraVsMes >= 0 ? "text-success" : "text-danger");

                let percCarteiraAnoAnterior = 0;
                if (totalClientesCarteira > 0) {
                    percCarteiraAnoAnterior = (countClientesAnoAnterior / totalClientesCarteira) * 100;
                }
                
                let diffCarteiraVsAno = percCarteiraAtual - percCarteiraAnoAnterior;
                $("#card-carteira-vs-ano")
                    .text(`${diffCarteiraVsAno >= 0 ? '+' : ''}${diffCarteiraVsAno.toFixed(2)}%`)
                    .removeClass("text-success text-danger")
                    .addClass(diffCarteiraVsAno >= 0 ? "text-success" : "text-danger");

                // 3.5. Processamento do Card Saldo em Conta
                const valSaldoAtual = resSaldoAtual ? parseFloat(resSaldoAtual.saldo || 0) : 0;
                const valSaldoPrevMes = resSaldoPrevMes ? parseFloat(resSaldoPrevMes.saldo || 0) : 0;
                const valSaldoPrevAno = resSaldoPrevAno ? parseFloat(resSaldoPrevAno.saldo || 0) : 0;
                
                $("#card-saldo-total").text(formatBRL(valSaldoAtual));

                let diffSaldoVsMes = valSaldoAtual - valSaldoPrevMes;
                $("#card-saldo-vs-mes")
                    .text(`${diffSaldoVsMes >= 0 ? '+' : ''}${formatBRL(diffSaldoVsMes)}`)
                    .removeClass("text-success text-danger")
                    .addClass(diffSaldoVsMes >= 0 ? "text-success" : "text-danger");

                let diffSaldoVsAno = valSaldoAtual - valSaldoPrevAno;
                $("#card-saldo-vs-ano")
                    .text(`${diffSaldoVsAno >= 0 ? '+' : ''}${formatBRL(diffSaldoVsAno)}`)
                    .removeClass("text-success text-danger")
                    .addClass(diffSaldoVsAno >= 0 ? "text-success" : "text-danger");

                renderGaugeMeta(percentMeta);

                // 4. Processamento dos Indicadores Secundários (Dias e Pedidos)
                // --- Cálculo de Dias ---
                function getBusinessDays(start, end) {
                    let count = 0;
                    let curDate = new Date(start.getTime());
                    while (curDate <= end) {
                        const dayOfWeek = curDate.getDay();
                        if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Ignora sábado (6) e domingo (0)
                            count++;
                        }
                        curDate.setDate(curDate.getDate() + 1);
                    }
                    return count;
                }

                const dataIniMesObj = new Date(ano, parseInt(mes, 10) - 1, 1);
                const dataFimMesObj = new Date(ano, parseInt(mes, 10), 0);
                
                const diasDoMes = dataFimMesObj.getDate();
                const diasUteisTotais = getBusinessDays(dataIniMesObj, dataFimMesObj);
                
                let hoje = new Date();
                // Se o mês/ano filtrado for o mês/ano atual, o dia limite é hoje. Caso contrário, é o final do mês selecionado.
                let dataLimite = (hoje.getMonth() + 1 == parseInt(mes, 10) && hoje.getFullYear() == parseInt(ano, 10)) ? hoje : dataFimMesObj;
                let diasTrabalhados = getBusinessDays(dataIniMesObj, dataLimite);
                
                let diasRestantes = diasUteisTotais - diasTrabalhados;
                if (diasRestantes < 0) diasRestantes = 0;

                $("#card-dias-mes").text(diasDoMes);
                $("#card-dias-trabalhados").text(diasTrabalhados);
                $("#card-dias-uteis").text(diasUteisTotais);
                $("#card-dias-restantes").text(diasRestantes);

                // --- Cálculo de Médias e Estimativas ---
                let mediaPorDia = 0;
                if (diasTrabalhados > 0) {
                    mediaPorDia = somaTotalNfActual / diasTrabalhados;
                }
                
                let valorEstimado = mediaPorDia * diasUteisTotais;

                $("#card-media-dia").text(formatBRL(mediaPorDia));
                $("#card-valor-estimado").text(formatBRL(valorEstimado));

                // --- Cálculo de Pedidos ---
                const qtdPedidos = resPedidos ? parseInt(resPedidos.total_pedidos || 0, 10) : 0;
                const totalPedidosVal = resPedidos ? parseFloat(resPedidos.total_geral ?? resPedidos.valor_total ?? 0) : 0;
                
                let ticketMedio = 0;
                if (qtdPedidos > 0) {
                    ticketMedio = totalPedidosVal / qtdPedidos;
                }

                $("#card-pedidos").text(qtdPedidos.toLocaleString('pt-BR'));
                $("#card-total-pedidos").text(formatBRL(totalPedidosVal));
                $("#card-ticket-medio").text(formatBRL(ticketMedio));
                
                const produtosVendidos = resPedidos ? parseInt(resPedidos.produtos_vendidos || 0, 10) : 0;
                const positivacao = resPedidos ? parseInt(resPedidos.positivacao || 0, 10) : 0;

                $("#card-produtos-vendidos").text(produtosVendidos.toLocaleString('pt-BR'));
                $("#card-positivacao").text(positivacao.toLocaleString('pt-BR'));

                // 5. Processamento dos meses do Ano para o Gráfico Mês a Mês
                const allNomesMeses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                let monthlyFat = Array(12).fill(0);
                let monthlyMeta = Array(12).fill(0);
                let monthlyFaltante = Array(12).fill(0);

                // Agrupa faturamento por mês diretamente dos dados sumarizados pela API
                faturamentosYear.forEach((item) => {
                    if (item.mes) {
                        const m = parseInt(item.mes.substring(5, 7), 10) - 1;
                        if (m >= 0 && m < 12) {
                            monthlyFat[m] = parseFloat(item.total_nf || 0) || 0;
                        }
                    }
                });

                // Agrupa metas por mês somando todas as metas dos vendedores filtrados
                metasYear.forEach((meta) => {
                    if (meta.vendedores && meta.vendedores.length > 0) {
                        const monthStr = String(meta.data_inicial).substring(5, 7);
                        const m = parseInt(monthStr, 10) - 1;
                        if (m >= 0 && m < 12) {
                            meta.vendedores.forEach(v => {
                                if (vendedoresIds === null || vendedoresIds.includes(String(v.lig_id_vendedor))) {
                                    monthlyMeta[m] += parseFloat(v.valor_meta || 0);
                                }
                            });
                        }
                    }
                });

                // Calcula a barra do faltante para o teto da meta
                for (let i = 0; i < 12; i++) {
                    if (monthlyMeta[i] > monthlyFat[i]) {
                        monthlyFaltante[i] = monthlyMeta[i] - monthlyFat[i];
                    } else {
                        monthlyFaltante[i] = 0;
                    }
                }

                // Limita os meses exibidos até o mês atual caso o ano selecionado seja o ano vigente
                const now = new Date();
                const currentYear = now.getFullYear();
                const currentMonthIndex = now.getMonth();

                let maxMonthIndex = 11;
                if (parseInt(ano, 10) === currentYear) {
                    maxMonthIndex = currentMonthIndex;
                } else if (parseInt(ano, 10) > currentYear) {
                    maxMonthIndex = 0;
                }

                const labelsExibidos = allNomesMeses.slice(0, maxMonthIndex + 1);
                const fatExibidos = monthlyFat.slice(0, maxMonthIndex + 1);
                const metaExibidos = monthlyMeta.slice(0, maxMonthIndex + 1);
                const faltantesExibidos = monthlyFaltante.slice(0, maxMonthIndex + 1);

                // Prepara a evolução do mês selecionado para o gráfico no card Faturamento
                const totalDiasMes = new Date(parseInt(ano, 10), parseInt(mes, 10), 0).getDate();
                const dailyFatMap = {};
                if (Array.isArray(dailyFatList)) {
                    dailyFatList.forEach(item => {
                        if (item.dia) {
                            const diaNum = parseInt(item.dia.substring(8, 10), 10);
                            dailyFatMap[diaNum] = parseFloat(item.total_nf || 0) || 0;
                        }
                    });
                }

                const isMesAtual = (now.getFullYear() === parseInt(ano, 10) && (now.getMonth() + 1) === parseInt(mes, 10));
                const diaLimiteHoje = now.getDate();

                const labelsDias = ['0'];
                const dataMetaDias = [0];
                const dataFatDias = [0];

                let acumuladoFat = 0;
                for (let d = 1; d <= totalDiasMes; d++) {
                    labelsDias.push(String(d).padStart(2, '0'));

                    // Meta: linha direta que sobe de 0 à meta selecionada
                    const metaProjetada = valorMeta > 0 ? (d / totalDiasMes) * valorMeta : 0;
                    dataMetaDias.push(metaProjetada);

                    // Faturamento: acumulado conforme foi crescendo
                    if (isMesAtual && d > diaLimiteHoje) {
                        dataFatDias.push(null);
                    } else {
                        acumuladoFat += (dailyFatMap[d] || 0);
                        dataFatDias.push(acumuladoFat);
                    }
                }

                const nomeMesSelecionado = allNomesMeses[parseInt(mes, 10) - 1] || mes;
                renderSparklineTrend(labelsDias, dataMetaDias, dataFatDias, valorMeta, nomeMesSelecionado);
                renderChartEvolucaoMensal(labelsExibidos, fatExibidos, metaExibidos, faltantesExibidos);

                // 5. Processamento de Faturamento por Departamento e por Vendedor
                const colabDeptMap = {};
                const colabNomeMap = {};
                if (Array.isArray(colabsList)) {
                    colabsList.forEach(c => {
                        const id = String(c.id || c.codigo || '');
                        if (id) {
                            const dept = c.departamento_id || c.depart || c.centro_custo || c.setor;
                            colabDeptMap[id] = (dept && String(dept) !== '0') ? String(dept) : null;
                            colabNomeMap[id] = c.nome || c.apelido || c.razao_social || `Vendedor ${id}`;
                        }
                    });
                }

                const deptNomeMap = {};
                if (Array.isArray(deptosList)) {
                    deptosList.forEach(d => {
                        const id = String(d.id || d.codigo || '');
                        if (id) {
                            deptNomeMap[id] = d.descricao;
                        }
                    });
                }

                const fatByDept = {};
                const fatByVend = {};

                if (Array.isArray(vendFatList)) {
                    vendFatList.forEach(item => {
                        const vendId = String(item.vendedor_id || item.VEND1 || item.vendedor || item.VENDEDOR || '');
                        const total = parseFloat(item.total_nf || item.TOTAL_NF || 0) || 0;
                        if (vendId && vendId !== '0' && total > 0) {
                            fatByVend[vendId] = (fatByVend[vendId] || 0) + total;
                            const deptId = colabDeptMap[vendId];
                            if (deptId) {
                                fatByDept[deptId] = (fatByDept[deptId] || 0) + total;
                            }
                        }
                    });
                }

                let labelsDept = [];
                let valuesDept = [];

                let deptoFiltro = departamento;
                if (!canSelectAllDepartments) {
                    deptoFiltro = user.departamentoId || window._meuDeptoTravado || departamento;
                }

                if (deptoFiltro && deptNomeMap[String(deptoFiltro)]) {
                    const deptIdStr = String(deptoFiltro);
                    labelsDept.push(deptNomeMap[deptIdStr]);
                    valuesDept.push(fatByDept[deptIdStr] || 0);
                } else {
                    deptosList.forEach(d => {
                        const deptIdStr = String(d.id || d.codigo || '');
                        labelsDept.push(d.descricao);
                        valuesDept.push(fatByDept[deptIdStr] || 0);
                    });
                }
                renderChartFatDepartamento(labelsDept, valuesDept);

                let labelsVend = [];
                let valuesVend = [];
                Object.keys(fatByVend).forEach(vId => {
                    labelsVend.push(colabNomeMap[vId] || `Vend ${vId}`);
                    valuesVend.push(fatByVend[vId]);
                });
                renderChartFatVendedor(labelsVend, valuesVend, !!vendedor);

                // Renderiza a Tabela de Curva ABC por Cliente
                renderTabelaCurvaABC(clientFatList);

                // Renderiza a Tabela de Desempenho por Vendedor (abaixo da Curva ABC)
                renderTabelaDesempenhoVendedores(vendedor, colabsList, vendFatList, metasYear, resSaldoAtual, resPedidos, diasTrabalhados, diasUteisTotais, dataInicial, dataFinal, deptoFiltro);
            } catch (renderErr) {
                console.error("Erro durante a renderização do dashboard:", renderErr);
            }
        });
    }).catch((err) => {
        console.error("Erro ao carregar dashboard de vendedores:", err);
    }).finally(() => {
        hideLoading();
    });
}

// Guarda os dados processados da curva ABC e estado da paginação
let abcClientesProcessados = [];
let abcCurrentPage = 1;
let abcPageSize = 15;

// Renderiza a página atual da tabela da Curva ABC
function renderPaginaCurvaABC() {
    const tbody = $("#table-curva-abc tbody");
    if (!tbody.length) return;

    const total = abcClientesProcessados.length;
    abcPageSize = parseInt($("#select-abc-limit").val() || abcPageSize, 10);
    const totalPages = Math.ceil(total / abcPageSize) || 1;

    if (abcCurrentPage > totalPages) abcCurrentPage = totalPages;
    if (abcCurrentPage < 1) abcCurrentPage = 1;

    const start = (abcCurrentPage - 1) * abcPageSize;
    const end = Math.min(start + abcPageSize, total);
    const pageItems = abcClientesProcessados.slice(start, end);

    if (total === 0) {
        tbody.html('<tr><td colspan="6" class="text-center text-muted py-4">Nenhum registro encontrado para o período.</td></tr>');
        $("#info-paginacao-abc").text("Mostrando 0 a 0 de 0 registros");
        $("#paginacao-abc").html('');
        return;
    }

    let htmlRows = '';
    pageItems.forEach((item) => {
        htmlRows += `
            <tr>
                <td class="text-center font-weight-bold text-muted">${item.rank}º</td>
                <td><span class="badge badge-light border">${item.codigo}</span></td>
                <td class="font-weight-bold text-dark">${item.nome}</td>
                <td class="text-right font-weight-bold text-dark">${item.fatFormatado}</td>
                <td class="text-right text-muted">${item.pctFormatado}</td>
                <td class="text-center">
                    <span class="badge ${item.badgeClass} px-3 py-1 font-weight-bold" style="font-size: 0.85rem;">${item.classeABC}</span>
                </td>
            </tr>
        `;
    });

    tbody.html(htmlRows);

    // Atualiza texto informativo de paginação
    $("#info-paginacao-abc").text(`Mostrando ${start + 1} a ${end} de ${total} registros`);

    // Monta botões da paginação
    let paginationHtml = '';

    paginationHtml += `
        <li class="page-item ${abcCurrentPage === 1 ? 'disabled' : ''}">
            <a class="page-link btn-paginacao-abc" href="javascript:void(0);" data-page="${abcCurrentPage - 1}">Anterior</a>
        </li>
    `;

    const maxVisiblePages = 5;
    let startPage = Math.max(1, abcCurrentPage - 2);
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    if (startPage > 1) {
        paginationHtml += `<li class="page-item"><a class="page-link btn-paginacao-abc" href="javascript:void(0);" data-page="1">1</a></li>`;
        if (startPage > 2) {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    for (let p = startPage; p <= endPage; p++) {
        paginationHtml += `
            <li class="page-item ${p === abcCurrentPage ? 'active' : ''}">
                <a class="page-link btn-paginacao-abc" href="javascript:void(0);" data-page="${p}">${p}</a>
            </li>
        `;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        paginationHtml += `<li class="page-item"><a class="page-link btn-paginacao-abc" href="javascript:void(0);" data-page="${totalPages}">${totalPages}</a></li>`;
    }

    paginationHtml += `
        <li class="page-item ${abcCurrentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link btn-paginacao-abc" href="javascript:void(0);" data-page="${abcCurrentPage + 1}">Próximo</a>
        </li>
    `;

    $("#paginacao-abc").html(paginationHtml);
}

// Prepara e calcula os dados da Curva ABC de Faturamento por Cliente
function renderTabelaCurvaABC(clientesData) {
    if (!clientesData || clientesData.length === 0) {
        abcClientesProcessados = [];
        abcCurrentPage = 1;
        $("#total-abc-faturamento").text("R$ 0,00");
        $("#total-abc-porcentagem").text("0,00%");
        renderPaginaCurvaABC();
        return;
    }

    // Ordena do maior faturamento para o menor
    const ordenados = [...clientesData].sort((a, b) => (parseFloat(b.total_nf || b.TOTAL_NF || 0) - parseFloat(a.total_nf || a.TOTAL_NF || 0)));

    // Calcula o faturamento total acumulado para definir as faixas ABC
    const totalFaturamento = ordenados.reduce((acc, curr) => acc + (parseFloat(curr.total_nf || curr.TOTAL_NF || 0) || 0), 0);
    const fatTotalFormatado = totalFaturamento.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    
    $("#total-abc-faturamento").text(fatTotalFormatado);
    $("#total-abc-porcentagem").text(totalFaturamento > 0 ? "100,00%" : "0,00%");

    let acumulado = 0;
    abcClientesProcessados = ordenados.map((item, index) => {
        const rank = index + 1;
        const totalNf = parseFloat(item.total_nf || item.TOTAL_NF || 0) || 0;
        const percentual = totalFaturamento > 0 ? (totalNf / totalFaturamento) * 100 : 0;
        
        acumulado += percentual;

        // Classificação Curva ABC (A: até 80%, B: de 80.01% a 95%, C: acima de 95%)
        let classeABC = 'C';
        let badgeClass = 'badge-secondary';

        if (acumulado <= 80.001 || (index === 0 && percentual > 0)) {
            classeABC = 'A';
            badgeClass = 'badge-success';
        } else if (acumulado <= 95.001) {
            classeABC = 'B';
            badgeClass = 'badge-warning';
        } else {
            classeABC = 'C';
            badgeClass = 'badge-secondary';
        }

        const codigo = item.codigo_cliente || item.CODIGO_CLI_FOR || item.codigo || '-';
        const nome = item.nome_cliente || item.NOME_CLIENTE || item.NOME || item.nome || `Cliente ${codigo}`;
        const fatFormatado = totalNf.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        const pctFormatado = percentual.toFixed(2) + '%';

        return {
            rank,
            codigo,
            nome,
            totalNf,
            fatFormatado,
            pctFormatado,
            classeABC,
            badgeClass
        };
    });

    abcCurrentPage = 1;
    renderPaginaCurvaABC();
}

// Guarda os dados processados da tabela de vendedores e estado da paginação
let vendedoresProcessados = [];
let vendedoresCurrentPage = 1;
let vendedoresPageSize = 15;
let vendedoresDiasTrabalhados = 0;
let vendedoresDiasUteisTotais = 0;

// Renderiza a página atual da tabela de desempenho por vendedor
function renderPaginaVendedores() {
    const tbody = $("#table-desempenho-vendedores tbody");
    const tfoot = $("#table-desempenho-vendedores tfoot");
    if (!tbody.length) return;

    const total = vendedoresProcessados.length;
    vendedoresPageSize = parseInt($("#select-vendedores-limit").val() || vendedoresPageSize, 10);
    const totalPages = Math.ceil(total / vendedoresPageSize) || 1;

    if (vendedoresCurrentPage > totalPages) vendedoresCurrentPage = totalPages;
    if (vendedoresCurrentPage < 1) vendedoresCurrentPage = 1;

    const start = (vendedoresCurrentPage - 1) * vendedoresPageSize;
    const end = Math.min(start + vendedoresPageSize, total);
    const pageItems = vendedoresProcessados.slice(start, end);

    if (total === 0) {
        tbody.html('<tr><td colspan="12" class="text-center text-muted py-4">Nenhum vendedor encontrado com os filtros selecionados.</td></tr>');
        tfoot.html('');
        $("#info-paginacao-vendedores").text("Mostrando 0 a 0 de 0 registros");
        $("#paginacao-vendedores").html('');
        return;
    }

    const formatBRL = (val) => val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    let rowsHtml = '';
    // Total Geral apurado EXCLUSIVAMENTE sobre os registros da página atual exibida
    let pageFaturamento = 0;
    let pageMeta = 0;
    let pageSaldo = 0;
    let pageVendas = 0;
    let pageClientes = 0;
    let pageProdVendidos = 0;
    let pagePositivacao = 0;

    pageItems.forEach(v => {
        pageFaturamento += v.faturamento;
        pageMeta += v.meta;
        pageSaldo += v.saldo;
        pageVendas += v.vendas;
        pageClientes += v.clientes;
        pageProdVendidos += v.produtos_vendidos;
        pagePositivacao += v.positivacao;

        const isNegativo = v.pctRealVsMeta < 0;
        const pctClass = isNegativo ? 'text-danger' : 'text-success';
        const iconSeta = isNegativo ? '<i class="fas fa-arrow-down mr-1"></i>' : (v.pctRealVsMeta > 0 ? '<i class="fas fa-arrow-up mr-1"></i>' : '');
        const sinalPlus = (!isNegativo && v.pctRealVsMeta > 0) ? '+' : '';

        rowsHtml += `
            <tr>
                <td><span class="badge-nome-vendedor">${v.nome}</span></td>
                <td class="text-right"><span class="badge-fat-vendedor">${formatBRL(v.faturamento)}</span></td>
                <td class="text-right"><span class="badge-meta-vendedor">${formatBRL(v.meta)}</span></td>
                <td class="text-center font-weight-bold ${pctClass}" style="font-size: 0.95rem;">
                    ${iconSeta}${sinalPlus}${v.pctRealVsMeta.toFixed(2)}%
                </td>
                <td class="text-right font-weight-bold text-dark">${formatBRL(v.saldo)}</td>
                <td class="text-center font-weight-bold text-dark">${v.vendas.toLocaleString('pt-BR')}</td>
                <td class="text-right font-weight-bold text-dark">${formatBRL(v.ticketMedio)}</td>
                <td class="text-center font-weight-bold text-dark">${v.clientes.toLocaleString('pt-BR')}</td>
                <td class="text-center font-weight-bold text-dark">${v.produtos_vendidos.toLocaleString('pt-BR')}</td>
                <td class="text-center font-weight-bold text-dark">${v.positivacao.toLocaleString('pt-BR')}</td>
                <td class="text-right font-weight-bold text-dark">${formatBRL(v.mediaPorDia)}</td>
                <td class="text-right font-weight-bold text-dark">${formatBRL(v.valorEstimado)}</td>
            </tr>
        `;
    });

    tbody.html(rowsHtml);

    // Cálculos consolidados dos registros da página
    let pagePctRealVsMeta = 0;
    if (pageMeta > 0) {
        pagePctRealVsMeta = ((pageFaturamento - pageMeta) / pageMeta) * 100;
    } else if (pageFaturamento > 0) {
        pagePctRealVsMeta = 100;
    }

    const pageTicketMedio = pageVendas > 0 ? (pageFaturamento / pageVendas) : 0;
    const pageMediaPorDia = vendedoresDiasTrabalhados > 0 ? (pageFaturamento / vendedoresDiasTrabalhados) : 0;
    const pageValorEstimado = pageMediaPorDia * vendedoresDiasUteisTotais;

    const pageIsNegativo = pagePctRealVsMeta < 0;
    const pagePctClass = pageIsNegativo ? 'text-danger' : 'text-success';
    const pageSinalPlus = (!pageIsNegativo && pagePctRealVsMeta > 0) ? '+' : '';

    const footHtml = `
        <tr class="font-weight-bold" style="border-top: 2px solid #dee2e6; background-color: #fafbfc;">
            <td class="text-dark font-weight-bold" style="font-size: 0.95rem;">Total</td>
            <td class="text-right font-weight-bold text-dark" style="font-size: 0.95rem;">${formatBRL(pageFaturamento)}</td>
            <td class="text-right font-weight-bold text-dark" style="font-size: 0.95rem;">${formatBRL(pageMeta)}</td>
            <td class="text-center font-weight-bold ${pagePctClass}" style="font-size: 0.95rem;">${pageSinalPlus}${pagePctRealVsMeta.toFixed(2)}%</td>
            <td class="text-right font-weight-bold text-dark" style="font-size: 0.95rem;">${formatBRL(pageSaldo)}</td>
            <td class="text-center font-weight-bold text-dark" style="font-size: 0.95rem;">${pageVendas.toLocaleString('pt-BR')}</td>
            <td class="text-right font-weight-bold text-dark" style="font-size: 0.95rem;">${formatBRL(pageTicketMedio)}</td>
            <td class="text-center font-weight-bold text-dark" style="font-size: 0.95rem;">${pageClientes.toLocaleString('pt-BR')}</td>
            <td class="text-center font-weight-bold text-dark" style="font-size: 0.95rem;">${pageProdVendidos.toLocaleString('pt-BR')}</td>
            <td class="text-center font-weight-bold text-dark" style="font-size: 0.95rem;">${pagePositivacao.toLocaleString('pt-BR')}</td>
            <td class="text-right font-weight-bold text-dark" style="font-size: 0.95rem;">${formatBRL(pageMediaPorDia)}</td>
            <td class="text-right font-weight-bold text-dark" style="font-size: 0.95rem;">${formatBRL(pageValorEstimado)}</td>
        </tr>
    `;

    tfoot.html(footHtml);

    // Atualiza texto informativo de paginação
    $("#info-paginacao-vendedores").text(`Mostrando ${start + 1} a ${end} de ${total} registros`);

    // Monta botões da paginação
    let paginationHtml = '';

    paginationHtml += `
        <li class="page-item ${vendedoresCurrentPage === 1 ? 'disabled' : ''}">
            <a class="page-link btn-paginacao-vendedores" href="javascript:void(0);" data-page="${vendedoresCurrentPage - 1}">Anterior</a>
        </li>
    `;

    const maxVisiblePages = 5;
    let startPage = Math.max(1, vendedoresCurrentPage - 2);
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    if (startPage > 1) {
        paginationHtml += `<li class="page-item"><a class="page-link btn-paginacao-vendedores" href="javascript:void(0);" data-page="1">1</a></li>`;
        if (startPage > 2) {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    for (let p = startPage; p <= endPage; p++) {
        paginationHtml += `
            <li class="page-item ${p === vendedoresCurrentPage ? 'active' : ''}">
                <a class="page-link btn-paginacao-vendedores" href="javascript:void(0);" data-page="${p}">${p}</a>
            </li>
        `;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        paginationHtml += `<li class="page-item"><a class="page-link btn-paginacao-vendedores" href="javascript:void(0);" data-page="${totalPages}">${totalPages}</a></li>`;
    }

    paginationHtml += `
        <li class="page-item ${vendedoresCurrentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link btn-paginacao-vendedores" href="javascript:void(0);" data-page="${vendedoresCurrentPage + 1}">Próximo</a>
        </li>
    `;

    $("#paginacao-vendedores").html(paginationHtml);
}

// Prepara e carrega os dados da Tabela de Desempenho por Vendedor
function renderTabelaDesempenhoVendedores(vendedorSelecionado, colabsList, vendFatList, metasYear, resSaldoAtual, resPedidos, diasTrabalhados, diasUteisTotais, dataInicial, dataFinal, deptoFiltro) {
    const $row = $("#row-tabela-vendedores");
    const tbody = $("#table-desempenho-vendedores tbody");
    const tfoot = $("#table-desempenho-vendedores tfoot");
    if (!$row.length || !tbody.length) return;

    // Se houver algum vendedor selecionado individualmente, o card deve ficar oculto
    if (vendedorSelecionado) {
        $row.hide();
        vendedoresProcessados = [];
        vendedoresCurrentPage = 1;
        tbody.html('');
        tfoot.html('');
        return;
    }

    $row.show();

    // 1. Mapeamento de colaboradores para indexação rápida por ID e Código
    const colabMap = {};
    const colabsArray = Array.isArray(colabsList) ? colabsList : [];
    colabsArray.forEach(c => {
        if (c.id != null) {
            colabMap[String(c.id)] = c;
        }
        if (c.codigo != null) {
            colabMap[String(c.codigo)] = c;
        }
    });

    const getColab = (vId) => colabMap[String(vId)];
    const getCanonicalKey = (vId) => {
        const c = getColab(vId);
        return c ? String(c.id || c.codigo || vId) : String(vId);
    };
    const getNome = (vId) => {
        const c = getColab(vId);
        return c ? (c.apelido || c.nome || c.razao_social || `Vendedor ${vId}`) : `Vendedor ${vId}`;
    };

    // 2. Mapeamento de faturamento por vendedor
    const fatMap = {};
    if (Array.isArray(vendFatList)) {
        vendFatList.forEach(item => {
            const rawId = item.vendedor_id || item.VEND1 || item.vendedor || item.VENDEDOR;
            if (rawId && String(rawId) !== '0') {
                const key = getCanonicalKey(rawId);
                const total = parseFloat(item.total_nf || item.TOTAL_NF || 0) || 0;
                fatMap[key] = (fatMap[key] || 0) + total;
            }
        });
    }

    // 3. Mapeamento de metas por vendedor
    const metaMap = {};
    if (Array.isArray(metasYear)) {
        metasYear.forEach(meta => {
            if (meta.data_inicial <= dataFinal && meta.data_final >= dataInicial) {
                if (meta.vendedores && meta.vendedores.length > 0) {
                    meta.vendedores.forEach(v => {
                        const rawId = v.lig_id_vendedor || v.vendedor_id;
                        if (rawId && String(rawId) !== '0') {
                            const key = getCanonicalKey(rawId);
                            metaMap[key] = (metaMap[key] || 0) + (parseFloat(v.valor_meta || 0) || 0);
                        }
                    });
                }
            }
        });
    }

    // 4. Mapeamento de saldos de conta corrente por vendedor
    const saldoMap = {};
    const saldosList = (resSaldoAtual && Array.isArray(resSaldoAtual.vendedores)) ? resSaldoAtual.vendedores : [];
    saldosList.forEach(s => {
        const rawId = s.usuario_vendedor_id;
        if (rawId && String(rawId) !== '0') {
            const key = getCanonicalKey(rawId);
            saldoMap[key] = (saldoMap[key] || 0) + (parseFloat(s.saldo || 0) || 0);
        }
    });

    // 5. Mapeamento de pedidos, clientes, produtos vendidos e positivação por vendedor
    const pedMap = {};
    const pedidosList = (resPedidos && Array.isArray(resPedidos.vendedores)) ? resPedidos.vendedores : [];
    pedidosList.forEach(p => {
        const rawId = p.vendedor_id;
        if (rawId && String(rawId) !== '0') {
            const key = getCanonicalKey(rawId);
            if (!pedMap[key]) {
                pedMap[key] = { vendas: 0, total_geral: 0, clientes: 0, produtos_vendidos: 0, positivacao: 0 };
            }
            pedMap[key].vendas += parseInt(p.vendas || 0, 10);
            pedMap[key].total_geral += parseFloat(p.total_geral || 0);
            pedMap[key].clientes += parseInt(p.clientes || 0, 10);
            pedMap[key].produtos_vendidos += parseInt(p.produtos_vendidos || 0, 10);
            pedMap[key].positivacao += parseInt(p.positivacao || 0, 10);
        }
    });

    // 6. Determinar os IDs dos vendedores a serem listados na tabela
    const vendedoresKeys = new Set();

    // Vendedores do filtro de colaboradores (tipo 'V' e ativo 'S')
    colabsArray.forEach(c => {
        const tipo = String(c.tipo || '').toUpperCase();
        const ativo = String(c.ativo || '').toUpperCase();
        if (tipo === 'V' && ativo === 'S') {
            const depto = c.departamento_id || c.depart || c.centro_custo || c.setor;
            if (!deptoFiltro || String(depto) === String(deptoFiltro)) {
                vendedoresKeys.add(getCanonicalKey(c.id || c.codigo));
            }
        }
    });

    // Adiciona também vendedores que tiveram movimentação no período
    const addIfMatchesDepto = (key) => {
        if (!key || key === '0') return;
        if (deptoFiltro) {
            const c = getColab(key);
            const depto = c ? (c.departamento_id || c.depart || c.centro_custo || c.setor) : null;
            if (String(depto) !== String(deptoFiltro)) return;
        }
        vendedoresKeys.add(key);
    };

    Object.keys(fatMap).forEach(addIfMatchesDepto);
    Object.keys(metaMap).forEach(addIfMatchesDepto);
    Object.keys(pedMap).forEach(addIfMatchesDepto);

    const listaVendedores = [];
    vendedoresKeys.forEach(key => {
        const nome = getNome(key);
        const faturamento = fatMap[key] || 0;
        const meta = metaMap[key] || 0;
        const saldo = saldoMap[key] || 0;
        const pedData = pedMap[key] || { vendas: 0, total_geral: 0, clientes: 0, produtos_vendidos: 0, positivacao: 0 };
        const vendas = pedData.vendas;
        const clientes = pedData.clientes;
        const produtos_vendidos = pedData.produtos_vendidos;
        const positivacao = pedData.positivacao;

        let pctRealVsMeta = 0;
        if (meta > 0) {
            pctRealVsMeta = ((faturamento - meta) / meta) * 100;
        } else if (faturamento > 0) {
            pctRealVsMeta = 100;
        }

        const ticketMedio = vendas > 0 ? (faturamento / vendas) : 0;
        const mediaPorDia = diasTrabalhados > 0 ? (faturamento / diasTrabalhados) : 0;
        const valorEstimado = mediaPorDia * diasUteisTotais;

        listaVendedores.push({
            id: key,
            nome,
            faturamento,
            meta,
            pctRealVsMeta,
            saldo,
            vendas,
            ticketMedio,
            clientes,
            produtos_vendidos,
            positivacao,
            mediaPorDia,
            valorEstimado
        });
    });

    // Ordenação decrescente por faturamento
    listaVendedores.sort((a, b) => b.faturamento - a.faturamento || a.nome.localeCompare(b.nome));

    vendedoresProcessados = listaVendedores;
    vendedoresDiasTrabalhados = diasTrabalhados;
    vendedoresDiasUteisTotais = diasUteisTotais;
    vendedoresCurrentPage = 1;

    renderPaginaVendedores();
}

async function initDashboard() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    const user = window.currentUser || {};

    // Se não puder ver todos os departamentos e não tem departamentoId definido na sessão, busca via API
    if (!user.canSelectAllDepartments && !user.departamentoId) {
        user.departamentoId = await obterDeptoUsuarioLogado(user.id);
    }
    if (user.departamentoId) {
        window._meuDeptoTravado = user.departamentoId;
    }

    GetEmpresa();
    await GetDepartamento(window._meuDeptoTravado);
    await GetVendedor(window._meuDeptoTravado || $("#departamento").val() || "");

    // Listener para o filtro de departamento para atualizar a lista de vendedores
    $("#departamento").on("change", function () {
        if (window.currentUser && window.currentUser.canSelectAllDepartments) {
            const departamentoId = $(this).val();
            GetVendedor(departamentoId);
        }
    });

    // Botão Filtrar
    $("#btn-filtrar").on("click", function () {
        carregarDashboard();
    });

    // Permite filtrar ao pressionar Enter nos filtros
    $("#filial, #departamento, #vendedor, #mes, #ano").on("keydown", function (e) {
        if (e.key === "Enter" || e.keyCode === 13) {
            e.preventDefault();
            carregarDashboard();
        }
    });

    // Listeners para alteração da quantidade por página e cliques da paginação da Curva ABC
    $("#select-abc-limit").on("change", function () {
        abcPageSize = parseInt($(this).val(), 10) || 15;
        abcCurrentPage = 1;
        renderPaginaCurvaABC();
    });

    $(document).on("click", ".btn-paginacao-abc", function (e) {
        e.preventDefault();
        const page = parseInt($(this).attr("data-page"), 10);
        if (!isNaN(page) && page !== abcCurrentPage) {
            abcCurrentPage = page;
            renderPaginaCurvaABC();
        }
    });

    // Listeners para alteração da quantidade por página e cliques da paginação da Tabela de Vendedores
    $("#select-vendedores-limit").on("change", function () {
        vendedoresPageSize = parseInt($(this).val(), 10) || 15;
        vendedoresCurrentPage = 1;
        renderPaginaVendedores();
    });

    $(document).on("click", ".btn-paginacao-vendedores", function (e) {
        e.preventDefault();
        const page = parseInt($(this).attr("data-page"), 10);
        if (!isNaN(page) && page !== vendedoresCurrentPage) {
            vendedoresCurrentPage = page;
            renderPaginaVendedores();
        }
    });

    $("#btn-limpar").on("click", function () {
        const user = window.currentUser || {};
        const canSelectAllDepartments = user.canSelectAllDepartments === true;
        const canSelectOtherSellers = user.canSelectOtherSellers === true;
        const currentUserId = user.id ? String(user.id) : null;

        $("#filial").val("").trigger('change.select2');

        if (canSelectAllDepartments) {
            $("#departamento").val("").trigger('change.select2');
            GetVendedor("");
            $("#vendedor").val("").trigger('change.select2');
        } else {
            const depto = user.departamentoId || window._meuDeptoTravado;
            if (depto) {
                $("#departamento").val(String(depto)).trigger('change.select2');
            }
            if (canSelectOtherSellers) {
                GetVendedor(depto);
                $("#vendedor").val("").trigger('change.select2');
            } else if (currentUserId) {
                $("#vendedor").val(currentUserId).trigger('change.select2');
            }
        }

        const now = new Date();
        const mesAtual = String(now.getMonth() + 1).padStart(2, '0');
        const anoAtual = now.getFullYear();

        $("#mes").val(mesAtual);
        $("#ano").val(anoAtual);

        carregarDashboard();
    });

    carregarDashboard();
}

document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({
        title: 'Verificando...',
        text: 'Aguarde enquanto verificamos sua permissão.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch(api_url + '/dashboard_permissoes/vendedores', { method: 'GET', headers: headers })
    .then(async (response) => {
        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Sem permissão');
        }
        return response.json();
    })
    .then(() => {
        Swal.close();
        initDashboard();
    })
    .catch((error) => {
        Swal.fire({ icon: 'error', title: 'Acesso Negado', html: error.message, allowOutsideClick: false })
        .then(() => { location.href = base_url + 'sales/dashboard'; });
    });
});