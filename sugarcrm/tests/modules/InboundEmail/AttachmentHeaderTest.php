<?php
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

require_once 'modules/InboundEmail/InboundEmail.php';
class AttachmentHeaderTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $ie = null;

    public function setUp()
    {
        $this->ie = new InboundEmail();
    }

    /**
     * @param $param -> "dparameters" | "parameters"
     * @param $a -> attribute
     * @param $v -> value
     * @return stdClass:  $obj->attribute = $a, $obj->value = $v
     */
    protected function _convertToObject($param,$a,$v)
    {
        $obj = new stdClass;
        $obj->attribute = $a;
        $obj->value = $v;

        $outer = new stdClass;
        $outer->parameters = ($param == 'parameters') ? array($obj) : array();
        $outer->isparameters = !empty($outer->parameters);
        $outer->dparameters = ($param == 'dparameters') ? array($obj) : array();
        $outer->isdparameters = !empty($outer->dparameters);

        return $outer;
    }

    public function contentParameterProvider()
    {
        return array(
            // pretty standard dparameters
            array(
                $this->_convertToObject('dparameters','filename','test.txt'),
                'test.txt'
            ),

            // how about a regular parameter set
            array(
                $this->_convertToObject('parameters','name','bonus.txt'),
                'bonus.txt'
            )
        );
    }

    /**
     * @group bug57309
     * @dataProvider contentParameterProvider
     * @param array $in - the part parameters -> will convert to object in test method
     * @param string $expected - the name digested from the parameters
     */
    public function testRetrieveAttachmentNameFromStructure($in, $expected)
    {
        $this->assertEquals($expected, $this->ie->retrieveAttachmentNameFromStructure($in),  'We did not get the attachmentName');
    }
}
