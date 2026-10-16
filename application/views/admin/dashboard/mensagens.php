<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<section class="col-lg-12 connectedSortable">
    <!-- TO DO List -->
    <div class="box box-primary">
        <div class="box-header">
          <i class="fa fa-comments-o"></i>

          <h3 class="box-title">Mensagens não lidas</h3>

        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- See dist/js/pages/dashboard.js to activate the todoList plugin -->
          <?php if($contatos->num_rows() > 0) { ?>
          <ul class="products-list product-list-in-box">
            <?php foreach($contatos->result() as $contato) { ?>
                <?php 
                    $date_time = new DateTime( date("Y-m-d H:i:s") );
                    $diff = $date_time->diff( new DateTime( $contato->created_at ) );

                    $time_to_show = "";

                    if($diff->format('%y') > 0)
                        $time_to_show = $diff->format('%y ano(s)');
                    elseif($diff->format('%m') > 0)
                        $time_to_show = $diff->format('%m mês(es)');
                    elseif($diff->format('%d') > 0)
                        $time_to_show = $diff->format('%d dia(s)');
                    elseif($diff->format('%H') > 0)
                        $time_to_show = $diff->format('%H hora(s)');
                    elseif($diff->format('%i') > 0)
                        $time_to_show = $diff->format('%i minuto(s)');
                    elseif($diff->format('%s') > 0)
                        $time_to_show = $diff->format('%s segundo(s)');
                ?>
                <li class="item">
                    <div class="product-info" style="margin-left: 0px !important;">
                        <?php echo $contato->first_name." ".$contato->last_name ?>
                        <small class="text-muted pull-right"><i class="fa fa-clock-o"></i> <?php echo $time_to_show ?></small>
                        <span class="product-description"><?php echo $contato->assunto ?></span>
                        <span class="product-description"><a href="<?php echo base_url('contatos_interno/ver/'.$contato->id) ?>" class="product-title">Ver mais...</a></span>
                    </a>
                </li>
            <?php } ?>
          </ul>

          <?php } else { ?>
            <p>Não há mensagens a serem lidas.</p>
          <?php } ?>
        </div>
        <!-- /.box-body -->
        <div class="box-footer clearfix no-border text-center">
          <p><a class="uppercase" href="<?php echo base_url('contatos_interno') ?>">Ver todos</a></p>
        </div>
    </div>
    <!-- /.box -->
</section>