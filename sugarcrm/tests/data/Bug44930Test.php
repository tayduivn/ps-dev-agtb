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

/**
 * Bug #44930
 * Issue with the opportunity subpanel in Accounts
 *
 * @author mgusev@sugarcrm.com
 * @ticked 44930
 */
class Bug44930Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test tries to emulate changing of related field and assert correct result
     *
     * @group 44930
     * @return void
     */
    public function testChangingOfRelation()
    {
        $_REQUEST['relate_id'] = '2';
        $_REQUEST['relate_to'] = 'test';

        $bean = new SugarBean();
        $bean->id = '1';
        $bean->test_id = '3';
        $bean->field_defs = array(
            'test' => array(
                'type' => 'link',
                'relationship' => 'test',
                'link_file' => 'data/SugarBean.php',
                'link_class' => 'Link44930'
            )
        );
        $bean->relationship_fields = array(
            'test_id' => 'test'
        );

        $bean->save_relationship_changes(true);

        $this->assertEquals($bean->test_id, $bean->test->lastCall, 'Last relation should point to test_id instead of relate_id');
    }
}

/**
 * Emulation of link2 class
 */
class Link44930
{
    public $lastCall = '';

    function __call($function, $arguments)
    {
        if ($function == 'add')
        {
            $this->lastCall = reset($arguments);
        }
    }
}