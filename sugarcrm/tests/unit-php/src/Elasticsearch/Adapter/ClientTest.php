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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Adapter;

use Elastica\Response;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client;
use Sugarcrm\Sugarcrm\Elasticsearch\Logger;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client
 *
 */
class ClientTest extends TestCase
{
    protected $config = array('host' => 'localhost', 'port' => '9200');
    protected $logger;
    /**
     * @covers ::__construct
     * @covers ::setLogger
     * @covers ::parseConfig
     *
     */
    public function testConstructor()
    {
        $client = $this->getTestClient();
        $this->assertSame($this->logger, TestReflection::getProtectedValue($client, '_logger'));
    }

    /**
     * @covers ::setConfig
     * @covers ::getConfig
     * @covers ::getVersion
     * @covers ::getEsVersion
     * @covers ::getAllowedVersions
     *
     */
    public function testSettersAndGetters()
    {
        $client = $this->getTestClient();
        $client->setConfig($this->config);

        $this->assertSame($this->config['host'], $client->getConfig()['host']);
        $this->assertSame($this->config['host'], $client->getConfig('host'));
        $this->assertSame($this->config['port'], $client->getConfig('port'));

        $version = '5.4';
        TestReflection::setProtectedValue($client, 'version', $version);
        $this->assertSame($version, $client->getVersion());

        $this->assertTrue(in_array($version, $client->getAllowedVersions()));
    }

    /**
     * @covers ::checkEsVersion
     * @covers ::isEsVersion5x
     *
     * @dataProvider providerTestCheckVersion
     */
    public function testCheckVersion($version, $expected)
    {
        $client = $this->getTestClient();
        $this->assertSame($expected, TestReflection::callProtectedMethod($client, 'checkEsVersion', array($version)));
    }

    public function providerTestCheckVersion()
    {
        return array(
            //5.4.x is supported
            array('5.4.0', true),
            array('5.4.9', true),
            array('5.4', true),
            // 5.5.x is not supported
            array('5.5.0', false),
            array('5.5', false),
            // 1.x and 2.x are not supported
            array('1.7', false),
            array('2.3.1', false),
        );
    }

    /**
     * @covers ::isAvailable
     * @covers ::verifyConnectivity
     * @covers ::loadAvailability
     * @covers ::updateAvailability
     * @covers ::processDataResponse
     *
     * @dataProvider providerTestIsAvailable
     */
    public function testIsAvailable($force, $isSearchEngineAvallble, $responseString, $expected)
    {
        $clientMock = $this->getClientMock(array('ping', 'isSearchEngineAvailable', 'saveAdminStatus'));
        $clientMock->expects($this->any())
            ->method('ping')
            ->will($this->returnValue(new Response($responseString)));
        $clientMock->expects($this->any())
            ->method('isSearchEngineAvailable')
            ->will($this->returnValue($isSearchEngineAvallble));

        $clientMock->expects($this->any())
            ->method('saveAdminStatus');

        $this->assertSame($expected, $clientMock->isAvailable($force));
    }

    public function providerTestIsAvailable()
    {
        return array(
            // no force update
            array(
                false,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ),
            // force update, all good
            array(
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ),
            // force update, new ES status is good
            array(
                true,
                false,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                true,
            ),
            // update to not available
            array(
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ),
            // bad status
            array(
                true,
                false,
                '{
                  "status" : 401,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.4.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ),
            // ES version 1.7, not supported
            array(
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "1.7",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ),
            // ES version 2.3, not supported
            array(
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "2.3.0",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ),
            // ES version 5.3, not supported
            array(
                true,
                true,
                '{
                  "status" : 200,
                  "name" : "Zom",
                  "cluster_name" : "elasticsearch_brew",
                  "version" : {
                    "number" : "5.3.0.",
                    "build_hash" : "62ff9868b4c8a0c45860bebb259e21980778ab1c",
                    "build_timestamp" : "2015-04-27T09:21:06Z",
                    "build_snapshot" : false,
                    "lucene_version" : "4.10.4"
                  },
                  "tagline" : "You Know, for Search"
                }',
                false,
            ),
        );
    }

    /**
     * @covers ::verifyConnectivity
     * @covers ::onConnectionFailure
     *
     */
    public function testVerifyConnectivityHandleException()
    {
        $clientMock = $this->getClientMock(array('ping'));
        $clientMock->expects($this->any())
            ->method('ping')
            ->will($this->throwException(new \Exception()));

        $status = $clientMock->verifyConnectivity(false);
        $this->assertSame(Client::CONN_FAILURE, $status);
    }

    /**
     * @covers ::request
     *
     * @expectedException \Exception
     */
    public function testRequestException()
    {
        $clientMock = $this->getClientMock(array('isAvailable'));
        $clientMock->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(false));

        $clientMock->request('/');
    }

    /**
     * @return Client Mock object
     */
    protected function getClientMock(array $methods = null)
    {
        $this->setLogger();
        $mock = TestMockHelper::getObjectMock($this, 'Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client', $methods);
        $mock->setLogger($this->logger);
        return $mock;
    }

    /**
     * to get real Client instance
     * @return Client
     */
    protected function getTestClient()
    {
        $this->setLogger();
        $client = new Client($this->config, $this->logger);
        return $client;
    }

    /**
     * set logger
     */
    protected function setLogger()
    {
        $logMgr = \LoggerManager::getLogger();
        // don't record anything in the log
        $logMgr->setLevel('off');
        $this->logger = new Logger($logMgr);
    }
}
