<?php
//FILE SUGARCRM flav=pro ONLY 
$k_path_url = 'http://localhost/';
require_once('include/Sugarpdf/Sugarpdf.php');
/**
 * @ticket 38850
 */
class Bug38850Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testCanInterjectCodeInTcpdfTag() 
    {
        $pdf = new Bug38850TestMock(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $dom = array(
            0 => array(
                'value' => 'html'
                ),
            1 => array(
                'parent' => 0,
                'value' => 'tcpdf',
                'attribute' => array(
                    'method' => 'Close',
                    'params' => ");echo ('Can Interject Code'",
                    ),
                ),
            );
        
        $output = '';
        try {
            ob_start();
            $pdf->openHTMLTagHandler($dom, 1);
            $output .= ob_get_contents();
            ob_end_clean();
        }
        catch (PHPUnit_Framework_Error $e) {
        }
        
        $this->assertNotContains('Can Interject Code',$output);
    }
}

class Bug38850TestMock extends TCPDF
{
    public function openHTMLTagHandler(&$dom, $key, $cell=false) 
    {
        parent::openHTMLTagHandler($dom, $key, $cell);
    }
}
