<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?>

<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>

    <section class="content">
        <?php if(isset($message)) {echo $message;} ?>
        <div class="row">
            <div class="col-md-12">
                 <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('unidades_facebook_api'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="alert alert-info alert-dismissible">
                            <h4><i class="icon fa fa-info"></i> Informação!</h4>
                            <p>Clique <a href="https://developers.facebook.com/docs/apps/register?locale=pt_BR" target="_blank">aqui</a> para saber <b>"Como criar um número de identificação do aplicativo?"</b></p>
                            <p>Na configuração da criação de um formulário no facebook, se atente a cadastrar campos com a descrição: <b>(name, full_name, nome_completo, email, telefone, telefone/whatsapp ou phone_number)</b></p>
                        </div>
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-facebook_api_unidade')); ?>
                            <div class="form-group">
                                <?php echo lang('status', 'status', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-10">
                                    <div class="radio">
                                        <label for="status_ativo"><input type="radio" id="status_ativo" name="status" value="S" <?php echo ($status == "S")?'checked="checked"':'' ?>><?php echo lang('status_ativo') ?></label>
                                    </div>
                                    <div class="radio">
                                        <label for="status_inativo"><input type="radio" id="status_inativo" name="status" value="N" <?php echo ($status == "N")?'checked="checked"':'' ?>><?php echo lang('status_inativo') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('app_id', 'app_id', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-5">
                                    <?php echo form_input($app_id);?>                                  
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('app_secret', 'app_secret', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-5">
                                    <?php echo form_input($app_secret);?>                                  
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('access_token', 'access_token', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-5">
                                    <?php echo form_input($access_token);?>
                                    <input type="hidden" name="expires_at" id="expires_at" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('pagina', 'pagina', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-5">
                                    <input type="hidden" name="page_name" id="page_name" value="<?php echo @$page_name ?>">
                                    <input type="hidden" name="page_id" id="page_id" value="<?php echo @$page_id ?>">
                                    <?php echo "<p>".form_button($btn_access_token)."</p>" ?>
                                    <div id="pagina_html">
                                        <?php if($page_id) { ?>
                                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="id: <?php echo $page_id ?>"> <button class="btn btn-primary" style="pointer-events: none;" type="button" disabled><?php echo $page_name ?></button> </span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('midia', 'midia', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-5">
                                    <?php echo form_dropdown($midia);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('evento', 'evento', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-5">
                                    <?php echo form_dropdown($evento);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('unidades', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                                    </div>
                                </div>
                            </div>
                        <?php echo form_close();?>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>
<script>
    
    function FBLogin(){
        FB.getLoginStatus(function(response) {
          if (response.status === 'connected') {
            console.log('Logged in.');
          }
          else {
            FB.login();
          }
        });
    }

    function subapp(page_id,page_access_token,page_name){
        $("#pagina_html").html('<span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="id: '+page_id+'"> <button class="btn btn-primary" style="pointer-events: none;" type="button" disabled>'+page_name+'</button> </span>');
        $("#page_name").val(page_name);
        $("#page_id").val(page_id);
        FB.api('/' + page_id + '/subscribed_apps?subscribed_fields=leadgen',
           'post',
           {access_token: page_access_token},
           function(response){
            console.log(response);
           }
        )
    }
    
    function gerarFbAccessToken() {
        if(jQuery("#app_id").val() == "") {
            createShowModal('Atenção', 'Não foi possível buscar as páginas e gerar o token, preencha o(s) campo(s): <br><br> - <b>ID do Aplicativo</b>');
            return false;
        }

        jQuery.ajax({
          url: '<?php echo base_url('unidades/has_fb_access_token') ?>',
          type: 'POST',
          data: {app_id:jQuery("#app_id").val()},
          success: function(data){
            var result = $.parseJSON(data);

            FB.init({
              appId      : jQuery("#app_id").val(),
              xfbml      : true,
              cookie     : true, 
              version    : 'v3.3'
            });

            if(result.erro == true) {
                FB.login(function(response){
                  console.log("Successufuly Loged in ", response);
                  jQuery.ajax({
                      url: '<?php echo base_url('unidades/update_fb_access_token') ?>',
                      type: 'POST',
                      data: {app_id:jQuery("#app_id").val(), access_token:response.authResponse.accessToken, expires_at:response.authResponse.expiresIn},
                      success: function(data){
                          var result = $.parseJSON(data);
                          console.log(result);

                          $("#access_token").val(response.authResponse.accessToken);
                          $("#expires_at").val(response.authResponse.expiresIn);
                          
                          FB.api('/me/accounts?limit=9999',function(response){
                              console.log("Successfully retrieved pages", response);
                              var pages = response.data;

                              var div = document.getElementById('pagina_html');
                              div.innerHTML = '';
                              for(var i = 0, len = pages.length; i < len ; i++){
                                  var page = pages[i];
                                  var a = document.createElement('a');
                                  a.href = "javascript:void(0);";
                                  a.id = page.id;
                                  a.onclick = subapp.bind(this,page.id,page.access_token,page.name);
                                  a.className = 'list-group-item list-group-item-action';
                                  a.innerHTML = page.name;
                                  div.appendChild(a);
                              }
                          })
                      }
                  });
                }, {scope: 'pages_show_list,leads_retrieval'});
            } else {                
                $("#access_token").val(result.token);
                $("#expires_at").val(result.expires_at);

                FB.login(function(response){
                  console.log("Successufuly 2 Loged in ", response);
                 
                  FB.api('/me/accounts?limit=9999',function(response){
                      console.log("Successfully 2 retrieved pages", response);
                      var pages = response.data;

                      var div = document.getElementById('pagina_html');
                      div.innerHTML = '';
                      for(var i = 0, len = pages.length; i < len ; i++){
                          var page = pages[i];
                          var a = document.createElement('a');
                          a.href = "javascript:void(0);";
                          a.id = page.id;
                          a.onclick = subapp.bind(this,page.id,page.access_token,page.name);
                          a.className = 'list-group-item list-group-item-action';
                          a.innerHTML = page.name;
                          div.appendChild(a);
                      }
                  })
                }, {scope: 'pages_show_list,leads_retrieval'});
            }
          }
        });
    }

    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));



    function createShowModal(title, body) {
        var html = '<div class="modal fade" tabindex="-1" role="dialog">' +
        '<div class="modal-dialog">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>' +
        '<h4 class="modal-title">' + title + '</h4>' +
        '</div>' +
        '<div class="modal-body">' +
        '<p>' + body + '</p>' +
        '</div>' +
        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

        var modal = $(html);
        modal.modal("show");
    }

</script>