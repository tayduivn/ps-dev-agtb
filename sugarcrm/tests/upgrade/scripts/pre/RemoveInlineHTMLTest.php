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
require_once 'upgrade/scripts/pre/RemoveInlineHTML.php';

/**
 * Test asserts correct removal of inline html in php files under custom directory
 */
class RemoveInlineHTMLTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var UpgradeDriver */
    protected $upgradeDriver = null;
    
    public function setUp()
    {
        SugarTestHelper::setUp('files');
        $this->upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $this->upgradeDriver->context = array();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts correct removal of inline HTML
     *
     * @dataProvider getContents
     */
    public function testRun($content, $expected)
    {
        $path = sugar_cached(__CLASS__);
        $file = $path . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . 'test.php';
        $this->upgradeDriver->context['source_dir'] = $path;
        SugarAutoLoader::ensureDir($path . DIRECTORY_SEPARATOR . 'custom');
        SugarTestHelper::saveFile($file);
        sugar_file_put_contents($file, $content);

        $script = $this->getMock('SugarUpgradeRemoveInlineHTML', array('backupFile') , array($this->upgradeDriver));
        if ($content == $expected) {
            $script->expects($this->never())->method('backupFile');
        } else {
            $script->expects($this->once())->method('backupFile')->with($this->equalTo('custom' . DIRECTORY_SEPARATOR . 'test.php'));
        }
        $script->run();
        $actual = sugar_file_get_contents($file);
        $this->assertEquals($expected, $actual, 'File trimmed incorrectly');
    }

    /**
     * Returns data for testRun, content and its expected trimmed version
     *
     * @return array
     */
    public static function getContents()
    {
        return array(
            array(
                "<?php ?>",
                "<?php ?>",
            ),
            array(
                "<?php ?>\n",
                "<?php ?>\n",
            ),
            array(
                "<?php ?> ",
                "<?php ?>",
            ),

            array(
                "<?php ?> \n\r\t\n\r\n",
                "<?php ?>",
            ),
            array(
                "<?php \n\r\t\n\r",
                "<?php \n\r\t\n\r",
            ),
            array(
                "\n\n<?php ?> ",
                "<?php ?>",
            ),
            array(
                "\r\n\r\n\t\t\t<?php ?>\n",
                "<?php ?>\n",
            ),
            array(
                "\r\n\r\n\t\t\t<?php ?>\n\n\n\n\n\r",
                "<?php ?>",
            ),
        );
    }
}
