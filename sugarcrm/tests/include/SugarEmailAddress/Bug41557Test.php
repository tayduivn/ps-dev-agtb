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
 * @ticket 41557
 */
class Bug41557Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function providerGetPrimaryAddress()
        {
            return array(
                array('old1@test.com', 'new1@test.com', false, 2),
                array('old2@test.com', 'new2@test.com', true, 1),
            );
        }

    /**
     * @group bug41557
     * @dataProvider providerGetPrimaryAddress
     */
    public function testGetPrimaryAddress($oldemail, $newemail, $conversion, $primary_count)
    {
        if ($conversion) {
            $_REQUEST['action'] = 'ConvertLead';
        }

        $user = SugarTestUserUtilities::createAnonymousUser();

        // primary email address
        $user->emailAddress->addAddress($oldemail, true, false);
        $user->emailAddress->save($user->id, $user->module_dir);

        $this->assertEquals($oldemail, $user->emailAddress->getPrimaryAddress($user), 'Primary email should be '.$oldemail);

        // second email
        $user->emailAddress->addAddress($newemail, true, false);

        // simulate lead conversion mode
        if ($conversion) {
            $_REQUEST['action'] = 'ConvertLead';
        }
        $user->emailAddress->save($user->id, $user->module_dir);

        $query = "select count(*) as CNT from email_addr_bean_rel eabr WHERE eabr.bean_id = '{$user->id}' AND eabr.bean_module = 'Users' and primary_address = 1 and eabr.deleted=0";
        $result = $GLOBALS['db']->query($query);
        $count = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals($primary_count, $count['CNT'], 'Incorrect primary email count');

        // cleanup
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
}
