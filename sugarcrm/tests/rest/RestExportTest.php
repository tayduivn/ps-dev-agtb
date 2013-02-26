<?php
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


require_once('tests/rest/RestTestBase.php');

class RestExportTest extends RestTestBase
{

    // for now, export and MassExport use the same rest endpoints. This will change.
    private $singleRestPath = 'Accounts/export';
    private $massRestPath = 'Accounts/export';

    public function setUp()
    {
        parent::setUp();
        // multiple uids
        $num_accounts = 25;
        for ($i = 0; $i < $num_accounts; $i++) {
            $this->accounts[] = SugarTestAccountUtilities::createAccount();
        }
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->_cleanUpRecords();
        $this->accounts = array();
    }

    public function testExportWithFilter()
    {
        $chosenIndex = 13;
        // this filter should retrieve only one account.
        $reply = $this->_restCall($this->massRestPath.'?filter='.urlencode('[{"name":"'.$this->accounts[$chosenIndex]->name.'"}]'));
        foreach($this->accounts as $i => $account) {
            if ($i == $chosenIndex) {
                $this->assertContains($account->name, $reply['replyRaw'], 'Reply does not contain chosen account '.$i.' '.$account->name);
            }
            else {
                $this->assertNotContains($account->name, $reply['replyRaw'], 'Reply contains non-chosen account '.$i.' '.$account->name);
            }
        }

    }

    public function testExportWithoutFilter()
    {
        $reply = $this->_restCall($this->massRestPath);

        // we want them all.
        foreach($this->accounts as $i => $account) {
            $this->assertContains($account->name, $reply['replyRaw'], 'Reply does not contain account '.$i.' '.$account->name);
        }
    }

    public function testExportSample()
    {
        $reply = $this->_restCall($this->massRestPath.'?sample=true$all=true');
        $this->assertContains('This is a sample import file', $reply['replyRaw'], 'Reply does not contain description text');
    }


    /**
     * this test is to make sure our rest call can handle a GET arg in array format
     */
    public function testExportWithUids()
    {
        // single uid as array
        $chosenIndex = 17;
        $reply = $this->_restCall($this->massRestPath.'?uid[]='.$this->accounts[$chosenIndex]->id);
        foreach($this->accounts as $i => $account) {
            if ($i == $chosenIndex) {
                $this->assertContains($account->name, $reply['replyRaw'], 'Reply does not contain chosen account '.$i.' '.$account->name);
            }
            else {
                $this->assertNotContains($account->name, $reply['replyRaw'], 'Reply contains non-chosen account '.$i.' '.$account->name);
            }
        }

        // multiple uids - emulate jQuery's $.param() method, which is used by sugarapi.js::buildURL
        // called as $.param({uid: [a,b,c]})
        // http://api.jquery.com/jQuery.param/
        // we only want to retrieve accounts 0..$chosen_index-1 -- guard against case where all accounts are retrieved indiscriminately
        $accountString = '';
        for($i=0; $i < $chosenIndex; $i++) {
            $accountString .= 'uid[]='.urlencode($this->accounts[$i]->id).'&';
        }
        $accountString = rtrim($accountString,'&');

        $reply = $this->_restCall($this->massRestPath.'?'.$accountString);
        foreach ($this->accounts as $i => $account) {
            if ($i < $chosenIndex) {
                $this->assertContains($account->name, $reply['replyRaw'], 'Reply does not contain chosen account '.$i.' '.$account->name);
            }
            else {
                $this->assertNotContains($account->name, $reply['replyRaw'], 'Reply contains non-chosen account '.$i.' '.$account->name);
            }
        }
    }
}
