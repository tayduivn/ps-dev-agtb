<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');
require_once('include/SugarSearchEngine/SugarSearchEngineFullIndexer.php');

// TODO: this one needs to be redone using new boost functionality
// skipping test for now

class SugarSearchEngineElasticBoostTest extends Sugar_PHPUnit_Framework_TestCase {
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('mod_strings', array('Administration', 'Leads'));
        $this->leads = array();
        $this->files = array();
        $this->dir = 'custom/Extension/modules/Leads/Ext/Vardefs';
        $this->search_engine_name = SugarSearchEngineFactory::getFTSEngineNameFromConfig();
        $this->search_engine = SugarSearchEngineFactory::getInstance(SugarSearchEngineFactory::getFTSEngineNameFromConfig(), array(), false);
        parent::setUp();
    }

    public function tearDown()
    {
        $leadIds = array();
        foreach ($this->leads as $lead) {
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
     * @large
     */
    public function testBoostSearch() {
        $this->markTestSkipped('Skipping this test because of intermittent results. We should reindex after
                                changing boost values to ensure good results. However the boost system is being
                                redesigned so it is notworth spending too much time on this yet.');

        if($this->search_engine_name != 'Elastic') {
            $this->markTestSkipped('Marking this skipped. Elastic Search is not installed.');
        } else {
            // XXX TODO: Replace this test with a real unit test that doesn't involve half the systems infrastructure to test a specific feature.
            // Last but not least this should be a data driven test that test combinations of field types, boost values and analyzers so that we hit all corner cases.
            $this->markTestSkipped('Due to the fact that various field and analyzer types cannot be index at creation time, it is decided to only (and always) use boost values on queries. Hence this test needs to be completely revisided.');
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

        // set Leads Name is High Boost
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
        $this->assertEquals($lead_name_id, $new_results[0]->getId(),  "The First Result wasn't the Lead with the Name");

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

        // need to wait to get the search, will review this
        sleep(10);

        // run search again with 5678
        // Run search with 5678
        $results = $this->search_engine->search('5678', 0, 1000, array('moduleFilter' => array('Leads')));

        // Verify that Lead with 5678 description is first record
        $new_results = array();
        foreach($results AS $result) {
            $new_results[] = $result;
        }

        // check the first one only.
        $this->assertEquals($lead_description_id, $new_results[0]->getId(),  "The First Result wasn't the Lead with the Description");
    }


}

