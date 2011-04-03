<?php
//FILE SUGARCRM flav=pro ONLY      
require_once("include/Sugarpdf/sugarpdf_config.php");
require_once('include/tcpdf/config/lang/eng.php');
require_once('include/tcpdf/tcpdf.php');
/**
 * @ticket 38850
 */
class Bug38850Test extends Sugar_PHPUnit_Framework_OutputTestCase
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
                    'params' => serialize(array(");echo ('Can Interject Code'")),
                    ),
                ),
            );

        $pdf->openHTMLTagHandler($dom, 1);
        $this->expectOutputNotRegex('/Can Interject Code/');
    }
}

class Bug38850TestMock extends TCPDF
{
    public function openHTMLTagHandler($dom, $key, $cell=false)
    {
        parent::openHTMLTagHandler($dom, $key, $cell);
    }
}
