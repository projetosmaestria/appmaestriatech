<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**


* CodeIgniter PDF Library
 *
 * Generate PDF's in your CodeIgniter applications.
 *
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 * @author          Chris Harvey
 * @license         MIT License
 * @link            https://github.com/chrisnharvey/CodeIgniter-  PDF-Generator-Library



*/

require_once APPPATH.'third_party/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
class Pdf extends DOMPDF
{
    /**
     * Get an instance of CodeIgniter
     *
     * @access  protected
     * @return  void
     */
    protected function ci()
    {
        return get_instance();
    }

    /**
     * Load a CodeIgniter view into domPDF
     *
     * @access  public
     * @param   string  $view The view to load
     * @param   array   $data The view data
     * @return  void
     */
    public function load_view($view, $nome_arquivo, $data = array())
    {
        $dompdf = new Dompdf();
        $dompdf->set_option('enable_html5_parser', TRUE);
        $html = $this->ci()->load->view($view, $data, TRUE);
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();
        $time = date('Ymd');

        $canvas = $dompdf->get_canvas();
        $canvas->page_script('
          if ($pdf->get_page_number() != $pdf->get_page_count()) {
            $font = Font_Metrics::get_font("helvetica", "12");                  
            $pdf->text(500, 770, "Page {PAGE_NUM} - {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->text(260, 770, "Canny Pack", $font, 10, array(0,0,0));
            $pdf->text(43, 770, $date, $font, 10, array(0,0,0));
          }
        ');

        $dompdf->stream($nome_arquivo);
        exit;
    }

    /**
     * Load a CodeIgniter save domPDF
     *
     * @access  public
     * @param   string  $view The view to load
     * @param   array   $data The view data
     * @return  void
     */
    public function load_view_save($view, $nome_arquivo, $data = array())
    {
        $dompdf = new Dompdf();
        $dompdf->set_option('enable_html5_parser', TRUE);
        $html = $this->ci()->load->view($view, $data, TRUE);
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();
        $time = date('Ymd');

        $canvas = $dompdf->get_canvas();
        $canvas->page_script('
          if ($pdf->get_page_number() != $pdf->get_page_count()) {
            $font = Font_Metrics::get_font("helvetica", "12");                  
            $pdf->text(500, 770, "Page {PAGE_NUM} - {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->text(260, 770, "Canny Pack", $font, 10, array(0,0,0));
            $pdf->text(43, 770, $date, $font, 10, array(0,0,0));
          }
        ');

        $output = $dompdf->output();
        file_put_contents($nome_arquivo.'.pdf', $output);
    }
}