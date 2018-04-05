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

use PHPUnit\Framework\TestCase;

class ConfiguratorTest extends TestCase
{
    protected function tearDown()
    {
        Activity::restoreToPreviousState();
    }

    public function testPopulateFromPostConvertsBoolValuesFromStrings()
    {
        $_POST = array(
            'disable_export' => 'true',
            'admin_export_only' => 'false',
            'upload_dir' => 'yummy'
            );

    	$cfg = new Configurator();

        $cfg->populateFromPost();
        $_POST = array();

        $this->assertEquals($cfg->config['disable_export'], true);
        $this->assertEquals($cfg->config['admin_export_only'], false);
        $this->assertEquals($cfg->config['upload_dir'], 'yummy');
    }

    public function clearCacheDataProvider()
    {
        return array(
            'activity_streams_enabled config is set the first time' => array(
                array(),
                array('activity_streams_enabled' => true),
                [
                    MetaDataManager::MM_CONFIG,
                    MetaDataManager::MM_SERVERINFO,
                    MetaDataManager::MM_MODULES,
                ],
            ),
            'activity_streams_enabled config is set the first time' => array(
                array('activity_streams_enabled' => true),
                array(),
                [
                    MetaDataManager::MM_CONFIG,
                    MetaDataManager::MM_SERVERINFO,
                    MetaDataManager::MM_MODULES,
                ],
            ),
            'activity_streams_enabled config is set the first time' => array(
                array('activity_streams_enabled' => false),
                array(),
                [
                    MetaDataManager::MM_CONFIG,
                    MetaDataManager::MM_SERVERINFO,
                    MetaDataManager::MM_MODULES,
                ],
            ),
            'activity_streams_enabled config changes' => array(
                array('activity_streams_enabled' => false),
                array('activity_streams_enabled' => true),
                [
                    MetaDataManager::MM_CONFIG,
                    MetaDataManager::MM_SERVERINFO,
                    MetaDataManager::MM_MODULES,
                ],
            ),
            'activity_streams_enabled config remains enabled' => array(
                array('activity_streams_enabled' => true),
                array('activity_streams_enabled' => true),
                [
                    MetaDataManager::MM_CONFIG,
                    MetaDataManager::MM_SERVERINFO,
                ],
            ),
            'activity_streams_enabled config remains disabled' => array(
                array('activity_streams_enabled' => false),
                array('activity_streams_enabled' => false),
                [
                    MetaDataManager::MM_CONFIG,
                    MetaDataManager::MM_SERVERINFO,
                ],
            ),
            'activity_streams_enabled config is not set and is not changed' => array(
                array(),
                array('new_email_addresses_opted_out' => true),
                [
                    MetaDataManager::MM_CONFIG,
                    MetaDataManager::MM_SERVERINFO,
                ],
            ),
            'activity_streams_enabled config is set and is not changed' => array(
                array('activity_streams_enabled' => true),
                array('new_email_addresses_opted_out' => true),
                [
                    MetaDataManager::MM_CONFIG,
                    MetaDataManager::MM_SERVERINFO,
                    MetaDataManager::MM_MODULES,
                ],
            ),
        );
    }

    /**
     * @covers ::clearCache
     * @dataProvider clearCacheDataProvider
     * @param array $oldConfig
     * @param array $newConfig
     * @param array $expectedSections
     */
    public function testClearCache_UpdatesMetadataCache($oldConfig, $newConfig, $expectedSections)
    {
        $configurator = $this->createPartialMock('Configurator', array('updateMetadataCache', 'readOverride'));
        $configurator->expects($this->once())
            ->method('updateMetadataCache')
            ->with($expectedSections);

        $configurator->expects($this->once())
            ->method('readOverride')
            ->will($this->returnValue(array($oldConfig, $newConfig)));

        $configurator->clearCache();
    }
}
