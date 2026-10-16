<?php
$dir = dirname(__DIR__) . '/assets/images/treinamentos';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// Funcao auxiliar para gerar imagens SVG estilizadas
function generateSvg($title, $subtitle, $color1, $color2, $icon, $filename) {
    global $dir;
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 450" width="100%" height="100%">
  <defs>
    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$color1};stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$color2};stop-opacity:1" />
    </linearGradient>
    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
      <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255, 255, 255, 0.07)" stroke-width="1"/>
    </pattern>
  </defs>
  <rect width="800" height="450" fill="url(#grad)" />
  <rect width="800" height="450" fill="url(#grid)" />
  <circle cx="700" cy="80" r="140" fill="rgba(255,255,255,0.05)" />
  <circle cx="100" cy="380" r="180" fill="rgba(255,255,255,0.03)" />
  
  <g transform="translate(60, 160)">
    <rect x="0" y="-80" width="60" height="60" rx="14" fill="rgba(255,255,255,0.18)" />
    <text x="30" y="-40" font-family="'Segoe UI', Roboto, sans-serif" font-size="28" font-weight="bold" fill="#ffffff" text-anchor="middle">{$icon}</text>
    
    <text x="0" y="20" font-family="'Segoe UI', Roboto, sans-serif" font-size="32" font-weight="800" fill="#ffffff" letter-spacing="-0.5">{$title}</text>
    <text x="0" y="58" font-family="'Segoe UI', Roboto, sans-serif" font-size="17" font-weight="400" fill="rgba(255,255,255,0.85)">{$subtitle}</text>
    
    <rect x="0" y="90" width="140" height="34" rx="17" fill="rgba(255,255,255,0.22)" />
    <text x="70" y="112" font-family="'Segoe UI', Roboto, sans-serif" font-size="13" font-weight="700" fill="#ffffff" text-anchor="middle">MAESTRIA TECH</text>
  </g>
</svg>
SVG;
    file_put_contents($dir . '/' . $filename, $svg);
    echo "Gerado: {$filename}\n";
}

generateSvg("Maestria Academy", "Plataforma Oficial de Treinamento e Capacitacao", "#1e3c72", "#2a5298", "M", "banner_academy_1.svg");
generateSvg("Módulo Financeiro", "Formas de Pagamento, Titulos e Fluxo de Caixa", "#0f2027", "#203a43", "R$", "banner_academy_2.svg");
generateSvg("Faturamento & Vendas", "Emissao de Notas, Fechamento de Lotes e Cobrancas", "#134e5e", "#71b280", "FAT", "banner_academy_3.svg");

generateSvg("Formas de Pagamento", "Configuracoes completas e taxas operacionais", "#1a2a6c", "#b21f1f", "PGT", "curso_financeiro_1.svg");
generateSvg("Titulos a Pagar e Receber", "Controle de faturas, baixas e conciliacoes", "#2c3e50", "#3498db", "REC", "curso_financeiro_2.svg");
generateSvg("Emissao de Faturamento", "Fluxo de emissao e controle de duplicatas", "#004e92", "#000428", "NFE", "curso_faturamento.svg");
generateSvg("Gestao de Cadastros", "Cadastros unificados de clientes e fornecedores", "#11998e", "#38ef7d", "CAD", "curso_cadastros.svg");

// Atualizar caminhos no banco de dados
$m = new mysqli('localhost', 'xkey', 'Alfa7410147', 'rodney');
$m->query("UPDATE treinamento_banners SET imagem = 'assets/images/treinamentos/banner_academy_1.svg' WHERE id = 1");
$m->query("UPDATE treinamento_banners SET imagem = 'assets/images/treinamentos/banner_academy_2.svg' WHERE id = 2");
$m->query("UPDATE treinamento_banners SET imagem = 'assets/images/treinamentos/banner_academy_3.svg' WHERE id = 3");

$m->query("UPDATE treinamentos SET imagem_capa = 'assets/images/treinamentos/curso_financeiro_1.svg' WHERE id = 1");
$m->query("UPDATE treinamentos SET imagem_capa = 'assets/images/treinamentos/curso_financeiro_2.svg' WHERE id = 2");
$m->query("UPDATE treinamentos SET imagem_capa = 'assets/images/treinamentos/curso_faturamento.svg' WHERE id = 3");
$m->query("UPDATE treinamentos SET imagem_capa = 'assets/images/treinamentos/curso_cadastros.svg' WHERE id = 4");

echo "Caminhos de imagem atualizados nas tabelas!\n";
