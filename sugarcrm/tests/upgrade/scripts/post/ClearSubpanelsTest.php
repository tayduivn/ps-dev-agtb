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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'upgrade/scripts/post/4_ClearSubpanels.php';

/**
 * Test for clearing bad defs from supbanels definitions..
 */
class ClearSubpanelsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        global $beanList;
        parent::setUp();
        SugarTestHelper::setUp('files');
        SugarTestHelper::setUp('beanList');
        $bean = $this->getMock('SugarBean');
        $beanList = array(
            'PreScript' => get_class($bean),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @param array $def
     * @param string $layout
     * @param string $file
     * @param array $expectedLayout
     *
     * @dataProvider provider
     */
    public function testRun($def, $layout, $file, $expectedLayout)
    {
        $path = sugar_cached(__CLASS__);
        SugarAutoLoader::ensureDir($path . DIRECTORY_SEPARATOR . 'custom');
        $fpath = $path . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . $file;
        SugarTestHelper::saveFile($fpath);
        sugar_file_put_contents($fpath, $layout);


        $upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $upgradeDriver->context = array(
            'source_dir' => $path
        );

        $script = $this->getMock(
            'SugarUpgradeClearSubpanels',
            array('getDefFiles', 'updateFile', 'getBeanDefs', 'rebuildExtensions'),
            array($upgradeDriver)
        );

        $script->expects($this->any())
            ->method('getBeanDefs')
            ->will($this->returnValue($def));
        $script->expects($this->any())
            ->method('getDefFiles')
            ->will($this->returnValue(array($fpath)));
        $script->expects($this->once())
            ->method('rebuildExtensions')
            ->with(array('' => ''));
        $script->expects($this->once())
            ->method('updateFile')
            ->with($fpath, $expectedLayout);
        $script->run();
    }

    public function provider()
    {
        return array(
            array(
                array(
                    'a' => array(
                        'name' => 'a',
                        'type' => 'text'
                    ),
                    'b' => array(
                        'name' => 'b',
                        'type' => 'text'
                    ),
                ),
                <<<EOL
<?php
\$subpanel_layout = array (
  'top_buttons' => array (),
  'where' => '',
  'list_fields' => array (
    'a' =>
    array (
      'vname' => 'a',
    ),
    'b' => array (
      'vname' => 'b',
    ),
    'c' => array (
      'vname' => 'c',
      'widget_class' => 'SomeClass',
    ),
    'd' => array (
      'vname' => 'd',
      'usage' => 'SomeUsage'
    ),
    'e' => array (
      'vname' => 'e',
    ),
    'edit_button' => array (
      'vname' => 'edit_button'
    ),
  ),
);
EOL
                ,
                'tst.php',
                array(
                    'top_buttons' => array(),
                    'where' => '',
                    'list_fields' => array(
                        'a' => array (
                            'vname' => 'a',
                        ),
                        'b' => array (
                            'vname' => 'b',
                        ),
                        'c' => array (
                            'vname' => 'c',
                            'widget_class' => 'SomeClass',
                        ),
                        'd' => array (
                            'vname' => 'd',
                            'usage' => 'SomeUsage'
                        ),
                        'edit_button' => array(
                            'vname' => 'edit_button',
                            'widget_class' => 'SubPanelEditButton'
                        )
                    )
                )
            ),
            array(
                array(
                    'a' => array(
                        'name' => 'a',
                        'type' => 'link',
                        'relationship' => 'd',
                    ),
                    'b' => array(
                        'name' => 'b',
                        'type' => 'relate',
                        'id_name' => 'c',
                        'relationship' => 'd',
                        'link' => 'a',
                    ),
                    'c' => array(
                        'name' => 'c',
                        'type' => 'id',
                        'relationship' => 'd',
                    )
                ),
                <<<EOL
<?php
\$layout_defs[""]["subpanel_setup"]["c"] = array (
  'order' => 100,
  'module' => 'PreScript',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL',
  'get_subpanel_data' => 'c',
  'top_buttons' =>
  array (),
);
EOL
            ,
                'tst2.php',
                array(
                    '' => array(
                        'subpanel_setup' => array (
                            'a' => array (
                                'order' => 100,
                                'module' => 'PreScript',
                                'subpanel_name' => 'default',
                                'sort_order' => 'asc',
                                'sort_by' => 'id',
                                'title_key' => 'LBL',
                                'get_subpanel_data' => 'a',
                                'top_buttons' =>
                                array (),
                            )
                        ),
                    )
                ),
            ),
        );
    }
}
