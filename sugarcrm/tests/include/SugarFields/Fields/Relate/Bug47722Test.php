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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 * ****************************************************************************** */

require_once('include/SugarFields/Fields/Relate/SugarFieldRelate.php');
require_once('modules/Leads/Lead.php');
require_once('modules/Import/ImportFieldSanitize.php');
/**
 * Bug #47722
 * 	Imports to Custom Relate Fields Do Not Work
 * @ticket 47722
 */
class Bug47722Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $contact;
    
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->contact = SugarTestContactUtilities::createContact();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestContactUtilities::removeAllCreatedContacts();
    }
    
    /**
     * @group 47722
     */
    public function testImportSanitize()
    {
        $vardef = array('module' => 'Contacts', 
                        'id_name' => 'contact_id_c', 
                        'name' => 'test_rel_cont_c');
        $value = $this->contact->first_name .' '. $this->contact->last_name;
        $focus = new Lead();
        $settings = new ImportFieldSanitize();
        
        $sfr = new SugarFieldRelate('relate');
        $value = $sfr->importSanitize($value, $vardef, $focus, $settings);
        $this->assertEquals($focus->$vardef['id_name'], $this->contact->id);
    }
}
?>