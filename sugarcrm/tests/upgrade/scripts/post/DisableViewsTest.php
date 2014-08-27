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

require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/7_DisableViews.php';

/**
 * Test covers behavior of SugarUpgradeDisableViews post upgrade script
 */
class DisableViewsTest extends UpgradeTestCase
{
    protected $directory = '';

    protected $currentDirectory = '';
    protected $currentPath = '';

    public function setUp()
    {
        $this->currentDirectory = getcwd();
        $this->currentPath = ini_get('include_path');

        parent::setUp();

        $this->directory = sugar_cached(__CLASS__);
        sugar_mkdir($this->directory . '/cache', null, true);
        ini_set('include_path', getcwd() . PATH_SEPARATOR . ini_get('include_path'));
        chdir($this->directory);
    }

    public function tearDown()
    {
        parent::tearDown();
        chdir($this->currentDirectory);
        $this->currentDirectory = '';
        ini_set('include_path', $this->currentPath);
        $this->currentPath = '';
        rmdir_recursive($this->directory);
    }

    public static function getFiles()
    {
        return array(
            array(
                array(
                    'custom/modules/Accounts/views/bad.php',
                    'custom/modules/Accounts/views/good.php',
                ),
                'hasCustomViews',
                array(
                    'custom/modules/Accounts/views/bad.php',
                ),
                array(
                    'custom/modules/Accounts/views/Disabled/bad.php' => true,
                    'custom/modules/Accounts/views/good.php' => true,
                    'custom/modules/Accounts/views/bad.php' => false,
                    'custom/modules/Accounts/views/Disabled/good.php' => false,
                    'modules/Accounts/views/Disabled/bad.php' => false,
                    'modules/Accounts/views/good.php' => false,
                    'modules/Accounts/views/bad.php' => false,
                    'modules/Accounts/views/Disabled/good.php' => false,
                ),
            ),
            array(
                array(
                    'modules/Accounts/views/bad.php',
                    'modules/Accounts/views/good.php',
                ),
                'hasCustomViewsModDir',
                array(
                    'modules/Accounts/views/bad.php',
                ),
                array(
                    'modules/Accounts/views/Disabled/bad.php' => true,
                    'modules/Accounts/views/good.php' => true,
                    'modules/Accounts/views/bad.php' => false,
                    'modules/Accounts/views/Disabled/good.php' => false,
                    'custom/modules/Accounts/views/Disabled/bad.php' => false,
                    'custom/modules/Accounts/views/good.php' => false,
                    'custom/modules/Accounts/views/bad.php' => false,
                    'custom/modules/Accounts/views/Disabled/good.php' => false,
                ),
            ),
        );
    }

    /**
     * Test creates two files
     * The first one is bad and should be moved to Disabled directory
     * The second one is good because of md5strings and shouldn't be moved to Disable directory
     *
     * @dataProvider getFiles
     *
     * @param array $filesToCreate of files paths
     * @param string $report name of report of health check
     * @param array $filesToDetect of files paths
     * @param array $filesToAssert key is file and value bool (should file exist or not)
     */
    public function testRun($filesToCreate, $report, $filesToDetect, $filesToAssert)
    {
        foreach ($filesToAssert as $file => $assertion) {
            SugarTestHelper::saveFile($file);
        }

        foreach ($filesToCreate as $file) {
            sugar_file_put_contents($file, '');
        }

        $script = new SugarUpgradeDisableViews($this->upgrader);
        $this->upgrader->state['healthcheck'] = array(
            array(
                'report' => $report,
                'params' => array(
                    'Accounts',
                    $filesToDetect,
                ),
            ),
        );

        $script->run();

        foreach ($filesToAssert as $file => $assertion) {
            if ($assertion) {
                $this->assertFileExists($file, 'File is not found');
            } else {
                $this->assertFileNotExists($file, 'File should be created');
            }
        }
    }
}
