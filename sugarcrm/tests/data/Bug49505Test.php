<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/**
 * @ticket 49505
 */
class Bug49505Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    private $_createdBeans = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        foreach ($this->_createdBeans as $bean) {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }

        SugarTestHelper::tearDown();
    }

    public function testGetRelatedListFunctionWithLink2Class()
    {
        $focusModule = 'Accounts';
        $linkedModules = array(
            'Bugs', // many-to-many
            'Contacts' // one-to-many
        );

        $focus = BeanFactory::newBean($focusModule);
        $focus->name = "bug49505";
        $focus->save();
        $this->_createdBeans[] = $focus;

        foreach ($linkedModules as $v) {

            $linkedBean = BeanFactory::newBean($v);
            $linkedBean->name = "bug49505";
            $linkedBean->save();
            $this->_createdBeans[] = $linkedBean;

            $link = new Link2(strtolower($v), $focus);
            $link->add(array($linkedBean));

            // get relation from 'Link2' class
            $link2List = $focus->get_related_list($linkedBean, strtolower($v));

            // get relation for 'get_related_list' function from Link class
            $focus->field_defs[strtolower($v)]['link_class'] = 'Link';
            $focus->field_defs[strtolower($v)]['link_file'] = 'data/Link.php';
            $linkList = $focus->get_related_list($linkedBean, strtolower($v));

            unset($focus->field_defs[strtolower($v)]['link_class']);
            unset($focus->field_defs[strtolower($v)]['link_file']);

            $this->assertEquals($linkedBean->id, $linkList['list'][0]->id);
            $this->assertEquals($linkedBean->id, $link2List['list'][0]->id);
        }
    }

}
