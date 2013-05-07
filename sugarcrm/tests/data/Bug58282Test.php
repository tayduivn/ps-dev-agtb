<?php
//FILE SUGARCRM flav=pro ONLY

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'include/Expressions/Expression/Parser/Parser.php';

class Bug58282Test extends Sugar_PHPUnit_Framework_TestCase
{
    private static $custom_field_def = array(
        'formula'     => 'strToUpper(related($accounts,"name"))',
        'name'        => 'custom_58282',
        'type'        => 'text',
        'label'       => 'LBL_CUSTOM_FIELD',
        'module'      => 'ModuleBuilder',
        'view_module' => 'Opportunities',
    );

    /** @var Account */
    private $account;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $mbc = new ModuleBuilderController();
        //Create the new Fields
        $_REQUEST = self::$custom_field_def;
        $mbc->action_SaveField();

        VardefManager::refreshVardefs('Opportunities', 'Opportunity');
    }

    public function setUp()
    {
        $this->account = SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public static function tearDownAfterClass()
    {
        $mbc = new ModuleBuilderController();

        $custom_field_def = self::$custom_field_def;
        $custom_field_def['name'] .= '_c';
        $_REQUEST = $custom_field_def;
        $mbc->action_DeleteField();

        VardefManager::refreshVardefs('Opportunities', 'Opportunity');

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        $_REQUEST = array();
        SugarCache::$isCacheReset = false;

        SugarTestHelper::tearDown();
    }

    /**
     */
    public function testRelatedBeanIsSaved()
    {
        $account = new Account();
        $account->retrieve($this->account->id);

        $link = $this->getLinkMock($account, true);
        $account->opportunities = $link;

        $account->name = 'Name has been changed';
        $account->save();
    }

    /**
     */
    public function testRelatedBeanIsNotSaved()
    {
        $account = new Account();
        $account->retrieve($this->account->id);

        $link = $this->getLinkMock($account, false);
        $account->opportunities = $link;

        $account->description = 'Added new description';
        $account->save();
    }

    /**
     * Creates mock of Link2 object with specified number of related beans
     *
     * @param SugarBean $focus
     * @param boolean $shouldBeanBeSaved
     * @return Link2
     */
    protected function getLinkMock(SugarBean $focus, $shouldBeanBeSaved)
    {
        $bean = $this->getMock('SugarBean', array('save'));
        $bean->id = 'Bug58282Test';

        if ($shouldBeanBeSaved) {
            $bean->expects($this->any())
                ->method('save');
        } else {
            $bean->expects($this->never())
                ->method('save');
        }

        $beans = array($bean);

        $mock = $this->getMock(
            'Link2',
            array('getBeans'),
            array('opportunities', $focus)
        );
        $mock->expects($this->any())
            ->method('getBeans')
            ->will($this->returnValue($beans));
        return $mock;
    }
}
