import { api_get } from "../../../helpers/api/get.js";

function get_params_modal(id) {
      var params = {
            "responsive": true, 
            "lengthChange": true, 
            "pageLength" : 10,
            "autoWidth": false,
            "ordering": true,
            "language": {
                  "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
            },
            "searching": true,
            "processing": true,
            "serverSide": true,
            "columnDefs": [
                  { "responsivePriority": 1, "targets": 0 },
                  { "responsivePriority": 2, "targets": 1 },
                  { "responsivePriority": 3, "targets": 2 },
                  { "responsivePriority": 4, "targets": 3 },
                  { "responsivePriority": 5, "targets": 4 },
                  { "responsivePriority": 6, "targets": 5 },
            ],
            "lengthMenu": [ [10, 25, 50], [10, 25, 50] ],
            "aaSorting": [ [0,'desc'] ],
            "initComplete": function () {
                  $(id+'_filter label input').focus();
            },
            "ajax": async (data, callback) => {
                  var column_to_order = "";
                  if(data.order[0].column == 0)
                        column_to_order = "codigo";
                  if(data.order[0].column == 1)
                        column_to_order = "nome";
                  if(data.order[0].column == 2)
                        column_to_order = "apelido";
                  if(data.order[0].column == 3)
                        column_to_order = "cidade";
                  if(data.order[0].column == 4)
                        column_to_order = "estado";
                  if(data.order[0].column == 5)
                        column_to_order = "tipo";
                  if(data.order[0].column == 6)
                        column_to_order = "ativo";

                  var params_get = { 
                        offset: data.start, 
                        limit: data.length, 
                        ordem: JSON.stringify([{campo : column_to_order, ordem : data.order[0].dir}]),
                        texto : data.search.value,
                        ativo: 'S'
                  };

                  api_get('/colaborador/', params_get).then(response => {
                        const objs = [];
                        
                        $.each(response.data, function (index, value) {

                              var tipo = "";
                              if(value.tipo == "V")
                                    tipo = "Vendedor";
                              else if(value.tipo == "F")
                                    tipo = "Funcionário";
                              else if(value.tipo == "I")
                                    tipo = "Indicação";
                              else if(value.tipo == "P")
                                    tipo = "Professor";
                              else if(value.tipo == "T")
                                    tipo = "Técnico";
                        
                              let obj = [];
                              obj.push(value.codigo);
                              obj.push(value.nome);
                              obj.push(value.apelido);
                              obj.push(value.cidade);
                              obj.push(value.estado);
                              obj.push(tipo);
                              obj.push((value.ativo == "S") ? "Sim" : "Não");
                              objs.push(obj);
                        });
                        var json_data = {
                              "draw"            : data.draw,  
                              "recordsTotal"    : response.total,  
                              "recordsFiltered" : response.total, 
                              "data"            : objs
                        };
                        
                        callback(json_data);
                  })
            }
      };
      return params;
}

