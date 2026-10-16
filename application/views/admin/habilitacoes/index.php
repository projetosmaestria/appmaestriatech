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
                                    <th><?php echo lang('fantasia');?></th>
                                    <th><?php echo lang('razao_social');?></th>
                                    <th><?php echo lang('unidades_administrador');?></th>
                                    <th><?php echo lang('unidades_telefone');?></th>
                                    <th><?php echo lang('unidades_cpf_cnpj');?></th>
                                    <th><?php echo lang('status');?></th>
                                    <th><?php echo lang('action');?></th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($clientes as $values):?>
                                <tr>
                                    <td><?php echo htmlspecialchars($values->unidade, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->razao_social, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->gestor, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($values->telefone1, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ($values->cnpj != "") ? $values->cnpj : $values->cpf; ?></td>
                                    <td><?php echo ($values->actived == 's') ? '<span class="label label-success">'.lang('status_ativo').'</span>' : '<span class="label label-default">'.lang('status_inativo').'</span>'; ?></td>
                                    <td>
                                        <div class="btn-group btn-orders-all"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#" title="Habilitar Produtos" onclick="carregarProdutos('<?php echo $values->id ?>', '<?php echo base_url() ?>');"><i class="fa fa-th"></i>Habilitar Produtos</a></li>

                                                <li><a href="<?php echo base_url('habilitacoes/historico/'.$values->id) ?>" title="Visualizar Produtos Habilitados"><i class="fa fa-search-plus"></i>Visualizar Produtos Habilitados</a></li>
                                            </ul>
                                        </div>


                                    </td>
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

<!-- MODAL HABILITAÇÕES -->
<div id="modalHabilitacaoProdutosCliente" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Fechar</span></button>
                <h4 class="modal-title-chamado" id="title-chamado"> Produtos para Habilitação</h4>
            </div>
            <div class="modal-body">
                <div class="panel panel-default">
                    <div class="alert-info" style="padding:10px;">
                        <div class="row">
                            <div class="col-sm-4">Produtos Disponíveis</div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row head-table-changelog">
                            <div class="col-sm-2"><i class="fa fa-database"></i> ID</div>
                            <div class="col-sm-4"><i class="glyphicon glyphicon-circle-arrow-down"></i> Produto</div>
                            <div class="col-sm-3"><i class="fa fa-chain"></i> Aplicação</div>
                            <div class="col-sm-3"><i class="glyphicon glyphicon-barcode"></i> Última Versão</div>
                        </div>
                        <div class="form-horizontal" id="content-produtos"></div>
                    </div>
                </div>
                <div class="hidden-sm clear-10"></div>
                <div class="panel panel-default">
                    <div class="alert-success" style="padding:10px;">
                        <div class="row">
                            <div class="col-sm-4">Produtos Habilitados</div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row head-table-changelog">
                            <div class="col-sm-2"><i class="fa fa-database"></i> ID</div>
                            <div class="col-sm-4"><i class="glyphicon glyphicon-circle-arrow-down"></i> Produto</div>
                            <div class="col-sm-2"><i class="fa fa-chain"></i> Aplicação</div>
                            <div class="col-sm-2 text-center"><i class="glyphicon glyphicon-circle-arrow-down"></i> Demonstração</div>
                            <div class="col-sm-2 text-center"><i class="glyphicon glyphicon-calendar"></i> Vencimento</div>
                        </div>
                        <div class="form-horizontal" id="content-produtos-habilitados"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="habilitacao-msgs"><small> Status: </small></div>
                    </div>
                    <div class="col-md-4">
                        <div class="habilitacao-msgs"><small> Vencimentos em menos de: </small></div>
                    </div>
                </div>
                <div class="separador-vencimento"></div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="habilitacao-desabilitado"></div>
                        <div class="habilitacao-msgs">Desabilitado</div>
                    </div>
                    <div class="col-md-2">
                        <div class="vencimento-15"></div>
                        <div class="habilitacao-msgs">15 Dias</div>
                    </div>                        
                    <div class="col-md-2">
                        <div class="vencimento-30"></div>
                        <div class="habilitacao-msgs">30 Dias</div>
                    </div>
                    <div class="col-md-2">
                        <div class="vencimento-60"></div>
                        <div class="habilitacao-msgs">60 Dias</div>
                    </div>
                </div>
                <div class="overlay" style="display:none;">
                    <i class="fa fa-refresh fa-spin"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>
            </div>
        </div>
    </div>
</div> <!-- END MODAL HABILITAÇÕES -->


<div class="modal fade" id="confirm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-desativar-habilitacao" method="post" action="<?php echo base_url('habilitacoes/desabilitar_habilitacao') ?>" class="form-horizontal">
                <input type="hidden" id="inputHabilitacaoId" name="inputHabilitacaoId" value="">
                <input type="hidden" id="inputStatus" name="inputStatus" value="">

                <div class="modal-header">
                  <h3>Tem certeza que deseja desabilitar/reabilitar este produto para o cliente?</h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Justificativa</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" required id="inputHabilitacaoJustificativa" name="inputHabilitacaoJustificativa" rows="6" cols="68"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="desabilitar" class="btn btn-danger btn-ok" data-dismiss="modal">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>