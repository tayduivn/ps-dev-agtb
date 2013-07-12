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

/**
 * @ticket 63989
 */
class Bug63989Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var aCase
     */
    private $case;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        $this->case = BeanFactory::getBean('Cases');
    }

    public function tearDown()
    {
        /** @var DBManager */
        global $db;
        if ($this->case && $this->case->id) {
            $query = 'DELETE FROM cases where id = ' . $db->quoted($this->case->id);
            $db->query($query);
        }
    }

    public function testGetCaseIdFromCaseNumber()
    {
        $this->case->save();
        $id = $this->case->id;

        $this->case->disable_row_level_security = true;
        $this->case->retrieve($id);
        $number = $this->case->case_number;

        $ie = new InboundEmail();
        $subject = '[CASE:' . $number . ']';
        $actual_id = $ie->getCaseIdFromCaseNumber($subject, $this->case);

        $this->assertEquals($id, $actual_id);
    }

    /**
     * @param $emailName
     * @dataProvider shouldNotQueryProvider
     */
    public function testShouldNotQuery($emailName)
    {
        $db = $this->getMockForAbstractClass('DBManager');
        $db->expects($this->never())
            ->method('query');

        $ie = new InboundEmail();
        $ie->db = $db;
        $ie->getCaseIdFromCaseNumber($emailName, $this->case);
    }

    public function shouldNotQueryProvider()
    {
        return array(
            array('An arbitrary subject'),
            array('[CASE:THE-CASE-NUMBER]'),
        );
    }
}
