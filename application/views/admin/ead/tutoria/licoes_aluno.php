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
                    <div class="box-body">
                        <table id="datatable_no_paging" class="table">
                            <thead>
                                <tr>
                                    <th><?php echo lang('titulo');?></th>
                                    <th><?php echo lang('inicio');?></th>
                                    <th><?php echo lang('termino');?></th>
                                    <th><?php echo lang('listening_score');?></th>
                                    <th><?php echo lang('speaking_score');?></th>
                                    <th><?php echo lang('grammar_score');?></th>
                                    <th><?php echo lang('reading_score');?></th>
                                    <th><?php echo lang('writing_score');?></th>
                                    <th><?php echo lang('vocabulary_score');?></th>
                                    <th><b><?php echo lang('total_score');?></b></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($licoes as $licao):?>
                                <tr>
                                    <td><?php echo $licao["titulo"] ?></td>
                                    <td data-order="<?php echo strtotime($licao["inicio_td"]) ?>"><?php echo $licao["inicio"] ?></td>
                                    <td data-order="<?php echo strtotime($licao["termino_td"]) ?>"><?php echo $licao["termino"] ?> </td>
                                    <td align="right"><?php echo ($licao["listening_score"])?$licao["listening_score"]:"-" ?></td>                                    
                                    <td align="right"><?php echo ($licao["speaking_score"])?$licao["speaking_score"]:"-" ?></td>                                    
                                    <td align="right"><?php echo ($licao["grammar_score"])?$licao["grammar_score"]:"-" ?></td>                                    
                                    <td align="right"><?php echo ($licao["reading_score"])?$licao["reading_score"]:"-" ?></td>                                    
                                    <td align="right"><?php echo ($licao["writing_score"])?$licao["writing_score"]:"-" ?></td>                                    
                                    <td align="right"><?php echo ($licao["vocabulary_score"])?$licao["vocabulary_score"]:"-" ?></td>                                    
                                    <td align="right"><b><?php echo ($licao["total_score"])?$licao["total_score"]:"-" ?></b></td>                                    
                                </tr>
<?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>