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

namespace Sugarcrm\SugarcrmTests\Dav\Cal;

use Sugarcrm\Sugarcrm\Dav\Cal\ForceDeletePlugin;

/**
 * Class ForceDeletePluginTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Cal
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\ForceDeletePlugin
 */
class ForceDeletePluginTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var \Sabre\DAV\Server|\PHPUnit_Framework_MockObject_MockObject|null $server */
    protected $server = null;

    /** @var \Sabre\HTTP\Request|\PHPUnit_Framework_MockObject_MockObject|null $server */
    protected $httpRequest = null;

    /** @var ForceDeletePlugin */
    protected $forceDeletePlugin = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->forceDeletePlugin = new ForceDeletePlugin();
        $this->server = $this->getMock('Sabre\DAV\Server');
        $this->httpRequest = $this->getMock('\Sabre\HTTP\Request');
        $this->server->httpRequest = $this->httpRequest;
    }

    /**
     * Checking subscribe plugin.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\ForceDeletePlugin::initialize
     */
    public function testInitialize()
    {
        $this->server
            ->expects($this->once())
            ->method('once')
            ->with('beforeMethod', array($this->forceDeletePlugin, 'forceDelete'));
        $this->forceDeletePlugin->initialize($this->server);
    }

    /**
     * Checking plugin name.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\ForceDeletePlugin::getPluginName
     */
    public function testGetPluginName()
    {
        $this->assertEquals('force-delete', $this->forceDeletePlugin->getPluginName());
    }

    /**
     * Checking case when http method not DELETE.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\ForceDeletePlugin::forceDelete
     */
    public function testNoDeleteMethod()
    {
        $this->httpRequest->expects($this->never())->method('setHeader');
        $this->httpRequest->method('getMethod')->willReturn('someOtherMethod');

        $this->forceDeletePlugin->initialize($this->server);
        $this->forceDeletePlugin->forceDelete();
    }

    /**
     * Checking case when http method DELETE.
     *
     * @dataProvider deleteMethodProvider
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\ForceDeletePlugin::forceDelete
     * @param string $method
     */
    public function testDeleteMethod($method)
    {
        $this->httpRequest->expects($this->once())
            ->method('setHeader')
            ->with($this->equalTo('If-Match'), $this->equalTo('*'));

        $this->httpRequest->method('getMethod')->willReturn($method);

        $this->forceDeletePlugin->initialize($this->server);
        $this->forceDeletePlugin->forceDelete();
    }

    /**
     * Data provider for testDeleteMethod.
     *
     * @see Sugarcrm\SugarcrmTests\Dav\Cal\ForceDeletePluginTest::testDeleteMethod
     * @return array
     */
    public function deleteMethodProvider()
    {
        return array(
            'loverCase' => array(
                'method' => 'delete',
            ),
            'upperCase' => array(
                'method' => 'DELETE',
            ),
            'mix' => array(
                'method' => 'deLeTe',
            ),
        );
    }
}
