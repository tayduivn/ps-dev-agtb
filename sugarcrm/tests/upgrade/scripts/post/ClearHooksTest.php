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

require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'upgrade/scripts/post/9_ClearHooks.php';

/**
 * Test for clearing logic hooks.
 */
class ClearHooksTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
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
     * Test removing bad hooks.
     * @param array $hooks
     * @param array $result
     * @param bool $rewrite
     *
     * @dataProvider provider
     */
    public function testRun($hooks, $result, $rewrite)
    {
        $path = sugar_cached(__CLASS__);
        SugarAutoLoader::ensureDir($path);
        $upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $upgradeDriver->context = array(
            'source_dir' => $path
        );
        $fname = $path . DIRECTORY_SEPARATOR . 'testHooks.php';
        file_put_contents($fname, "<?php \$hooks_array=" . var_export($hooks, true) . ";");
        $script = $this->getMock(
            'SugarUpgradeClearHooks',
            array('findHookFiles'),
            array($upgradeDriver)
        );
        $script->expects($this->any())
            ->method('findHookFiles')
            ->will($this->returnValue(array('ext' => array(), 'hooks' => array($fname))));

        if ($rewrite) {
            $script->expects($this->any())
                ->method('rewriteHookFile')
                ->with('', $result);
        } else {
            $script->expects($this->never())
                ->method('rewriteHookFile');
        }
        $script->run();
    }

    /**
     * Data provider.
     * @return array
     */
    public function provider()
    {
        return array(
            'GoodHooks' => array(
                array(
                    'before_save' => array(
                        array(1, '', 'data/SugarBean.php', 'SugarBean', 'retrieve'),
                        array(2, '', 'data/SugarBean.php', 'SugarBean', 'save')
                    )
                ),
                array(),
                false,
            ),
            'BadHook' => array(
                array(
                    'before_save' => array(
                        array(1, '', 'data/SugarBean.php', 'SugarBean', 'retrieve'),
                        array(2, '', 'data/SugarBean.php', 'SugarBean', 'SomeStrangeMethod')
                    ),
                ),
                array(
                    'before_save' => array(
                        array(1, '', 'data/SugarBean.php', 'SugarBean', 'retrieve'),
                    ),
                ),
                true,
            ),
            'BadHooks' => array(
                array(
                    'before_save' => array(
                        array(1, '', 'data/SugarBean.php', 'SugarBean', 'SomeStrangeMethod2'),
                        array(2, '', 'data/SugarBean.php', 'SugarBean', 'SomeStrangeMethod'),
                        array(3, '', 'data/SugarBean3.php', 'SugarBean3', 'SomeStrangeMethod'),
                        array(4, ''),
                    ),
                ),
                array(),
                true,
            )
        );
    }
}
