<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');
require_once('include/SugarSearchEngine/SugarSearchEngineFullIndexer.php');
require_once('include/SugarSearchEngine/SugarSearchEngineSyncIndexer.php');
class SugarSearchEngineElasticBoostTest extends Sugar_PHPUnit_Framework_TestCase {
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        $this->calls = array();
        $this->files = array();
        $this->dir = 'custom/Extension/modules/Leads/Ext/Vardefs';
        $this->search_engine_name = SugarSearchEngineFactory::getFTSEngineNameFromConfig();
        $this->search_engine = SugarSearchEngineFactory::getInstance(SugarSearchEngineFactory::getFTSEngineNameFromConfig(), array(), false);        
        parent::setUp();
    }

    public function tearDown()
    {
        $leadIds = array();
        foreach ( $this->calls as $lead ) {
            $this->search_engine->delete($lead);
            $leadIds[] = $lead->id;
        }
        $leadIds = "('".implode("','",$leadIds)."')";

        $GLOBALS['db']->query("DELETE FROM leads WHERE id IN {$leadIds}");
        if ($GLOBALS['db']->tableExists('leads_cstm')) {
            $GLOBALS['db']->query("DELETE FROM leads_cstm WHERE id_c IN {$leadIds}");
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    
        foreach($this->files AS $file) {
            //unlink($file);
        }

        // run repair and rebuild
        $old_user = $GLOBALS['current_user'];
        $user = new User();
        $GLOBALS['current_user'] = $user->getSystemUser();

        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array('clearAll'), array(), true, false);
        $GLOBALS['current_user'] = $old_user;

        parent::tearDown();

    }

    /**
     * @group elastic
     */
    public function testBoostSearch() {
        if($this->search_engine_name != 'Elastic') {
            $this->markTestSkipped('Marking this skipped. Elastic Search is not installed.');
        }        
        $lead = new Lead();
        $lead->name = 'test' . create_guid();
        $lead->account_name = "5678";
        $lead->account_description = "1000";
        $lead->assigned_user_id = $GLOBALS['current_user']->id;
        $lead->team_id = 1;
        $lead->team_set_id = 1;
        $lead->save();
        $lead_name_id = $lead->id;
        $this->leads[] = $lead;
        $this->search_engine->indexBean($lead, FALSE);

        $lead = new Lead();
        $lead->name = 'test' . create_guid();
        $lead->account_name = "1000";
        $lead->account_description = "5678";
        $lead->assigned_user_id = $GLOBALS['current_user']->id;
        $lead->team_id = 1;
        $lead->team_set_id = 1;
        $lead->save();
        $lead_description_id = $lead->id;
        $this->leads[] = $lead;
        $this->search_engine->indexBean($lead, FALSE);
        
        $GLOBALS['db']->commit();

        // set Calls Name is High Boost
        if(!is_dir($this->dir)) {
            sugar_mkdir($this->dir, null, true);
        }
        

        $filename = 'sugarfield_account_name.php';
        $this->files[] = $this->dir . '/' . $filename;

        $file_contents = '<?php
        $dictionary[\'Lead\'][\'fields\'][\'account_name\'][\'full_text_search\']=array (
  \'boost\' => \'3\',
); 
?>';
        file_put_contents($this->dir . '/' . $filename, $file_contents);

        $filename = 'sugarfield_account_description.php';
        $this->files[] = $this->dir . '/' . $filename;

        $file_contents = '<?php
        $dictionary[\'Lead\'][\'fields\'][\'account_description\'][\'full_text_search\']=array (
  \'boost\' => \'1\',
); 
?>';

        file_put_contents($this->dir . '/' . $filename, $file_contents);

        // run repair and rebuild
        $old_user = $GLOBALS['current_user'];
        $user = new User();
        $GLOBALS['current_user'] = $user->getSystemUser();

        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array('clearAll'), array(), true, false);
        $GLOBALS['current_user'] = $old_user;

        // need to wait to get the search, will review this
        sleep(10);

        // Run search with 5678
        $results = $this->search_engine->search('5678', 0, 1000, array('moduleFilter' => array('Leads')));

        // Verify that Lead with 5678 name is first record
        $new_results = array();
        foreach($results AS $result) {
            $new_results[] = $result;
        }

        // check the first one only.
        $this->assertEquals($new_results[0]->getId(), $lead_name_id, "The First Result wasn't the Lead with the Name");    

        
        // set description as high boost and name as low boost

        $filename = 'sugarfield_account_name.php';
        
        $file_contents = '<?php
        $dictionary[\'Lead\'][\'fields\'][\'account_name\'][\'full_text_search\']=array (
  \'boost\' => \'1\',
); 
?>';
        file_put_contents($this->dir . '/' . $filename, $file_contents);

        $filename = 'sugarfield_account_description.php';

        $file_contents = '<?php
        $dictionary[\'Lead\'][\'fields\'][\'account_description\'][\'full_text_search\']=array (
  \'boost\' => \'3\',
); 
?>';
        file_put_contents($this->dir . '/' . $filename, $file_contents);

        // run repair and rebuild
        $old_user = $GLOBALS['current_user'];
        $user = new User();
        $GLOBALS['current_user'] = $user->getSystemUser();

        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array('clearAll'), array(), true, false);
        $GLOBALS['current_user'] = $old_user;
        
        // run search again with 5678
        // Run search with 5678
        $results = $this->search_engine->search('5678', 0, 1000, array('moduleFilter' => array('Leads')));

        // Verify that Lead with 5678 description is first record
        $new_results = array();
        foreach($results AS $result) {
            $new_results[] = $result;
        }

        // check the first one only.
        $this->assertEquals($new_results[0]->getId(), $lead_description_id, "The First Result wasn't the Lead with the Description");     
    }


}

