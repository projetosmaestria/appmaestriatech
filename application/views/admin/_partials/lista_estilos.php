<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Estilos do padrão de listagem: cards de indicadores, barra de filtros e tabela zebrada com divisória.
?>
<style type="text/css">
    .lista-kpi { display: block; background: #fff; border-radius: 6px; box-shadow: 0 1px 2px rgba(0, 0, 0, .1); padding: 14px 16px; margin-bottom: 15px; color: #333; transition: box-shadow .15s; }
    a.lista-kpi:hover { color: #333; box-shadow: 0 3px 10px rgba(0, 0, 0, .15); }
    .lista-kpi span { display: block; color: #777; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; }
    .lista-kpi strong { display: block; font-size: 26px; line-height: 1.2; }
    .lista-kpi small { color: #999; }
    .lista-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .lista-busca { flex: 1 1 260px; max-width: 420px; }
    .lista-filtro { width: auto; min-width: 160px; max-width: 240px; }
    .lista-tabela { border-collapse: separate; border-spacing: 0; margin: 0 !important; }
    .lista-tabela > thead > tr > th { border-bottom: 2px solid #d2d6de; background: #f4f6f8; }
    .lista-tabela > tbody > tr > td { vertical-align: middle; padding-top: 10px; padding-bottom: 10px; border-top: 0; border-bottom: 1px solid #d2d6de; }
    .lista-tabela > tbody > tr:nth-child(even) > td { background-color: #f7f9fb; }
    .lista-tabela > tbody > tr:hover > td { background-color: #eaf2f8; }
    .lista-tabela > tbody > tr.is-inativo > td { color: #999; }
    .lista-tabela > tbody > tr > td:first-child { border-left: 3px solid transparent; }
    .lista-tabela > tbody > tr.is-ativo > td:first-child { border-left-color: #00a65a; }
    .lista-tabela > tbody > tr.is-inativo > td:first-child { border-left-color: #d2d6de; }
    .lista-nome { font-weight: 600; font-size: 14px; }
    .lista-detalhe { font-size: 12px; color: #777; }
    .lista-chip { display: inline-block; margin: 2px 4px 2px 0; padding: 2px 8px; border-radius: 10px; background: #eef2f6; border: 1px solid #dde3ea; color: #444; font-size: 12px; white-space: nowrap; }
    .lista-acoes { white-space: nowrap; text-align: right; }
    .lista-acoes .btn { float: none; }
    .lista-vazio { padding: 24px; text-align: center; color: #999; }
    .lista-page .dataTables_info, .lista-page .dataTables_paginate { padding: 8px 12px; }
    .lista-page .dataTables_processing { top: 60px; }
</style>
