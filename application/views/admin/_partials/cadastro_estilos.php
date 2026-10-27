<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Estilos do padrão de formulário: cards numerados (.step-number) e coluna lateral fixa com resumo.
?>
<style type="text/css">
    .cadastro-form .box { border-top-width: 3px; }
    .cadastro-form .box-header .box-title { font-size: 16px; }
    .cadastro-form .step-number { display: inline-block; width: 24px; height: 24px; line-height: 24px; margin-right: 6px; border-radius: 50%; background: #3c8dbc; color: #fff; text-align: center; font-size: 13px; font-weight: 600; }
    .cadastro-form .box-header .box-subtitle { display: block; margin: 4px 0 0 30px; color: #777; font-size: 12px; }
    .cadastro-form .form-group label { font-weight: 600; }
    .cadastro-form .help-block { margin-bottom: 0; font-size: 12px; }
    .cadastro-form .fstElement { width: 100%; }
    .cadastro-opcao { display: flex; align-items: flex-start; gap: 8px; margin: 0; padding: 10px 12px; background: #f7f9fb; border: 1px solid #e3e8ee; border-radius: 4px; font-weight: 400 !important; cursor: pointer; }
    .cadastro-opcao input { margin-top: 3px; }
    .cadastro-segmentos .btn.active { background: #eaf2f8; border-color: #3c8dbc; box-shadow: none; }
    .cadastro-heading { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 15px; }
    .cadastro-heading-id { font-size: 18px; font-weight: 700; color: #777; }
    .cadastro-heading-nome { font-size: 18px; font-weight: 600; }
    .cadastro-heading-meta { color: #777; font-size: 12px; }
    .cadastro-resumo dl { margin-bottom: 10px; }
    .cadastro-resumo dt { color: #777; font-weight: 400; font-size: 12px; }
    .cadastro-resumo dd { margin-bottom: 8px; font-weight: 600; word-break: break-word; }
    .cadastro-resumo dd.is-empty { color: #bbb; font-weight: 400; font-style: italic; }
    .cadastro-dica { margin: 0; padding: 10px; background: #f7f9fb; border-radius: 4px; font-size: 12px; color: #666; }
    @media (min-width: 992px) {
        .cadastro-form .sticky-column { position: sticky; top: 60px; }
    }
</style>
