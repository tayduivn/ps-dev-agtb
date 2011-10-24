<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


 
require_once('modules/Import/Importer.php');

class Bug47737Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // if beanList got unset, set it back
        if (!isset($GLOBALS['beanList'])) {
            require('include/modules.php');
            $GLOBALS['beanList'] = $beanList;
        }
    }

    public function tearDown()
    {
        restore_error_handler();
    }

    public function providerIdData()
    {
        return array(
            //Valid ids
            array('12345','12345'),
            array('12345-6789-1258','12345-6789-1258'),
            array('aaaBBB12AA122cccD','aaaBBB12AA122cccD'),
            array('aaa-BBB-12AA122-cccD','aaa-BBB-12AA122-cccD'),
            array('aaa_BBB_12AA122_cccD','aaa_BBB_12AA122_cccD'),
            //Invalid
            array('1242','12*'),
            array('abdcd36','abdcd$'),
            array('1234-asdf3535353523','1234-asdf####23'),
            );
    }

    /**
     * @ticket 47737
     * @dataProvider providerIdData
     */
    public function testConvertID($expected, $dirty)
    {
        $c = new Contact();
        $importer = new ImporterStub('UNIT TEST',$c);
        $actual = $importer->convertID($dirty);

        $this->assertEquals($expected, $actual, "Error converting id during import process $actual , expected: $expected, before conversion: $dirty");
    }

}


class ImporterStub extends Importer
{

    public function convertID($id)
    {
        return $this->_convertId($id);
    }
}