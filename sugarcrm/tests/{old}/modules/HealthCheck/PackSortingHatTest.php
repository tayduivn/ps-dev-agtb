<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once __DIR__ . '/../../../../modules/HealthCheck/pack_sortinghat.php';

class PackSortingHatTest extends PHPUnit_Framework_TestCase
{

    public function healthCheckPackProvider()
    {
        return array(
            array(
                array(
                    'version' => '1.2.3.4'
                ),
                array(
                    'version' => '1.2.3.4',
                    'build' => '998'
                ),
            ),
            array(
                array(),
                array(
                    'version' => '7.5.0.0',
                    'build' => '998'
                ),
            ),
            array(
                array(
                    'build' => '1.2.3.4'
                ),
                array(
                    'version' => '7.5.0.0',
                    'build' => '1.2.3.4'
                ),
            )
        );
    }

    /**
     * @dataProvider healthCheckPackProvider
     * @param $params
     * @param $expect
     */
    public function testHealthCheckPack($params, $expect)
    {
        $zip = $this->createMock('ZipArchive');
        $versionFile = __DIR__ . '/../../../modules/HealthCheck/Scanner/version.json';
        $zip->expects($this->exactly(6))->method('addFile');
        packSortingHat($zip, $params);

        $this->assertEquals(json_encode($expect), file_get_contents($versionFile));
        unlink($versionFile);
    }

    public function testPackSortingHatPhp()
    {
        $result = exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../modules/HealthCheck/pack_sortinghat.php');
        $this->assertEquals(
            "Use " . __DIR__ . "/../../../modules/HealthCheck/pack_sortinghat.php healthcheck.phar [sugarVersion [buildNumber]]",
            $result
        );
        $zip = tempnam('/tmp', 'phar') . '.phar';
        exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../modules/HealthCheck/pack_sortinghat.php ' . $zip);
        $this->assertTrue(file_exists($zip));
        unlink($zip);
    }
}
