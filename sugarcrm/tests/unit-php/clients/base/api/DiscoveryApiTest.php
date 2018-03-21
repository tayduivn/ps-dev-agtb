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

namespace Sugarcrm\SugarcrmTestsUnit\clients\base\api;

/**
 * @coversDefaultClass \DiscoveryApi
 */
class DiscoveryApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::discovery
     * @dataProvider discoveryDataProvider
     */
    public function testDiscovery($expected, $config)
    {
        $api = $this->getMockBuilder(\DiscoveryApi::class)
            ->setMethods(['getIDMModeConfig'])
            ->getMock();
        $api->method('getIDMModeConfig')
            ->willReturn($config);

        $this->assertEquals($expected, $api->discovery($this->createMock(\ServiceBase::class), []));
    }

    public function discoveryDataProvider()
    {
        return [
            'idm mode disabled' => [
                ['idmMode' => false],
                [],
            ],
            'idm mode enabled' => [
                [
                    'idmMode' => true,
                    'stsUrl' => 'http://sts.sugarcrm.local',
                    'tenant' => 'srn:cloud:idp:eu:0000000001:tenant',
                ],
                [
                    'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
                    'stsUrl' => 'http://sts.sugarcrm.local',
                ],
            ],
        ];
    }
}
