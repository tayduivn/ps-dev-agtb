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

 require_once 'include/Expressions/DependencyManager.php';

/**
 * Check that actions are filtered depending on view given
 *
 * @ticket 62050
 * @author avucinic@sugarcrm.com
 */
class Bug62050Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $files = array();

    public function setUp()
    {
        $this->files = array();
    }

    public function tearDown()
    {
        foreach ($this->files as $file)
        {
            unlink($file);
        }
    }

    /**
     * Test Detail View Filter Function
     *
     * @dataProvider dataProvider
     * @group 62050
     */
    public function testDetailViewFilterFunction($module, $action, $view, $path, $data, $allowedActions, $bannedActions)
    {
        if (!is_dir($path))
        {
            mkdir($path, 0777, true);
        }

        $file = $path . 'deps.ext.php';

        // Add the files for deletion in tear down
        $this->files[] = $file;

        sugar_file_put_contents($file, $data);

        SugarAutoLoader::buildCache();
        $dependencies = DependencyManager::getModuleDependenciesForAction($module, $action, $view);
        $def = $dependencies[0]->getDefinition();

        // Pull out the filtered actions
        $filteredActions = array();
        foreach ($def['actions'] as $action)
        {
            $filteredActions[] = $action['action'];
        }

        // Check if all the allowed actions are there
        foreach ($allowedActions as $action)
        {
            $this->assertContains($action, $filteredActions);
        }

        // Check if the disallowed were removed
        foreach ($bannedActions as $action)
        {
            $this->assertNotContains($action, $filteredActions);
        }
    }

    public function dataProvider() {
        return array(
            array(
                // Module
                "Opportunities",
                // Action
                "view",
                // View
                "DetailView",
                // Path for the custom dependencies
                "custom/modules/Opportunities/Ext/Dependencies/",
                // Dependencies data
                "<?php \$dependencies['Opportunities']['views'] = array(
                        'hooks' => array('view'),
                        'trigger' => 'equal(\$name, \"aabb\")',
                        'triggerFields' => array('name'),
                        'onload' => true,
                        'actions' => array(
                            array(
                                'name' => 'SetValue',
                                'params' => array(
                                    'target' => 'amount',
                                    'value' => '99999',
                                ),
                            ),
                            array(
                                'name' => 'Style',
                                'params' => array(
                                    'target' => 'amount',
                                    'attrs'  => array(
                                        'fontSize' => '\"16px\"',
                                        'fontWeight' => '\"bold\"',
                                        'color' => '\"red\"',
                                    ),
                                ),
                            ),
                            array(
                                'name' => 'SetRequired',
                                'params' => array(
                                    'target' => 'amount',
                                    'label'  => 'amount',
                                    'value' => 'equal(\$name, \"aabb\")',
                                ),
                            ),
                            array(
                                'name' => 'SetPanelVisibility',
                                'params' => array(
                                    'target' => 'whole_subpanel_activities',
                                    'value'  => 'false',
                                ),
                            ),
                        ),
                    );
                ",
                // Allowed Expression Actions
                array(
                    'SetValue',
                    'Style',
                    'SetPanelVisibility'
                ),
                // Banned Expression Actions
                array(
                    'SetRequired',
                )
            )
        );
    }

}
