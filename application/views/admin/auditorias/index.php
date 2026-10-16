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
                        <table id="datatable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="col-md-2"><?php echo lang('usuario');?></th>
                                    <th class="col-md-2"><?php echo lang('controlador');?></th>
                                    <th class="col-md-2"><?php echo lang('metodo');?></th>
                                    <th class="col-md-2"><?php echo lang('data');?></th>
                                    <th class="col-md-4"><?php echo lang('obs');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($auditorias as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->first_name." ".$values->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->controlador, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->metodo, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td data-order="<?php echo strtotime($values->created_at);?>"><?php echo htmlspecialchars(date("d/m/Y H:i:s", strtotime($values->created_at)), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->obs, ENT_QUOTES, 'UTF-8'); ?></td>
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
