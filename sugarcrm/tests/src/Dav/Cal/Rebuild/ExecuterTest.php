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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Rebuild;

/**
 * Class ExecuterTest
 *
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\Executer
 */
class ExecuterTest extends \Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterFactory = null;

    /**
     * @var \Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\Executer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reExporter = null;

    /**
     * @var \Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $hookHandler = null;

    /**
     * @var \Meeting|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bean = null;

    /**
     * List of supported modules.
     *
     * @var array
     */
    protected $supportedModules = array('SupportedModules1:CRYS:1633', 'SupportedModules2:CRYS:1633', );

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->bean = $this->getMock('Meeting');
        $this->hookHandler = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler');
        $this->bean->method('getCalDavHook')->willReturn($this->hookHandler);

        $this->adapterFactory = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory');
        $this->adapterFactory->method('getSupportedModules')->willReturn($this->supportedModules);
        $this->reExporter = $this->getMock(
            'Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\Executer',
            array('getCalDavAdapterFactory', 'getBeanIterator')
        );
        $this->reExporter->method('getCalDavAdapterFactory')->willReturn($this->adapterFactory);

    }

    /**
     * Testing is fetched beans for all modules that have Executer.
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\Executer::getBeans
     */
    public function testGetBeans()
    {
        /** @var array $calDavModules */
        $calDavModules = array();

        /** @var array $beansExpected */
        $beansExpected = array(
            $this->supportedModules[0] => array(
                $this->supportedModules[0] . rand(1000, 9999),
                $this->supportedModules[0] . rand(1000, 9999),
                $this->supportedModules[0] . rand(1000, 9999),
            ),
            $this->supportedModules[1] => array(
                $this->supportedModules[1] . rand(1000, 9999),
                $this->supportedModules[1] . rand(1000, 9999),
                $this->supportedModules[1] . rand(1000, 9999),
            ),
        );
        /** @var array $beansExpectedList */
        $beansExpectedList = call_user_func_array('array_merge', array_values($beansExpected));

        $this->reExporter->expects($this->any())
            ->method('getBeanIterator')
            ->withConsecutive(
                array($this->equalTo($this->supportedModules[0])),
                array($this->equalTo($this->supportedModules[1]))
            )
            ->will($this->returnCallback(function ($module) use (&$calDavModules, $beansExpected) {
                $calDavModules[] = $module;
                return new \ArrayIterator($beansExpected[$module]);
            }));

        $beans = array();
        foreach ($this->reExporter->getBeans() as $bean) {
            $beans[] = $bean;
        }

        $this->assertCount(count($this->supportedModules), $calDavModules);
        $this->assertArraySubset($this->supportedModules, $calDavModules);

        $this->assertCount(count($beansExpectedList), $beans);
        $this->assertArraySubset($beans, $beansExpectedList);
    }

    /**
     * Testing is executed export of hookHandler.
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Rebuild\Executer::execute
     */
    public function testExecute()
    {
        $this->hookHandler->expects($this->once())->method('export')
            ->with($this->equalTo($this->bean), $this->equalTo(false), $this->equalTo(true));

        $this->reExporter->execute($this->bean);
    }
}
