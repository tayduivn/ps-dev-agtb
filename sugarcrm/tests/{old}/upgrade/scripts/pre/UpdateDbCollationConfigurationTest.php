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

use Sugarcrm\Sugarcrm\DependencyInjection\Container;

require_once 'upgrade/scripts/pre/UpdateDbCollationConfiguration.php';

/**
 * @coversDefaultClass SugarUpgradeUpdateDbCollationConfiguration
 */
class SugarUpgradeUpdateDbCollationConfigurationTest extends UpgradeTestCase
{
    protected function setUp()
    {
        parent::setUp();

        // Back up the configuration override.
        SugarTestHelper::saveFile('config_override.php');
    }

    public function runProvider()
    {
        return [
            'no collation in the configuration' => [
                'mysql',
                '',
                '',
            ],
            'configured collation is allowed' => [
                'mysql',
                'utf8mb4_general_ci',
                'utf8mb4_general_ci',
            ],
            'mapped collation is allowed' => [
                'mysql',
                'utf8_general_ci',
                'utf8mb4_general_ci',
            ],
            'collation is not allowed' => [
                'mysql',
                'utf8_general_mysql500_ci',
                'utf8mb4_general_ci',
            ],
            'db2 does not need to be upgraded' => [
                'ibm_db2',
                'utf8_general_ci',
                'utf8_general_ci',
            ],
            'oracle does not need to be upgraded' => [
                'oci8',
                'utf8_general_ci',
                'utf8_general_ci',
            ],
            'mssql does not need to be upgraded' => [
                'mssql',
                'utf8_general_ci',
                'utf8_general_ci',
            ],
            'sql server does not need to be upgraded' => [
                'sqlsrv',
                'utf8_general_ci',
                'utf8_general_ci',
            ],
        ];
    }

    /**
     * @dataProvider runProvider
     * @covers ::run
     * @covers Configurator::saveConfig
     */
    public function testRun($database, $configuredCollation, $expectedCollation)
    {
        $this->upgrader->db = $this->getMockForAbstractClass(
            DBManager::class,
            [],
            '',
            false,
            false,
            true,
            ['getOption']
        );
        $this->upgrader->db->method('getOption')
            ->with($this->equalTo('collation'))
            ->willReturn($configuredCollation);
        $this->upgrader->db->dbType = $database;

        $script = $this->createPartialMock('SugarUpgradeUpdateDbCollationConfiguration', [
            'log',
            'getAllowedCollations',
        ]);
        $script->method('getAllowedCollations')->willReturn(['utf8mb4_general_ci']);
        $script->upgrader = $this->upgrader;
        $script->from_version = '8.0.0';

        $config = new Configurator();
        $config->config['dbconfigoption']['collation'] = $configuredCollation;
        $config->saveConfig();

        $script->run();

        $actual = Container::getInstance()->get(SugarConfig::class)->get('dbconfigoption.collation');
        $this->assertSame($expectedCollation, $actual);
    }
}
