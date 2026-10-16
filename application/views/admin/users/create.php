<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-info"></i> Não encontrou alguns campos? Não se preocupe!</h4>
            Nome, sobrenome e senha serão preenchidos pelo usuário na ativação de seu cadastro.
        </div>
        <?php if(isset($message)) {echo $message;} ?>
        <div class="row">
            <div class="col-md-12">
                <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-user')); ?>
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo lang('users_create_user'); ?></h3>
                        </div>
                        <div class="box-body">
                            <?php if($prefs->cadastro_user_completo == 1): ?>
                                <div class="form-group">
                                    <?php echo lang('users_firstname', 'first_name', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_input($first_name);?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo lang('users_lastname', 'last_name', array('class' => 'col-sm-2 control-label'), true); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_input($last_name);?>
                                    </div>
                                </div>
                            <?php endif ?>
                            <div class="form-group">
                                <?php echo lang('users_email', 'email', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <?php echo form_input($email);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('users_cpf', 'users_cpf', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($cpf);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('users_rg', 'users_rg', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($rg);?>
                                </div>
                            </div>

                            <?php //if(@$tipo_user != 0) { ?>
                                <?php //if($tipo_user == 1) { ?>
                                    <div id="inputs_aluno">
                                        <!-- aluno -->
                                        <div class="form-group">
                                            <?php echo lang('users_data_nascimento', 'data_nascimento', array('class' => 'col-sm-2 control-label'), false); ?>
                                            <div class="col-sm-2 input-group date" id='datetimepicker'>
                                                <?php echo form_input($data_nascimento);?>
                                                <span class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('users_telefone', 'telefone', array('class' => 'col-sm-2 control-label'), false); ?>
                                            <div class="col-sm-2">
                                                <?php echo form_input($telefone);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('users_celular', 'celular', array('class' => 'col-sm-2 control-label'), false); ?>
                                            <div class="col-sm-2">
                                                <?php echo form_input($celular);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('users_cep', 'cep', array('class' => 'col-sm-2 control-label'), false); ?>
                                            <div class="col-sm-1">
                                                <?php echo form_input($cep);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('users_logradouro', 'logradouro', array('class' => 'col-sm-2 control-label'), false); ?>
                                            <div class="col-sm-10">
                                                <?php echo form_input($logradouro);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('users_numero', 'numero', array('class' => 'col-sm-2 control-label'), false); ?>
                                            <div class="col-sm-1">
                                                <?php echo form_input($numero);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('users_bairro', 'bairro', array('class' => 'col-sm-2 control-label'), false); ?>
                                            <div class="col-sm-4">
                                                <?php echo form_input($bairro);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('users_cidade', 'cidade', array('class' => 'col-sm-2 control-label'), false); ?>
                                            <div class="col-sm-4">
                                                <?php echo form_input($cidade);?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php echo lang('users_uf', 'uf', array('class' => 'col-sm-2 control-label'), false); ?>
                                            <div class="col-sm-4">
                                                <?php echo form_dropdown($uf);?>
                                            </div>
                                        </div>
                                    </div>
                                <?php //} ?>
                            <?php //} ?>
                            <!--<div id="inputs_vendedor" style="display:none;">
                                <div class="form-group">
                                    <?php echo lang('users_cupom_vendedor', 'cupom_vendedor', array('class' => 'col-sm-2 control-label'), false); ?>
                                    <div class="col-sm-4">
                                        <?php echo form_input($cupom_vendedor);?>
                                    </div>
                                </div>
                            </div>-->
                            
                            <?php if($prefs->cadastro_user_completo == 1): ?>
                                <hr>
                                <div class="form-group">
                                    <?php echo lang('users_password', 'password', array('class' => 'col-sm-2 control-label')); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_input($password);?>
                                        <div class="progress" style="margin:0">
                                            <div class="pwstrength_viewport_progress"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo lang('users_password_confirm', 'password_confirm', array('class' => 'col-sm-2 control-label')); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_input($password_confirm);?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo lang('senha_atual', 'senha_atual', array('class' => 'col-sm-2 control-label')); ?>
                                    <div class="col-sm-10">
                                        <?php echo form_input($senha_atual);?>
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo lang('users_create_empresas'); ?></h3>
                        </div>
                        <div class="box-body">
                            <!-- Custom Tabs -->
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs">
                                    <?php $i=1; foreach ($dados_empresa as $key => $emp) { ?>
                                        <li class="<?php if($empresa_selecionada == $key){ echo 'active'; } ?>"><a href="#tab_<?php echo $key ?>" data-toggle="tab" aria-expanded="<?php if($empresa_selecionada == $key){ echo 'true'; } ?>"><?php echo $emp['desc'] ?></a></li>
                                    <?php $i++; } ?>
                                </ul>
                                <div class="tab-content">
                                    <?php $i=1; foreach ($dados_empresa as $key => $emp) { ?>
                                      <div class="tab-pane <?php if($empresa_selecionada == $key){ echo 'active'; } ?>" id="tab_<?php echo $key; ?>">
                                            <div class="form-group">
                                                <?php echo lang('users_active', 'active', array('class' => 'col-sm-2 control-label'), true);?>
                                                <div class="col-sm-10">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input id="active-<?php echo $key ?>" type="checkbox" name="active[<?php echo $key ?>]" value="1"
                                                            <?php 
                                                                if(@$dados_empresa[$key]['currentEmps'] == 1) {
                                                                    echo " ";
                                                                }
                                                            ?>
                                                            >
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <?php echo lang('users_member_of_groups', 'groups', array('class' => 'col-sm-2 control-label'), true);?>
                                                <div class="col-sm-10">
                                                    <?php foreach ($emp['grupos'] as $group):
                                                        if((!$is_admin && $group['id'] != $emp['grupo_admin']) || ($is_admin)){
                                                            if(!$has_users_create && @in_array($group['id'], $dados_empresa[$key]['currentGroups'])){
                                                                if(@$tipo_user != 0){
                                                                    if($tipo_user == 1){
                                                                        echo "<input type='hidden' name='groups[]' value='".$group['id']."'>";
                                                                    }
                                                                }
                                                            }
                                                    ?>
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input id="groups-<?php echo $key ?>" type="checkbox" name="groups[]" value="<?php echo $group['id'];?>"
                                                                        <?php 
                                                                            if(@in_array($group['id'], $dados_empresa[$key]['currentGroups'])) {
                                                                                echo " checked";
                                                                            }
                                                                            if($dados_empresa[$key]['grupo_aluno'] == $group['id']) {
                                                                                echo " class='grupo_aluno'";
                                                                            }
                                                                            if($dados_empresa[$key]['grupo_vendedor'] == $group['id']) {
                                                                                echo " class='grupo_vendedor'";
                                                                            }
                                                                            if(!$has_users_create) {
                                                                                if(@$tipo_user != 0){
                                                                                    if($tipo_user == 1){
                                                                                        echo " disabled='disabled'";
                                                                                    }
                                                                                }
                                                                            }
                                                                        ?>
                                                                    >
                                                                    <?php echo htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                                </label>
                                                            </div>
                                                        <?php } ?>
                                                    <?php endforeach?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <?php echo lang('users_setor', 'setores', array('class' => 'col-sm-2 control-label'), true);?>
                                                <div class="col-sm-10">             
                                                    <select name="setores[]" id="setores-<?php echo $key ?>" class="form-control">
                                                        <option value="">Selecione</option>
                                                        <?php foreach ($emp['setores'] as $setor):?>
                                                            <option value="<?php echo $setor['id'] ?>"
                                                                <?php if(@in_array($setor['id'], $dados_empresa[$key]['currentSetores'])):
                                                                    echo " selected";
                                                                endif; ?>
                                                            ><?php echo $setor['descricao'] ?></option>
                                                        <?php endforeach?>
                                                    </select>        
                                                    <?php //echo form_dropdown($setor);?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <?php echo lang('users_unidade', 'unidade', array('class' => 'col-sm-2 control-label'), true); ?>
                                                <div class="col-sm-10">
                                                    <table id="unidade_table<?php echo $i ?>" class="table table-bordered table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th class="no-sort" style="text-align: center;"><input id="table<?php echo $i ?>" name="select_all" value="1" class="unidades-select-all" type="checkbox"></th>
                                                                <th>Empresa</th>
                                                                <th>Nome Fantasia</th>
                                                                <th>Razão Social</th>
                                                                <th>E-mail</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($emp['unidades'] as $unid):?>
                                                                <tr>
                                                                    <td style="text-align: center;"><input id="unidades-<?php echo $key ?>" type="checkbox" name="unidades[]" value="<?php echo $unid['id'];?>"
                                                                    <?php if(@in_array($unid['id'], $dados_empresa[$key]['currentUnids'])):
                                                                            echo " checked";
                                                                        endif; ?>
                                                                    ></td>
                                                                    <td><?php echo $unid['nome_empresa'] ?></td>
                                                                    <td><?php echo $unid['unidade'] ?></td>
                                                                    <td><?php echo $unid['razao_social'] ?></td>
                                                                    <td><?php echo $unid['email'] ?></td>
                                                                </tr>
                                                            <?php endforeach?>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                              <th></th>
                                                              <th>Empresa</th>
                                                              <th>Nome Fantasia</th>
                                                              <th>Razão Social</th>
                                                              <th>E-mail</th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                      </div>
                                  <?php $i++; } ?>
                                </div>
                                <!-- /.tab-content -->
                            </div>
                            <!-- nav-tabs-custom -->

                            
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('users', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php echo form_close();?>
            </div>
        </div>
    </section>
</div>

<?php 
    $i=1; $script_html = ""; 
    foreach ($dados_empresa as $emp) { 
        $script_html .= "
            var table".$i." = $('#unidade_table".$i."').DataTable({
              'paging'      : true,
              'lengthChange': false,
              'searching'   : true,
              'ordering'    : true,
              'aoColumnDefs': [
                  { 'bSortable': false, 'aTargets': [ 0 ] }
               ],
              'order': [[1, 'asc']],
              'info'        : true,
              'autoWidth'   : false,
              'oLanguage': {
                'sProcessing':   'Processando...',
                'sLengthMenu':   'Mostrar _MENU_ registros',
                'sZeroRecords':  'Não foram encontrados resultados',
                'sInfo':         'Mostrando de _START_ até _END_ de _TOTAL_ registros',
                'sInfoEmpty':    'Mostrando de 0 até 0 de 0 registros',
                'sInfoFiltered': '',
                'sInfoPostFix':  '',
                'sSearch':       'Buscar:',
                'sUrl':          '',
                'oPaginate': {
                    'sFirst':    'Primeiro',
                    'sPrevious': 'Anterior',
                    'sNext':     'Seguinte',
                    'sLast':     'Último'
                }
            }
          });
           $('.unidades-select-all').on('click', function(){
              var table_name = $(this).attr('id');
              var rows = $('#unidade_'+table_name).DataTable().rows({ 'search': 'applied' }).nodes();
              $('input[type=\"checkbox\"]', rows).prop('checked', this.checked);
           });

           $('#unidade_table".$i." tbody').on('change', 'input[type=\"checkbox\"]', function(){
              if(!this.checked){
                 var el = $('.unidades-select-all').get(0);
                 if(el && el.checked && ('indeterminate' in el)){
                    el.indeterminate = true;
                 }
              }
           });
        ";
    $i++; }

    $script_checkbox_rows_include = "";

    $script_checkbox_rows_include .= "
       $('#form-user').on('submit', function(e){
          var form = this;
    ";
    $i=1;
    foreach ($dados_empresa as $emp) { 
        $script_checkbox_rows_include .= "
            table".$i.".$('input[type=\"checkbox\"]').each(function(){
                 if(!$.contains(document, this)){
                    if(this.checked){
                       $(form).append(
                          $('<input>')
                             .attr('type', 'hidden')
                             .attr('name', this.name)
                             .val(this.value)
                       );
                    }
                 }
            });
        ";
    $i++; }
    $script_checkbox_rows_include .= "
        });
    ";
?>

<script type="text/javascript">
window.onload = function() {
    <?php echo $script_html; ?>
    <?php echo $script_checkbox_rows_include; ?>

    $('.grupo_aluno').change(function() {
        if ($(".grupo_aluno:checkbox:checked").length > 0) {
            $("#inputs_aluno").fadeIn();
        }
        else {
            $("#inputs_aluno").fadeOut();
        }
    });

    /*$('.grupo_vendedor').change(function() {
        if ($(".grupo_vendedor:checkbox:checked").length > 0) {
            $("#inputs_vendedor").fadeIn();
        }
        else {
            $("#inputs_vendedor").fadeOut();
        }
    });*/
}

</script>