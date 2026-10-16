import { api_get } from "../../../helpers/api/get.js";

// Armazena as permissões concedidas no banco de dados inicialmente
let _permissoesIniciais = new Set();

// Armazena as permissões atualmente selecionadas pelo usuário na interface
let _permissoesConcedidas = new Set();

// Renderiza a árvore de permissões organizada por módulos
async function render_permissoes(permissoesAtuais) {
  const listaInicial = (permissoesAtuais || [])
    .map((item) => {
      if (typeof item === "object" && item !== null) {
        return String(item.opcoes ?? item.operacoes ?? item.item_idx ?? "");
      }
      return String(item);
    })
    .filter(Boolean);

  _permissoesIniciais = new Set(listaInicial);
  _permissoesConcedidas = new Set(listaInicial);

  try {
    const arvore = await api_get("/permissoes_arvore/", {});

    $("#perm-loading").hide();
    $("#perm-container").show();

    // Ordena módulos por ID (menor para maior)
    const modulosOrdenados = (arvore.data || []).sort((a, b) => a.id - b.id);

    renderizarAbas(modulosOrdenados);
    initBusca();
  } catch (erro) {
    $("#perm-loading").html(
      '<i class="fas fa-exclamation-triangle text-danger" style="font-size:36px"></i>' +
        '<p class="mt-2 text-danger">Erro ao carregar permissões. Tente novamente.</p>',
    );
    console.error("Erro ao carregar árvore de permissões:", erro);
  }
}

// Gera as abas de módulos e o conteúdo de cada aba
function renderizarAbas(modulos) {
  const $tabs = $("#modulos-tabs");
  const $content = $("#modulos-content");
  $tabs.empty();
  $content.empty();

  modulos.forEach((modulo, idx) => {
    const moduloId = "modulo-" + idx;
    const isActive = idx === 0 ? "active" : "";
    const isShow = idx === 0 ? "show active" : "";

    // Aba
    $tabs.append(
      '<li class="nav-item">' +
        '<a class="nav-link ' +
        isActive +
        '" data-toggle="tab" href="#' +
        moduloId +
        '" role="tab">' +
        escapeHtml(modulo.nome) +
        "</a>" +
        "</li>",
    );

    // Conteúdo da aba
    const $tabPane = $(
      '<div class="tab-pane fade p-3 ' +
        isShow +
        '" id="' +
        moduloId +
        '" role="tabpanel">' +
        '<ul class="perm-tree"></ul>' +
        "</div>",
    );

    const $tree = $tabPane.find(".perm-tree");
    renderizarNos(modulo.children, $tree, 0);

    $content.append($tabPane);
  });
}

// Renderiza nós da árvore recursivamente
function renderizarNos(nos, $container, nivel) {
  if (!nos || nos.length === 0) return;

  // Ordena: menus e submenus primeiro, funções depois (mantendo a ordem interna)
  const nosOrdenados = [...nos].sort((a, b) => {
    if (a.tipo === "funcao" && b.tipo !== "funcao") return 1;
    if (a.tipo !== "funcao" && b.tipo === "funcao") return -1;
    return 0;
  });

  nosOrdenados.forEach((no) => {
    if (no.tipo === "funcao") {
      renderizarItemFuncao(no, $container, nivel);
    } else {
      renderizarMenuSubmenu(no, $container, nivel);
    }
  });
}

// Renderiza um menu ou submenu (nó expansível) — seta à direita, sem badge
function renderizarMenuSubmenu(no, $container, nivel) {
  const itemId = "tree-" + Math.random().toString(36).substr(2, 9);
  const icone = no.tipo === "menu" ? "fa-folder" : "fa-folder-open";
  const paddingLeft = nivel * 20 + "px";

  const $item = $(
    '<li class="perm-tree-item">' +
      '<div class="perm-tree-toggle collapsed" style="padding-left:calc(10px + ' +
      paddingLeft +
      ')">' +
      '<span class="perm-icon"><i class="fas ' +
      icone +
      ' text-warning"></i></span>' +
      '<span class="perm-label">' +
      escapeHtml(no.nome) +
      "</span>" +
      '<span class="perm-arrow"><i class="fas fa-chevron-down"></i></span>' +
      "</div>" +
      '<ul class="perm-tree perm-tree-children" id="' +
      itemId +
      '"></ul>' +
      "</li>",
  );

  const $children = $item.find(".perm-tree-children");

  if (no.children && no.children.length > 0) {
    renderizarNos(no.children, $children, nivel + 1);
  }

  $item.find("> .perm-tree-toggle").on("click", function (e) {
    // Se clicou na seta, apenas expande ou colapsa os filhos
    if ($(e.target).closest(".perm-arrow").length) {
      $(this).toggleClass("collapsed");
      $children.toggleClass("show");
      return;
    }

    // Expande ou colapsa a árvore
    $(this).toggleClass("collapsed");
    $children.toggleClass("show");

    // Remove active de todos os nós e ativa o selecionado
    $(".perm-funcao-toggle, .perm-tree-toggle").removeClass("active");
    $(this).addClass("active");

    // Exibe as funções do menu ou submenu no painel direito
    exibirFuncoes(no);
  });

  $container.append($item);
}

