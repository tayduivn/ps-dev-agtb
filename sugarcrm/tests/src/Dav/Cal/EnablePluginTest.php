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

use Sugarcrm\Sugarcrm\Dav\Cal\EnablePlugin;

class EnablePluginTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Server|\PHPUnit_Framework_MockObject_MockObject|null $server
     */
    protected $server = null;

    /**
     * @var EnablePlugin
     */
    protected $enablePlugin = null;

    /**
     * @var \Configurator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurator = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->configurator = $this->getMock('Configurator');
        $this->enablePlugin = new EnablePlugin($this->configurator);
        $this->server = $this->getMock('Sabre\DAV\Server');
    }

    /**
     * Testing case if calendar is disabled.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\EnablePlugin::checkIsEnabled
     * @expectedException \Sabre\DAV\Exception\ServiceUnavailable
     */
    public function testIfDisabled()
    {
        $this->configurator->config = array('caldav_enable_sync' => false);

        $this->enablePlugin->checkIsEnabled();
    }

    /**
     * Testing case if calendar is disabled.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\EnablePlugin::checkIsEnabled
     */
    public function testIfEnabled()
    {
        $this->configurator->config = array('caldav_enable_sync' => true);

        $this->assertNull($this->enablePlugin->checkIsEnabled(), 'Checking is method called with out errors');
    }

    /**
     * Checking subscribe plugin.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\EnablePlugin::initialize
     */
    public function testInitialize()
    {
        $this->server
            ->expects($this->once())
            ->method('once')
            ->with('beforeMethod', array($this->enablePlugin, 'checkIsEnabled'));
        $this->enablePlugin->initialize($this->server);
    }

    /**
     * Checking plugin name.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\EnablePlugin::getPluginName
     */
    public function testGetPluginName()
    {
        $this->assertEquals('check-is-enabled', $this->enablePlugin->getPluginName());
    }
}