function get_params_list() {
      var params = {
            "responsive": true, 
            "lengthChange": true, 
            "pageLength" : 10,
            "autoWidth": false,
            "ordering": true,
            "language": {
                  "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
            },
            "searching": true,
            "processing": true,
            "serverSide": true,
            "columnDefs": [
                  { "responsivePriority": 1, "targets": 0, 'checkboxes': {'selectRow': true}, 'orderable': false },
                  { "responsivePriority": 2, "targets": 1 },
                  { "responsivePriority": 3, "targets": 2 },
                  { "responsivePriority": 4, "targets": 3 },
                  { "responsivePriority": 5, "targets": 4 },
                  { "responsivePriority": 6, "targets": 5 },
                  { "responsivePriority": 7, "targets": 6 },
                  { 'orderable': false, 'targets': [ -1 ] }
            ],
            "select": {'style': 'multi'},
            "sDom": 'Brtipl',
            "buttons": [
                  {
                        extend: 'excelHtml5',
                        exportOptions: {
                          columns: [':visible:not(:eq(0), :eq(-1))']
                        }
                  },
                  {
                        extend: 'csvHtml5',
                        exportOptions: {
                          columns: [':visible:not(:eq(0), :eq(-1))']
                        }
                  },
                  {
                        extend: 'pdfHtml5',
                        exportOptions: {
                          columns: [':visible:not(:eq(0), :eq(-1))']
                        }
                  }
            ],
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "Todos"] ],
            "aaSorting": [ [1,'desc'] ],
            "ajax": async (data, callback) => {
                  var column_to_order = "";
                  if(data.order[0].column == 0)
                        column_to_order = "codigo";
                  if(data.order[0].column == 1)
                        column_to_order = "codigo";
                  if(data.order[0].column == 2)
                        column_to_order = "nome";
                  if(data.order[0].column == 3)
                        column_to_order = "apelido";
                  if(data.order[0].column == 4)
                        column_to_order = "cidade";
                  if(data.order[0].column == 5)
                        column_to_order = "estado";
                  if(data.order[0].column == 6)
                        column_to_order = "tipo";
                  if(data.order[0].column == 7)
                        column_to_order = "ativo";
                  
                  var params_get = { 
                        offset: data.start, 
                        limit: data.length, 
                        ordem: JSON.stringify([{campo : column_to_order, ordem : data.order[0].dir}]),
                        texto : $("input[name=qtxt]").val(), 
                        ativo : $("select[name=ativo]").val(), 
                        data_inicial : (moment($("input[name=data_inicial]").val(), 'DD/MM/YYYY').isValid()) ? moment($("input[name=data_inicial]").val(), 'DD/MM/YYYY').format('YYYY-MM-DD') : "", 
                        data_final : (moment($("input[name=data_final]").val(), 'DD/MM/YYYY').isValid()) ? moment($("input[name=data_final]").val(), 'DD/MM/YYYY').format('YYYY-MM-DD') : ""
                  };

                  api_get('/colaborador/', params_get).then(response => {
                        const objs = [];
                        
                        $.each(response.data, function (index, value) {

                              var tipo = "";
                              if(value.tipo == "V")
                                    tipo = "Vendedor";
                              else if(value.tipo == "F")
                                    tipo = "Funcionário";
                              else if(value.tipo == "I")
                                    tipo = "Indicação";
                              else if(value.tipo == "P")
                                    tipo = "Professor";
                              else if(value.tipo == "T")
                                    tipo = "Técnico";

                              let obj = [];
                              obj.push(value.codigo);
                              obj.push(value.codigo);
                              obj.push(value.nome);
                              obj.push(value.apelido);
                              obj.push(value.cidade);
                              obj.push(value.estado);
                              obj.push(tipo);
                              obj.push((value.ativo == "S") ? "Sim" : "Não");
                              obj.push('<div class="btn-group">'+
                                  '<a href="'+base_url+'/general/collaborators/view/'+value.codigo+'" class="btn btn-default">Ver</a>'+
                                  '<button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false">'+
                                    '<span class="sr-only">Toggle Dropdown</span>'+
                                  '</button>'+
                                  '<div class="dropdown-menu" role="menu" style="">'+
                                    '<a class="dropdown-item" href="'+base_url+'/general/collaborators/edit/'+value.codigo+'">Editar</a>'+
                                    '<a class="dropdown-item" href="'+base_url+'/general/collaborators/permissions/'+value.codigo+'">Configurar Permissões</a>'+
                                  '</div>'+
                                '</div>');
                              objs.push(obj);
                        });
                        var json_data = {
                              "draw"            : data.draw,  
                              "recordsTotal"    : response.total,  
                              "recordsFiltered" : response.total, 
                              "data"            : objs
                        };
                        
                        callback(json_data);
                  })
            }
      };
      return params;
}

function get_html_list() {
      return '<table id="datatable" class="table table-striped">'+
            '<thead>'+
                  '<tr>'+
                        '<th></th>'+
                        '<th>Cód.</th>'+
                        '<th>Nome</th>'+
                        '<th>Apelido</th>'+
                        '<th>Cidade</th>'+
                        '<th>Estado</th>'+
                        '<th>Tipo</th>'+
                        '<th>Ativo</th>'+
                        '<th>Ações</th>'+
                  '</tr>'+
            '</thead>'+
            '<tbody></tbody>'+
      '</table>';
}

function get_html_modal() {
      return '<div class="modal fade" id="modal-dt-collaborators" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">'+
        '<div class="modal-dialog modal-xl" role="document">'+
          '<div class="modal-content">'+
            '<div class="modal-header">'+
              '<h5 class="modal-title">Colaboradores</h5>'+
              '<button type="button" class="close" data-dismiss="modal" aria-label="Close">'+
                '<span aria-hidden="true">&times;</span>'+
              '</button>'+
            '</div>'+
            '<div class="modal-body">'+
              '<table id="datatable" class="table table-striped" style="cursor:pointer">'+
                '<thead>'+
                  '<tr>'+
                        '<th>Cód.</th>'+
                        '<th>Nome</th>'+
                        '<th>Apelido</th>'+
                        '<th>Cidade</th>'+
                        '<th>Estado</th>'+
                        '<th>Tipo</th>'+
                        '<th>Ativo</th>'+
                  '</tr>'+
                '</thead>'+
                '<tbody></tbody>'+
              '</table>'+
            '</div>'+
            '<div class="modal-footer">'+
              '<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>'+
            '</div>'+
          '</div>'+
        '</div>'+
      '</div>';
}

function datatable_html(is_modal = false) {
      /* gerando html para listagem em tela */
      if(is_modal == false) {
            var html = get_html_list();
      } 
      /* gerando html para listagem em modal */
      else {
            var html = get_html_modal();
      }
      return html;
}


function datatable(is_modal = false, id = "#datatable") {

      /* gerando parâmetros para listagem em tela */
      if(is_modal == false) {
            var params = get_params_list();
      } 
      /* gerando parâmetros para listagem em modal */
      else {
            var params = get_params_modal(id);
      }

      return $(id).DataTable(params);
};

export { datatable_html, datatable };