// Renderiza um nó tipo funcao — só o nome, sem ícone, sem badge, sem código
function renderizarItemFuncao(no, $container, nivel) {
  const paddingLeft = nivel * 20 + "px";
  const temFilhos = no.children && no.children.length > 0;

  const $funcaoEl = $(
    '<li class="perm-tree-item">' +
      '<div class="perm-funcao-toggle" style="padding-left:calc(10px + ' +
      paddingLeft +
      ')" ' +
      'data-opcao="' +
      escapeHtml(no.opcao) +
      '">' +
      '<span class="perm-label">' +
      escapeHtml(no.nome) +
      "</span>" +
      "</div>" +
      "</li>",
  );

  $funcaoEl.find(".perm-funcao-toggle").on("click", function () {
    $(".perm-funcao-toggle, .perm-tree-toggle").removeClass("active");
    $(this).addClass("active");
    exibirFuncoes(no);
  });

  $container.append($funcaoEl);

  if (temFilhos) {
    const itemId = "tree-" + Math.random().toString(36).substr(2, 9);

    const $subItem = $(
      '<li class="perm-tree-item">' +
        '<ul class="perm-tree perm-tree-children show" id="' +
        itemId +
        '"></ul>' +
        "</li>",
    );

    const $children = $subItem.find(".perm-tree-children");
    renderizarNos(no.children, $children, nivel + 1);
    $container.append($subItem);
  }
}

// Exibe as permissoes_funcoes do nó selecionado no painel direito
function exibirFuncoes(no) {
  const $painel = $("#painel-detalhe");
  const funcoes = no.funcoes || [];

  // Se for um menu ou submenu sem funções, mantém mensagem padrão
  if (no.tipo !== "funcao" && funcoes.length === 0) {
    $(".perm-tree-toggle").removeClass("active");
    $painel.html(
      '<div class="perm-detalhe-vazio">' +
        '<i class="fas fa-hand-pointer"></i>' +
        "<p>Selecione uma função para ver os detalhes</p>" +
        "</div>",
    );
    return;
  }

  let html =
    '<div class="perm-detalhe-titulo">' + escapeHtml(no.nome) + "</div>";

  if (funcoes.length === 0) {
    html +=
      '<div class="text-muted text-center py-3">Nenhuma permissão cadastrada para este item.</div>';
    $painel.html(html);
    return;
  }

  html +=
    '<table class="table table-sm table-bordered mb-0">' +
    '<thead class="thead-light">' +
    "<tr>" +
    "<th>O que esta permissão libera</th>" +
    '<th class="text-center" style="width:90px">Conceder</th>' +
    "</tr>" +
    "</thead>" +
    "<tbody>";

  funcoes.forEach((funcao, idx) => {
    const itemIdxStr = String(funcao.item_idx);
    const checked = _permissoesConcedidas.has(itemIdxStr) ? "checked" : "";
    const switchId = "chk-painel-" + idx;

    html +=
      "<tr>" +
      "<td>" +
      escapeHtml(funcao.item) +
      "</td>" +
      '<td class="text-center">' +
      '<div class="custom-control custom-switch d-flex justify-content-center">' +
      '<input type="checkbox" class="custom-control-input chk_opcoes" ' +
      'id="' +
      switchId +
      '" ' +
      'value="' +
      escapeHtml(itemIdxStr) +
      '" ' +
      checked +
      ">" +
      '<label class="custom-control-label" for="' +
      switchId +
      '"></label>' +
      "</div>" +
      "</td>" +
      "</tr>";
  });

  html += "</tbody></table>";

  $painel.html(html);

  // Monitora alterações dos checkboxes no painel de detalhes
  $painel.find(".chk_opcoes").on("change", function () {
    const valor = String($(this).val());
    if ($(this).is(":checked")) {
      // Adiciona permissão concedida
      _permissoesConcedidas.add(valor);
    } else {
      // Remove permissão desmarcada
      _permissoesConcedidas.delete(valor);
    }
  });
}

