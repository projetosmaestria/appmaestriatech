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
                        <h3 class="box-title"><?=$unidade->unidade?></h3>
                    </div>
                    <div class="box-body">
                        <form action="../cursoSalvar/<?=$unidade->id?>" method="post">
                            <div class="input-group">
                                <input type="text" class="form-control" name="curso" list="cursos" placeholder="Curso">
                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-primary">Adicionar</button>
                                </div>
                                <datalist id="cursos">
                                    <?php foreach($cursos as $curso){ ?>
                                        <option value="<?=$curso->descricao?>">
                                    <?php } ?>
                                </datalist>
                            </div>
                        </form>

                        <ul class="list-group" style="margin-top:2em">
                            <?php foreach($cursos_filial as $curso){ ?>
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-sm-11">
                                        <?=$curso->descricao?>
                                    </div>
                                    <div class="col-sm-1 text-right">
                                        <a href="../cursoDeletar/<?=$curso->id_filial?>/<?=$curso->id_curso?>" onclick="return RemoveCurso()" class="btn btn-sm btn-danger">X</a>
                                    </div>
                                </div>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
             </div>
        </div>
    </section>
</div>

<script>
    function RemoveCurso(){
        Swal.fire({
            title: 'Tem certeza que deseja remover este curso desta unidae?',
            icon: 'info',
            showCancelButton: true,
            focusConfirm: true,
            confirmButtonText: 'Sim',
            cancelButtonText: 'Não',
        }).then((result) => {
            if (result.isConfirmed) {
                return true
            } else {
                return false
            }
        })
    }
</script>