<?php
//FILE SUGARCRM flav=pro ONLY   
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
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