// Retorna o array com os códigos das permissões atualmente concedidas
function get_permissoes_selecionadas() {
  return Array.from(_permissoesConcedidas);
}

// Retorna o array com os códigos das permissões iniciais do banco
function get_permissoes_iniciais() {
  return Array.from(_permissoesIniciais);
}

// Calcula as diferenças entre o estado inicial e o estado atual para POST e DELETE
function get_diferenca_permissoes() {
  const adicionar = [];
  const remover = [];

  // Permissões que foram marcadas e não estavam no banco inicialmente (criar registro)
  _permissoesConcedidas.forEach((item) => {
    if (!_permissoesIniciais.has(item)) {
      adicionar.push(item);
    }
  });

  // Permissões que estavam no banco inicialmente e foram desmarcadas (excluir registro)
  _permissoesIniciais.forEach((item) => {
    if (!_permissoesConcedidas.has(item)) {
      remover.push(item);
    }
  });

  return { adicionar, remover };
}

// Atualiza o estado inicial após salvar com sucesso
function atualizar_permissoes_iniciais(novasPermissoes) {
  _permissoesIniciais = new Set(novasPermissoes);
  _permissoesConcedidas = new Set(novasPermissoes);
}

// Escapa HTML para prevenir XSS
function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// Inicializa a lógica de busca/filtro das permissões e abas
function initBusca() {
  $("#input-busca-permissoes").off("input").on("input", function () {
    const termo = $(this).val().toLowerCase().trim();

    if (termo === "") {
      $(".nav-item, .perm-tree-item").show();
      // Recolhe todos os menus e submenus novamente
      $(".perm-tree-toggle").addClass("collapsed");
      $(".perm-tree-children").removeClass("show");
      return;
    }

    $("#modulos-tabs .nav-item").each(function () {
      const $aba = $(this);
      const $link = $aba.find(".nav-link");
      const targetId = $link.attr("href");
      const $conteudo = $(targetId);

      let encontrouNoModulo = false;

      // Esconde todos os itens da árvore primeiro
      $conteudo.find(".perm-tree-item").hide();

      // Itera sobre todas as funções e menus/submenus
      $conteudo.find(".perm-funcao-toggle, .perm-tree-toggle").each(function () {
        const texto = $(this).find(".perm-label").text().toLowerCase();

        if (texto.includes(termo)) {
          encontrouNoModulo = true;

          // Mostra o item que deu match
          const $item = $(this).closest(".perm-tree-item");
          $item.show();

          // Mostra os ancestrais e expande-os para facilitar a navegação
          $item.parents(".perm-tree-item").show().each(function () {
            $(this).find("> .perm-tree-toggle").removeClass("collapsed");
            $(this).find("> .perm-tree-children").addClass("show");
          });

          // Se a busca bateu com o nome de um menu, exibe todos os seus descendentes
          if ($(this).hasClass("perm-tree-toggle")) {
            $item.find(".perm-tree-item").show();
          }
        }
      });

      // Verifica se encontrou dentro do módulo ou se o nome do próprio módulo bate com a busca
      if (encontrouNoModulo || $link.text().toLowerCase().includes(termo)) {
        $aba.show();
        // Se a busca bateu com o nome do módulo inteiro, mostra tudo dele
        if ($link.text().toLowerCase().includes(termo)) {
          $conteudo.find(".perm-tree-item").show();
        }
      } else {
        $aba.hide();
      }
    });

    // Ativa automaticamente a primeira aba visível se a aba atual foi escondida
    if (!$("#modulos-tabs .nav-item:visible .nav-link.active").length) {
      $("#modulos-tabs .nav-item:visible").first().find(".nav-link").tab("show");
    }
  });
}

export {
  render_permissoes,
  get_permissoes_selecionadas,
  get_permissoes_iniciais,
  get_diferenca_permissoes,
  atualizar_permissoes_iniciais,
};
