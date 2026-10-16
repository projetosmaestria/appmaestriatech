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
                        <h3 class="box-title"><?php echo lang('pesquisa_perfil_edit'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-edit_pesquisa_perfil')); ?>
                            <div class="form-group">
                                <?php echo lang('nome', 'nome', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_input($nome);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('idade', 'idade', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_input($idade);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('genero', 'genero', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="masculino"><input type="radio" id="masculino" name="genero" value="M" <?php echo ($genero == "M") ? 'checked' : '' ?>>Masculino</label>
                                    </div>
                                    <div class="radio">
                                        <label for="feminino"><input type="radio" id="feminino" name="genero" value="F" <?php echo ($genero == "F") ? 'checked' : '' ?>>Feminino</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('civil', 'civil', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="solteiro"><input type="radio" id="solteiro" name="civil" value="Solteiro" <?php echo ($civil == "Solteiro") ? 'checked' : '' ?>>Solteiro</label>
                                    </div>
                                    <div class="radio">
                                        <label for="casado"><input type="radio" id="casado" name="civil" value="Casado" <?php echo ($civil == "Casado") ? 'checked' : '' ?>>Casado</label>
                                    </div>
                                    <div class="radio">
                                        <label for="uniao_estavel"><input type="radio" id="uniao_estavel" name="civil" value="União Estável" <?php echo ($civil == "União Estável") ? 'checked' : '' ?>>União Estável</label>
                                    </div>
                                    <div class="radio">
                                        <label for="divorciado"><input type="radio" id="divorciado" name="civil" value="Divorciado ou Separado" <?php echo ($civil == "Divorciado ou Separado") ? 'checked' : '' ?>>Divorciado ou Separado</label>
                                    </div>
                                    <div class="radio">
                                        <?php 
                                            $civil_outro = false;
                                            if($civil != "Divorciado ou Separado" && $civil != "União Estável" && $civil != "Casado" && $civil != "Solteiro") {
                                                $civil_outro = true;
                                            }
                                        ?>
                                        <label for="civil_outro"><input type="radio" id="civil_outro" name="civil" value="Outro" <?php echo ($civil_outro == true) ? 'checked' : '' ?>>Outro:</label>
                                        <input type="text" id="civil_outro_desc" name="civil_outro_desc" class="form-control" value="<?php echo ($civil_outro == true) ? $civil : '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('formacao', 'formacao', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="medio_incompleto"><input type="radio" id="medio_incompleto" name="formacao" value="Ensino Médio Incompleto" <?php echo ($formacao == "Ensino Médio Incompleto") ? 'checked' : '' ?>>Ensino Médio Incompleto</label>
                                    </div>
                                    <div class="radio">
                                        <label for="medio_completo"><input type="radio" id="medio_completo" name="formacao" value="Ensino Médio Completo" <?php echo ($formacao == "Ensino Médio Completo") ? 'checked' : '' ?>>Ensino Médio Completo</label>
                                    </div>
                                    <div class="radio">
                                        <label for="superior_incompleto"><input type="radio" id="superior_incompleto" name="formacao" value="Superior Incompleto" <?php echo ($formacao == "Superior Incompleto") ? 'checked' : '' ?>>Superior Incompleto</label>
                                    </div>
                                    <div class="radio">
                                        <label for="superior_completo"><input type="radio" id="superior_completo" name="formacao" value="Superior Completo" <?php echo ($formacao == "Superior Completo") ? 'checked' : '' ?>>Superior Completo</label>
                                    </div>
                                    <div class="radio">
                                        <label for="pos_graduando"><input type="radio" id="pos_graduando" name="formacao" value="Pós Graduando" <?php echo ($formacao == "Pós Graduando") ? 'checked' : '' ?>>Pós Graduando</label>
                                    </div>
                                    <div class="radio">
                                        <label for="pos_graduado"><input type="radio" id="pos_graduado" name="formacao" value="Pós Graduado" <?php echo ($formacao == "Pós Graduado") ? 'checked' : '' ?>>Pós Graduado</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('formado', 'formado', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-4">
                                    <?php echo form_input($formado);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('ocupacao', 'ocupacao', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <?php echo form_input($ocupacao);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('rendimento', 'rendimento', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="de_768_a_1625"><input type="radio" id="de_768_a_1625" name="rendimento" value="de R$768,00 a R$1625,00" <?php echo ($rendimento == "de R$768,00 a R$1625,00") ? 'checked' : '' ?>>de R$768,00 a R$1.625,00</label>
                                    </div>
                                    <div class="radio">
                                        <label for="de_1625_a_2705"><input type="radio" id="de_1625_a_2705" name="rendimento" value="de R$1625,01 a R$2705,00" <?php echo ($rendimento == "de R$1625,01 a R$2705,00") ? 'checked' : '' ?>>de R$1.625,01 a R$2.705,00</label>
                                    </div>
                                    <div class="radio">
                                        <label for="de_2705_a_4852"><input type="radio" id="de_2705_a_4852" name="rendimento" value="de R$2705,01 a R$4852,00" <?php echo ($rendimento == "de R$2705,01 a R$4852,00") ? 'checked' : '' ?>>de R$2.705,01 a R$4.852,00</label>
                                    </div>
                                    <div class="radio">
                                        <label for="de_4852_a_9254"><input type="radio" id="de_4852_a_9254" name="rendimento" value="de R$4852,01 a R$9254,00" <?php echo ($rendimento == "de R$4852,01 a R$9254,00") ? 'checked' : '' ?>>de R$4.852,01 a R$9.254,00</label>
                                    </div>
                                    <div class="radio">
                                        <label for="de_9254_a_20888"><input type="radio" id="de_9254_a_20888" name="rendimento" value="de R$9254,01 a R$20888,00" <?php echo ($rendimento == "de R$9254,01 a R$20888,00") ? 'checked' : '' ?>>de R$9.254,01 a R$20.888,00</label>
                                    </div>
                                    <div class="radio">
                                        <label for="acima_20888"><input type="radio" id="acima_20888" name="rendimento" value="acima de R$20.888,00" <?php echo ($rendimento == "acima de R$20.888,00") ? 'checked' : '' ?>>acima de R$20.888,00</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('telefone', 'telefone', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($telefone);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('skype', 'skype', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-4">
                                    <?php echo form_input($skype);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('celular', 'celular', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-2">
                                    <?php echo form_input($celular);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('pq_escolheu_curso', 'pq_escolheu_curso', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="falta_tempo"><input type="radio" id="falta_tempo" name="pq_escolheu_curso" value="Falta de tempo para frequentar um curso presencial" <?php echo ($pq_escolheu_curso == "Falta de tempo para frequentar um curso presencial") ? 'checked' : '' ?>>Falta de tempo para frequentar um curso presencial</label>
                                    </div>
                                    <div class="radio">
                                        <label for="adaptar_horario"><input type="radio" id="adaptar_horario" name="pq_escolheu_curso" value="Possibilidade de adaptar o horário de estudo conforme necessidade" <?php echo ($pq_escolheu_curso == "Possibilidade de adaptar o horário de estudo conforme necessidade") ? 'checked' : '' ?>>Possibilidade de adaptar o horário de estudo conforme necessidade</label>
                                    </div>
                                    <div class="radio">
                                        <label for="autonomia"><input type="radio" id="autonomia" name="pq_escolheu_curso" value="Autonomia para definir o ritmo e a forma de estudar" <?php echo ($pq_escolheu_curso == "Autonomia para definir o ritmo e a forma de estudar") ? 'checked' : '' ?>>Autonomia para definir o ritmo e a forma de estudar</label>
                                    </div>
                                    <div class="radio">
                                        <label for="outra"><input type="radio" id="outra" name="pq_escolheu_curso" value="Outra" <?php echo ($pq_escolheu_curso == "Outra") ? 'checked' : '' ?>>Outra</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('pq_escolheu_bsl', 'pq_escolheu_bsl', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="preco"><input type="radio" id="preco" name="pq_escolheu_bsl" value="Preço" <?php echo ($pq_escolheu_bsl == "Preço") ? 'checked' : '' ?>>Preço</label>
                                    </div>
                                    <div class="radio">
                                        <label for="diferenciais"><input type="radio" id="diferenciais" name="pq_escolheu_bsl" value="Diferenciais" <?php echo ($pq_escolheu_bsl == "Diferenciais") ? 'checked' : '' ?>>Diferenciais</label>
                                    </div>
                                    <div class="radio">
                                        <label for="atendimento"><input type="radio" id="atendimento" name="pq_escolheu_bsl" value="Atendimento do setor de vendas" <?php echo ($pq_escolheu_bsl == "Atendimento do setor de vendas") ? 'checked' : '' ?>>Atendimento do setor de vendas</label>
                                    </div>
                                    <div class="radio">
                                        <label for="individualizado"><input type="radio" id="individualizado" name="pq_escolheu_bsl" value="Curso EAD com atendimento individualizado" <?php echo ($pq_escolheu_bsl == "Curso EAD com atendimento individualizado") ? 'checked' : '' ?>>Curso EAD com atendimento individualizado</label>
                                    </div>
                                    <div class="radio">
                                        <?php 
                                            $pq_escolheu_bsl_outro = false;
                                            if($pq_escolheu_bsl != "Curso EAD com atendimento individualizado" && $pq_escolheu_bsl != "Atendimento do setor de vendas" && $pq_escolheu_bsl != "Diferenciais" && $pq_escolheu_bsl != "Preço") {
                                                $pq_escolheu_bsl_outro = true;
                                            }
                                        ?>
                                        <label for="pq_escolheu_bsl_outro"><input type="radio" id="pq_escolheu_bsl_outro" name="pq_escolheu_bsl" value="Outro" <?php echo ($pq_escolheu_bsl_outro == true) ? 'checked' : '' ?>>Outro:</label>
                                        <input type="text" id="pq_escolheu_bsl_outro_desc" name="pq_escolheu_bsl_outro_desc" class="form-control" value="<?php echo ($pq_escolheu_bsl_outro == true) ? $pq_escolheu_bsl : '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('ac_tutoria', 'ac_tutoria', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="ac_tutoria_sim"><input type="radio" id="ac_tutoria_sim" name="ac_tutoria" value="S" <?php echo ($ac_tutoria == "S") ? 'checked' : '' ?>>Sim</label>
                                    </div>
                                    <div class="radio">
                                        <label for="ac_tutoria_nao"><input type="radio" id="ac_tutoria_nao" name="ac_tutoria" value="N" <?php echo ($ac_tutoria == "N") ? 'checked' : '' ?>>Não</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('espera_tutoria', 'espera_tutoria', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="contato_diario"><input type="radio" id="contato_diario" name="espera_tutoria" value="Contato diário, dando suporte às suas necessidades" <?php echo ($espera_tutoria == "Contato diário, dando suporte às suas necessidades") ? 'checked' : '' ?>>Contato diário, dando suporte às suas necessidades</label>
                                    </div>
                                    <div class="radio">
                                        <label for="contato_semanal"><input type="radio" id="contato_semanal" name="espera_tutoria" value="Contato semanal, dando suporte às suas necessidades" <?php echo ($espera_tutoria == "Contato semanal, dando suporte às suas necessidades") ? 'checked' : '' ?>>Contato semanal, dando suporte às suas necessidades</label>
                                    </div>
                                    <div class="radio">
                                        <label for="contato_quinzenal"><input type="radio" id="contato_quinzenal" name="espera_tutoria" value="Contato quinzenal, dando suporte às suas necessidades" <?php echo ($espera_tutoria == "Contato quinzenal, dando suporte às suas necessidades") ? 'checked' : '' ?>>Contato quinzenal, dando suporte às suas necessidades</label>
                                    </div>
                                    <div class="radio">
                                        <label for="sem_contato"><input type="radio" id="sem_contato" name="espera_tutoria" value="Não deseja contato" <?php echo ($espera_tutoria == "Não deseja contato") ? 'checked' : '' ?>>Não deseja contato</label>
                                    </div>
                                    <div class="radio">
                                        <?php 
                                            $espera_tutoria_outro = false;
                                            if($espera_tutoria != "Não deseja contato" && $espera_tutoria != "Contato quinzenal, dando suporte às suas necessidades" && $espera_tutoria != "Contato semanal, dando suporte às suas necessidades" && $espera_tutoria != "Contato diário, dando suporte às suas necessidades") {
                                                $espera_tutoria_outro = true;
                                            }
                                        ?>
                                        <label for="espera_tutoria_outro"><input type="radio" id="espera_tutoria_outro" name="espera_tutoria" value="Outro" <?php echo ($espera_tutoria_outro == true) ? 'checked' : '' ?>>Outro:</label>
                                        <input type="text" id="espera_tutoria_outro_desc" name="espera_tutoria_outro_desc" class="form-control" value="<?php echo ($espera_tutoria_outro == true) ? $espera_tutoria : '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('opcao_contato', 'opcao_contato', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="whatsapp"><input type="radio" id="whatsapp" name="opcao_contato" value="Whatsapp" <?php echo ($opcao_contato == "Whatsapp") ? 'checked' : '' ?>>Whatsapp</label>
                                    </div>
                                    <div class="radio">
                                        <label for="ligacao_telefonica"><input type="radio" id="ligacao_telefonica" name="opcao_contato" value="Ligação Telefonica" <?php echo ($opcao_contato == "Ligação Telefonica") ? 'checked' : '' ?>>Ligação Telefonica</label>
                                    </div>
                                    <div class="radio">
                                        <label for="email"><input type="radio" id="email" name="opcao_contato" value="E-mail" <?php echo ($opcao_contato == "E-mail") ? 'checked' : '' ?>>E-mail</label>
                                    </div>
                                    <div class="radio">
                                        <label for="skype"><input type="radio" id="skype" name="opcao_contato" value="Skype" <?php echo ($opcao_contato == "Skype") ? 'checked' : '' ?>>Skype</label>
                                    </div>
                                    <div class="radio">
                                        <?php 
                                            $opcao_contato_outro = false;
                                            if($opcao_contato != "Skype" && $opcao_contato != "E-mail" && $opcao_contato != "Ligação Telefonica" && $opcao_contato != "Whatsapp") {
                                                $opcao_contato_outro = true;
                                            }
                                        ?>
                                        <label for="opcao_contato_outro"><input type="radio" id="opcao_contato_outro" name="opcao_contato" value="Outro" <?php echo ($opcao_contato_outro == true) ? 'checked' : '' ?>>Outro:</label>
                                        <input type="text" id="opcao_contato_outro_desc" name="opcao_contato_outro_desc" class="form-control" value="<?php echo ($opcao_contato_outro == true) ? $opcao_contato : '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('periodo_contato', 'periodo_contato', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="manha"><input type="radio" id="manha" name="periodo_contato" value="Manhã" <?php echo ($periodo_contato == "Manhã") ? 'checked' : '' ?>>Manhã</label>
                                    </div>
                                    <div class="radio">
                                        <label for="tarde"><input type="radio" id="tarde" name="periodo_contato" value="Tarde" <?php echo ($periodo_contato == "Tarde") ? 'checked' : '' ?>>Tarde</label>
                                    </div>
                                    <div class="radio">
                                        <label for="noite"><input type="radio" id="noite" name="periodo_contato" value="Noite" <?php echo ($periodo_contato == "Noite") ? 'checked' : '' ?>>Noite</label>
                                    </div>
                                    <div class="radio">
                                        <?php 
                                            $periodo_contato_outro = false;
                                            if($periodo_contato != "Noite" && $periodo_contato != "Tarde" && $periodo_contato != "Manhã") {
                                                $periodo_contato_outro = true;
                                            }
                                        ?>
                                        <label for="periodo_contato_outro"><input type="radio" id="periodo_contato_outro" name="periodo_contato" value="Outro" <?php echo ($periodo_contato_outro == true) ? 'checked' : '' ?>>Outro:</label>
                                        <input type="text" id="periodo_contato_outro_desc" name="periodo_contato_outro_desc" class="form-control" value="<?php echo ($periodo_contato_outro == true) ? $periodo_contato : '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('contato_sabado', 'contato_sabado', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="contato_sabado_sim"><input type="radio" id="contato_sabado_sim" name="contato_sabado" value="S" <?php echo ($contato_sabado == "S") ? 'checked' : '' ?>>Sim</label>
                                    </div>
                                    <div class="radio">
                                        <label for="contato_sabado_nao"><input type="radio" id="contato_sabado_nao" name="contato_sabado" value="N" <?php echo ($contato_sabado == "N") ? 'checked' : '' ?>>Não</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('horario_acesso', 'horario_acesso', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="antes_8"><input type="radio" id="antes_8" name="horario_acesso" value="antes das 08:00" <?php echo ($horario_acesso == "antes das 08:00") ? 'checked' : '' ?>>antes das 08:00</label>
                                    </div>
                                    <div class="radio">
                                        <label for="horario_comercial"><input type="radio" id="horario_comercial" name="horario_acesso" value="horário comercial" <?php echo ($horario_acesso == "horário comercial") ? 'checked' : '' ?>>horário comercial</label>
                                    </div>
                                    <div class="radio">
                                        <label for="entre_18_e_20"><input type="radio" id="entre_18_e_20" name="horario_acesso" value="entre 18:00 e 20:00" <?php echo ($horario_acesso == "entre 18:00 e 20:00") ? 'checked' : '' ?>>entre 18:00 e 20:00</label>
                                    </div>
                                    <div class="radio">
                                        <label for="entre_20_e_23"><input type="radio" id="entre_20_e_23" name="horario_acesso" value="entre 20:00 e 23:00" <?php echo ($horario_acesso == "entre 20:00 e 23:00") ? 'checked' : '' ?>>entre 20:00 e 23:00</label>
                                    </div>
                                    <div class="radio">
                                        <label for="apos_23"><input type="radio" id="apos_23" name="horario_acesso" value="após as 23:00 e madrugada" <?php echo ($horario_acesso == "após as 23:00 e madrugada") ? 'checked' : '' ?>>após as 23:00 e madrugada</label>
                                    </div>
                                    <div class="radio">
                                        <?php 
                                            $horario_acesso_outro = false;
                                            if($horario_acesso != "após as 23:00 e madrugada" && $horario_acesso != "entre 20:00 e 23:00" && $horario_acesso != "entre 18:00 e 20:00" && $horario_acesso != "horário comercial" && $horario_acesso != "antes das 08:00") {
                                                $horario_acesso_outro = true;
                                            }
                                        ?>
                                        <label for="horario_acesso_outro"><input type="radio" id="horario_acesso_outro" name="horario_acesso" value="Outro" <?php echo ($horario_acesso_outro == true) ? 'checked' : '' ?>>Outro:</label>
                                        <input type="text" id="horario_acesso_outro_desc" name="horario_acesso_outro_desc" class="form-control" value="<?php echo ($horario_acesso_outro == true) ? $horario_acesso : '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('ja_cursou_ead', 'ja_cursou_ead', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="ja_cursou_ead_sim"><input type="radio" id="ja_cursou_ead_sim" name="ja_cursou_ead" value="S" <?php echo ($ja_cursou_ead == "S") ? 'checked' : '' ?>>Sim</label>
                                    </div>
                                    <div class="radio">
                                        <label for="ja_cursou_ead_nao"><input type="radio" id="ja_cursou_ead_nao" name="ja_cursou_ead" value="N" <?php echo ($ja_cursou_ead == "N") ? 'checked' : '' ?>>Não</label>
                                    </div>
                                </div>
                            </div>                            
                            <div class="form-group">
                                <?php echo lang('ja_cursou_ead_txt', 'ja_cursou_ead_txt', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-4">
                                    <?php echo form_textarea($ja_cursou_ead_txt);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('concluiu_ead', 'concluiu_ead', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="concluiu_ead_sim"><input type="radio" id="concluiu_ead_sim" name="concluiu_ead" value="S" <?php echo ($concluiu_ead == "S") ? 'checked' : '' ?>>Sim</label>
                                    </div>
                                    <div class="radio">
                                        <label for="concluiu_ead_nao"><input type="radio" id="concluiu_ead_nao" name="concluiu_ead" value="N" <?php echo ($concluiu_ead == "N") ? 'checked' : '' ?>>Não</label>
                                    </div>
                                </div>
                            </div>                                 
                            <div class="form-group">
                                <?php echo lang('concluiu_ead_txt', 'concluiu_ead_txt', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-4">
                                    <?php echo form_textarea($concluiu_ead_txt);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('ja_fez_ingles', 'ja_fez_ingles', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="ja_fez_ingles_sim"><input type="radio" id="ja_fez_ingles_sim" name="ja_fez_ingles" value="S" <?php echo ($ja_fez_ingles == "S") ? 'checked' : '' ?>>Sim</label>
                                    </div>
                                    <div class="radio">
                                        <label for="ja_fez_ingles_nao"><input type="radio" id="ja_fez_ingles_nao" name="ja_fez_ingles" value="N" <?php echo ($ja_fez_ingles == "N") ? 'checked' : '' ?>>Não</label>
                                    </div>
                                </div>
                            </div> 
                            <div class="form-group">
                                <?php echo lang('nivel_ingles', 'nivel_ingles', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="basico"><input type="radio" id="basico" name="nivel_ingles" value="básico" <?php echo ($nivel_ingles == "básico") ? 'checked' : '' ?>>básico</label>
                                    </div>
                                    <div class="radio">
                                        <label for="intermediario"><input type="radio" id="intermediario" name="nivel_ingles" value="intermediário" <?php echo ($nivel_ingles == "intermediário") ? 'checked' : '' ?>>intermediário</label>
                                    </div>
                                    <div class="radio">
                                        <label for="avancado"><input type="radio" id="avancado" name="nivel_ingles" value="avançado" <?php echo ($nivel_ingles == "avançado") ? 'checked' : '' ?>>avançado</label>
                                    </div>
                                    <div class="radio">
                                        <?php 
                                            $nivel_ingles_outro = false;
                                            if($nivel_ingles != "avançado" && $nivel_ingles != "intermediário" && $nivel_ingles != "básico") {
                                                $nivel_ingles_outro = true;
                                            }
                                        ?>
                                        <label for="nivel_ingles_outro"><input type="radio" id="nivel_ingles_outro" name="nivel_ingles" value="Outro" <?php echo ($nivel_ingles_outro == true) ? 'checked' : '' ?>>Outro:</label>
                                        <input type="text" id="nivel_ingles_outro_desc" name="nivel_ingles_outro_desc" class="form-control" value="<?php echo ($nivel_ingles_outro == true) ? $nivel_ingles : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('concluiu_ingles', 'concluiu_ingles', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="concluiu_ingles_sim"><input type="radio" id="concluiu_ingles_sim" name="concluiu_ingles" value="S" <?php echo ($concluiu_ingles == "S") ? 'checked' : '' ?>>Sim</label>
                                    </div>
                                    <div class="radio">
                                        <label for="concluiu_ingles_nao"><input type="radio" id="concluiu_ingles_nao" name="concluiu_ingles" value="N" <?php echo ($concluiu_ingles == "N") ? 'checked' : '' ?>>Não</label>
                                    </div>
                                </div>
                            </div>                             
                            <div class="form-group">
                                <?php echo lang('concluiu_ingles_txt', 'concluiu_ingles_txt', array('class' => 'col-sm-2 control-label'), false); ?>
                                <div class="col-sm-4">
                                    <?php echo form_textarea($concluiu_ingles_txt);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('pq_influencia', 'pq_influencia', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="trabalho_exige"><input type="radio" id="trabalho_exige" name="pq_influencia" value="Meu trabalho exige" <?php echo ($pq_influencia == "Meu trabalho exige") ? 'checked' : '' ?>>Meu trabalho exige</label>
                                    </div>
                                    <div class="radio">
                                        <label for="viajar_exterior"><input type="radio" id="viajar_exterior" name="pq_influencia" value="Quero viajar para o exterior" <?php echo ($pq_influencia == "Quero viajar para o exterior") ? 'checked' : '' ?>>Quero viajar para o exterior</label>
                                    </div>
                                    <div class="radio">
                                        <label for="conversar_pessoas"><input type="radio" id="conversar_pessoas" name="pq_influencia" value="Quero conversar com as pessoas" <?php echo ($pq_influencia == "Quero conversar com as pessoas") ? 'checked' : '' ?>>Quero conversar com as pessoas</label>
                                    </div>
                                    <div class="radio">
                                        <label for="entender_musica"><input type="radio" id="entender_musica" name="pq_influencia" value="Quero entender uma música, um filme, um vídeo em inglês" <?php echo ($pq_influencia == "Quero entender uma música, um filme, um vídeo em inglês") ? 'checked' : '' ?>>Quero entender uma música, um filme, um vídeo em inglês</label>
                                    </div>
                                    <div class="radio">
                                        <?php 
                                            $pq_influencia_outro = false;
                                            if($pq_influencia != "Meu trabalho exige" && $pq_influencia != "Quero viajar para o exterior" && $pq_influencia != "Quero conversar com as pessoas" && $pq_influencia != "Quero entender uma música, um filme, um vídeo em inglês") {
                                                $pq_influencia_outro = true;
                                            }
                                        ?>
                                        <label for="pq_influencia_outro"><input type="radio" id="pq_influencia_outro" name="pq_influencia" value="Outro" <?php echo ($pq_influencia_outro == true) ? 'checked' : '' ?>>Outro:</label>
                                        <input type="text" id="pq_influencia_outro_desc" name="pq_influencia_outro_desc" class="form-control" value="<?php echo ($pq_influencia_outro == true) ? $pq_influencia : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('meta', 'meta', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-4">
                                    <div class="radio">
                                        <label for="superar_timidez"><input type="radio" id="superar_timidez" name="meta" value="Superar minha timidez e começar a falar em grupo" <?php echo ($meta == "Superar minha timidez e começar a falar em grupo") ? 'checked' : '' ?>>Superar minha timidez e começar a falar em grupo</label>
                                    </div>
                                    <div class="radio">
                                        <label for="entender_pessoas"><input type="radio" id="entender_pessoas" name="meta" value="Entender o que as pessoas falam, músicas, áudios, vídeos e fimes" <?php echo ($meta == "Entender o que as pessoas falam, músicas, áudios, vídeos e fimes") ? 'checked' : '' ?>>Entender o que as pessoas falam, músicas, áudios, vídeos e fimes</label>
                                    </div>
                                    <div class="radio">
                                        <label for="conseguir_concluir"><input type="radio" id="conseguir_concluir" name="meta" value="Conseguir concluir a etapa, pois preciso atingir a fluência" <?php echo ($meta == "Conseguir concluir a etapa, pois preciso atingir a fluência") ? 'checked' : '' ?>>Conseguir concluir a etapa, pois preciso atingir a fluência</label>
                                    </div>
                                    <div class="radio">
                                        <?php 
                                            $meta_outro = false;
                                            if($meta != "Superar minha timidez e começar a falar em grupo" && $meta != "Entender o que as pessoas falam, músicas, áudios, vídeos e fimes" && $meta != "Conseguir concluir a etapa, pois preciso atingir a fluência") {
                                                $meta_outro = true;
                                            }
                                        ?>
                                        <label for="meta_outro"><input type="radio" id="meta_outro" name="meta" value="Outro" <?php echo ($meta_outro == true) ? 'checked' : '' ?>>Outro:</label>
                                        <input type="text" id="meta_outro_desc" name="meta_outro_desc" class="form-control" value="<?php echo ($meta_outro == true) ? $meta : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('meta_porcentagem', 'meta_porcentagem', array('class' => 'col-sm-2 control-label'), true); ?>
                                <div class="col-sm-1">
                                    <?php echo form_dropdown($meta_porcentagem);?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <div class="btn-group">
                                        <?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
                                        <?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
                                        <?php echo anchor('pesquisa_perfil', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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