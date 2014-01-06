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

require_once('include/QuickSearchDefaults.php');

/**
 * @ticket 66276
 */
class Bug66276Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $testFiles = array(
        array('dir' => 'custom/include', 'name' => 'QuickSearchDefaults.php', 'content' => '<?php class QuickSearchDefaultsCustom {}'),
        array('dir' => 'custom/modules/Test', 'name' => 'QuickSearchDefaults.php', 'content' => '<?php class QuickSearchDefaultsModule {}'));

    public function setUp()
    {
        foreach ($this->testFiles as $testFile) {
            if (!file_exists($testFile['dir'])) {
                sugar_mkdir($testFile['dir'], 0777, true);
            }

            SugarAutoLoader::put($testFile['dir'] . '/' . $testFile['name'], $testFile['content'], true);
        }
    }

    public function tearDown()
    {
        foreach ($this->testFiles as $testFile) {
            if (SugarAutoLoader::fileExists($testFile['dir'] . '/' . $testFile['name'])) {
                SugarAutoLoader::unlink($testFile['dir'] . '/' . $testFile['name'], true);
            }
        }
    }

    /**
     * Tests function QuickSearchDefaults::getQuickSearchDefaults()
     */
    public function testGetQuickSearchDefaults()
    {
        $this->assertInstanceOf('QuickSearchDefaultsModule', QuickSearchDefaults::getQuickSearchDefaults(array('custom/modules/Test/QuickSearchDefaults.php'=>'QuickSearchDefaultsModule')));
        $this->assertInstanceOf('QuickSearchDefaultsCustom', QuickSearchDefaults::getQuickSearchDefaults());
        SugarAutoLoader::unlink('custom/include/QuickSearchDefaults.php', true);
        $this->assertInstanceOf('QuickSearchDefaults', QuickSearchDefaults::getQuickSearchDefaults());
    }
}
