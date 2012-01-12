<?php
//FILE SUGARCRM flav=pro ONLY
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
 * Bug49698Test.php
 * This class tests to ensure that label changes made on the Rename Modules link from the Admin section
 * are accurately reflected.
 *
 * @author Collin Lee
 */

require_once('modules/Reports/views/view.buildreportmoduletree.php');

class Bug49698Test extends Sugar_PHPUnit_Framework_TestCase
{

public function testModuleRenameForReportsTree()
{
    $mock = new ReportsViewBuildreportmoduletreeMock();
    $linked_field = array(
        'name' => 'accounts',
        'type' => 'link',
        'relationship' => 'accounts_opportunities',
        'source' => 'non-db',
        'link_type' => 'one',
        'module' => 'Accounts',
        'bean_name' => 'Account',
        'vname' => 'LBL_ACCOUNTS',
        'label' => 'Prospects' //Assume here that Accounts module label was renamed to Prospects
    );
    $node = $mock->_populateNodeItem('Opportunity', 'Accounts', $linked_field);
    $this->assertRegExp('/\\\'Prospects\\\'/', $node['href']);
}

}

/**
 * ReportsViewBuildreportmoduletreeMock
 * This is a mock class to override the protected function _populateNodeItem so we may test it
 *
 */
class ReportsViewBuildreportmoduletreeMock extends ReportsViewBuildreportmoduletree
{
    public function __construct()
    {

    }

    public function _populateNodeItem($bean_name,$link_module,$linked_field)
    {
        return parent::_populateNodeItem($bean_name,$link_module,$linked_field);
    }
}