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
                        <h3 class="box-title"><?php echo lang('cartao_recorrente'); ?> usuário <b><?php echo $user->row()->first_name." ".$user->row()->last_name ?></b></h3>
                    </div>
                    <div class="box-body">
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                <?php if(@is_array($dados_cartao)) { ?>
                                    <b>Bandeira:</b> <?php echo $dados_cartao["brand"] ?><br>
                                    <b>Nome:</b> <?php echo $dados_cartao["holder_name"] ?><br>
                                    <b>Número:</b> <?php echo $dados_cartao["display_number"] ?><br>
                                    <b>Bin:</b> <?php echo $dados_cartao["bin"] ?><br>
                                    <b>Mês/Ano:</b> <?php echo $dados_cartao["month"]."/".$dados_cartao["year"] ?>
                                <?php } else { ?>
                                    Não há cartão cadastrado.
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo lang('alterar_cartao_recorrente'); ?></h3>
                    </div>
                    <form id="payment-form-1" class="form-horizontal" action="" method="POST">
                        <div class="box-body">
                            <input type="hidden" name="token" id="token" value="" readonly="true" size="64" style="text-align:center" />
                            <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $customer_id ?>" />
                            <div class="usable-creditcard-form">
                              <div class="wrapperiugu">
                                <div class="input-group nmb_a">
                                  <div class="icon ccic-brand"></div>
                                    <input autocomplete="off" class="credit_card_number" data-iugu="number" placeholder="Número do Cartão" type="text" value="" />
                                  </div>
                                <div class="input-group nmb_b">
                                  <div class="icon ccic-cvv"></div>
                                    <input autocomplete="off" class="credit_card_cvv" data-iugu="verification_value" placeholder="CVV" type="text" value="" />
                                </div>
                                <div class="input-group nmb_c">
                                  <div class="icon ccic-name"></div>
                                    <input class="credit_card_name" data-iugu="full_name" placeholder="Titular do Cartão" type="text" value="" />
                                </div>
                                <div class="input-group nmb_d">
                                  <div class="icon ccic-exp"></div>
                                    <input autocomplete="off" class="credit_card_expiration" data-iugu="expiration" placeholder="MM/AA" type="text" value="" />
                                </div>
                              </div>
                              <div class="footer">
                                <img src="<?php echo base_url('assets/images/cc-icons.png') ?>" alt="Visa, Master, Diners. Amex" border="0" />
                                <a class="iugu-btn" href="http://iugu.com" target="_blank" tabindex="-1"><img src="<?php echo base_url('assets/images/payments-by-iugu.png') ?>" alt="Pagamentos por Iugu" border="0" /></a>
                              </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="btn-group">
                                <?php echo form_button(array('type' => 'submit', 'id' => 'btn-primary1', 'class' => 'btn btn-flat btn-success', 'content' => 'Validar')); ?>
                                <?php echo anchor('users/profile/'.$user_id, lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal -->
<div id="confirmCartaoPadraoModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <?php echo form_open("", array('class' => 'form-horizontal', 'id' => 'form-cartao_recorrencia')); ?>
        <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $customer_id ?>" />
        <input type="hidden" name="token" id="token_modal" value="" readonly="true" size="64" style="text-align:center" />
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Validação do cartão de crédito</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
                <p class="msg_retorno col-sm-12"></p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" id="salvar_cartao_padrao" style="display:none;" class="btn btn-danger">Salvar</button>
            <button type="button" data-dismiss="modal" class="btn btn btn-default">Fechar</button>
        </div>
      <?php echo form_close();?>
    </div>
  </div>
</div>

<script type="text/javascript">
    window.onload = function() {
        Iugu.setAccountID("<?php echo $unidade->iugu_acc_id ?>");
        Iugu.setTestMode(false);
        jQuery(function($) {
          $('#payment-form-1').submit(function(evt) {
                var form = $(this);
                var tokenResponseHandler = function(data) {
                    $("#confirmCartaoPadraoModal").modal('show');
                    var html = "";
                    if (data.errors) {
                        for (var prop in data.errors) {
                            if (!data.errors.hasOwnProperty(prop)) continue;
                            if(prop == "number")
                                html += "Número do cartão incorreto<br>";
                            if(prop == "verification_value")
                                html += "CVV incorreto<br>";
                            if(prop == "first_name")
                                html += "Nome incorreto<br>";
                            if(prop == "last_name")
                                html += "Sobrenome incorreto<br>";
                            if(prop == "expiration")
                                html += "Expiração incorreto<br>";
                        }
                        $(".msg_retorno").html(html);
                        $("#confirmCartaoPadraoModal").addClass('modal-warning');
                    } else {
                        $(".msg_retorno").html("Cartão de crédito validado com sucesso, confirme a alteração no botão abaixo.");
                        $("#salvar_cartao_padrao").show();
                        $("#token").val( data.id );
                        $("#token_modal").val( data.id );
                        $("#confirmCartaoPadraoModal").addClass('modal-success');
                    }
                };
                Iugu.createPaymentToken(this, tokenResponseHandler);
                return false;
            });
        });
    };
</script>