<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

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
            SugarAutoLoader::delFromMap($file);
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
