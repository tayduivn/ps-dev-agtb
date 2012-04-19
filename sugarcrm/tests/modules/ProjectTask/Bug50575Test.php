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

require_once('modules/ModuleBuilder/controller.php');
require_once('modules/ProjectTask/views/view.list.php');

/**
 * Bug #50575
 * Query Failure when searching in Project Tasks list view, using Accounts field created from Relationship
 *
 * @author asokol@sugarcrm.com
 * @ticket 50575
 */

class Bug50575Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    protected $_project;
    protected $_projectTask;
    protected $_account;

    protected $_savedSearchDefs;
    protected $_savedSearchFields;

    protected $relationship;
    protected $relationships;

    protected $_localSearchFields = array (
        'ProjectTask' => array(
            'name' => array (
                'query_type' => 'default',
            ),
            'project_name' => array (
                'query_type' => 'default',
                'db_field' => array (
                    0 => 'project.name',
                ),
            ),
        )
    );

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        parent::setUp();
        $this->relationships = new DeployedRelationships('Products');
        $definition = array(
            'lhs_module' => 'Accounts',
            'relationship_type' => 'one-to-many',
            'rhs_module' => 'ProjectTask'
        );
        $this->relationship = RelationshipFactory::newRelationship($definition);
        $this->relationships->add($this->relationship);
        $this->relationships->save();
        $this->relationships->build();
        SugarTestHelper::setUp('relation', array(
            'Accounts',
            'ProjectTask'
        ));

        $searchDefs = array(
                'layout' => array(
                    'advanced_search' => array(
                        $this->relationship->getName() . '_name' => array (
                            'type' => 'relate',
                            'link' => true,
                            'label' => '',
                            'id' => strtoupper($this->relationship->getJoinKeyLHS()),
                            'width' => '10%',
                            'default' => true,
                            'name' => $this->relationship->getName() . '_name',
                        ),
                    )
                ),
                'templateMeta' => array (
                    'maxColumns' => '3',
                    'maxColumnsBasic' => '4',
                    'widths' => array (
                        'label' => '10',
                        'field' => '30',
                    ),
                ),
        );
        // Add new field to advanced search layout
        if(file_exists("custom/modules/ProjectTask/metadata/searchdefs.php"))
        {
            $this->_savedSearchDefs = file_get_contents("custom/modules/ProjectTask/metadata/searchdefs.php");
        }

        write_array_to_file("searchdefs['ProjectTask']", $searchDefs, 'custom/modules/ProjectTask/metadata/searchdefs.php');

        if(file_exists("modules/ProjectTask/metadata/SearchFields.php"))
        {
            $this->_savedSearchFields = file_get_contents("modules/ProjectTask/metadata/SearchFields.php");
        }

        write_array_to_file("searchFields['ProjectTask']", $this->_localSearchFields['ProjectTask'], 'modules/ProjectTask/metadata/SearchFields.php');

        // Creates linked test account, project and project task
        $this->_project = SugarTestProjectUtilities::createProject();
        $this->_account = SugarTestAccountUtilities::createAccount();

        $projectTaskData = array (
            'project_id' => $this->_project->id,
            'parent_task_id' => '',
            'project_task_id' => '1',
            'percent_complete' => '0',
            'name' => 'Test Task 1',
            'duration_unit' => 'Days',
            'duration' => '1',
        );

        $this->_projectTask = SugarTestProjectTaskUtilities::createProjectTask($projectTaskData);
        $this->_projectTask->{$this->relationship->getName()}->add($this->_account);
        $this->_projectTask->save();
    }

    public function tearDown()
    {
        if(!empty($this->_savedSearchDefs))
        {
            file_put_contents("custom/modules/ProjectTask/metadata/searchdefs.php", $this->_savedSearchDefs);
        }
        else
        {
            @unlink("custom/modules/ProjectTask/metadata/searchdefs.php");
        }

        if(!empty($this->_savedSearchFields))
        {
            file_put_contents("modules/ProjectTask/metadata/SearchFields.php", $this->_savedSearchFields);
        }
        else
        {
            @unlink("modules/ProjectTask/metadata/SearchFields.php");
        }
        SugarTestProjectTaskUtilities::removeAllCreatedProjectTasks();
        SugarTestProjectUtilities::removeAllCreatedProjects();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        $this->relationships->delete($this->relationship->getName());
        $this->relationships->save();
        parent::tearDown();
        SugarCache::$isCacheReset = false;
        SugarTestHelper::tearDown();
        $GLOBALS['reload_vardefs'] = true;
        $bean = new ProjectTask();
        unset($GLOBALS['reload_vardefs']);
    }

    /**
     * Test checks if advanced search provides correct result (correct SQL query)
     * @group 50575
     */
    public function testCustomAdvancedSearch()
    {
        $_REQUEST = $_POST = array(
            "module" => "ProjectTask",
            "action" => "index",
            "searchFormTab" => "advanced_search",
            "displayColumns" => "NAME|PROJECT_NAME",
            "query" => "true",
            $this->relationship->getName(). '_name_advanced' => $this->_account->name,
            "button" => "Search",
        );

        $vl = new ProjectTaskViewList();
        $vl->bean = $this->_projectTask;
        $GLOBALS['module'] = 'ProjectTask';
        $vl->module = 'ProjectTask';

        $this->expectOutputRegex("/(" . $this->_project->name . ")/");
        $vl->display();
    }
}
