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

namespace Sugarcrm\SugarcrmTests\JobQueue\Manager;

use Psr\Log\NullLogger;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

class ManagerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
    }

    public function tearDown()
    {
        \SugarTestHelper::tearDown();
    }

    /**
     * Test that observers can be filtered by 'on' and 'off' flags.
     * @dataProvider providerObserverConfig
     */
    public function testObserverConfiguration($config, $handlers, $expectedCalls)
    {
        $config = array(
            'observers' => array($config),
        );
        $observerMock = $this->getMock('Sugarcrm\Sugarcrm\JobQueue\Observer\ObserverInterface');
        $observerMock->expects($this->exactly($expectedCalls))->method('onAdd');

        /* @var Manager $manager */
        $manager = $this->getMockBuilder('Sugarcrm\Sugarcrm\JobQueue\Manager\Manager')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'getSystemConfig',
                'getClient',
                'getSystemObservers',
                'initObserver'
            ))->getMock();
        $manager->expects($this->any())->method('getClient')->will($this->returnValue(
            $this->getMock('Sugarcrm\Sugarcrm\JobQueue\Client\ClientInterface')
        ));
        $manager->expects($this->any())->method('getSystemConfig')->will($this->returnValue($config));
        $manager->expects($this->any())->method('getSystemObservers')->will($this->returnValue(array()));
        $manager->expects($this->any())->method('initObserver')->will($this->returnValue($observerMock));
        TestReflection::setProtectedValue($manager, 'logger', new NullLogger());

        foreach ($handlers as $handler) {
            $workload = $this->getMock(
                'Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface',
                array(
                    'getRoute',
                    'setData',
                    'getData',
                    'setRoute',
                    'setAttribute',
                    'getAttribute',
                    'getAttributes',
                    'getHandlerName'
                )
            );
            $workload->expects($this->any())->method('getHandlerName')->will($this->returnValue($handler));
            $manager->addJob($workload);
        }
    }

    public function providerObserverConfig()
    {
        return array(
            array(
                // Config.
                array(
                    'class' => 'stub',
                ),
                // Handlers.
                array('h1', 'h2', 'h3'),
                // Expected count.
                3,
            ),
            array(
                array(
                    'class' => 'stub',
                    'on' => array('h1'),
                ),
                array('h1', 'h2'),
                1,
            ),
            array(
                array(
                    'class' => 'stub',
                    'on' => array(),
                    'off' => array('h2'),
                ),
                array('h1', 'h2'),
                1,
            ),
            array(
                array(
                    'class' => 'stub',
                    'on' => array('h1', 'h2'),
                    'off' => array('h2'),
                ),
                array('h1', 'h2', 'h3'),
                1,
            ),
            array(
                array(
                    'class' => 'stub',
                    'on' => array('h1'),
                    'off' => array('h1'),
                ),
                array('h1'),
                0,
            ),
        );
    }

    /**
     * Test observers follow order.
     */
    public function testObserversOrder()
    {
        $config = array(
            'observers' => array(
                array(
                    'class' => 'mock0',
                    'priority' => 100,
                ),
                array(
                    'class' => 'mock1',
                    'priority' => -100,
                ),
                array(
                    'class' => 'mock2',
                    'priority' => 0,
                ),
                array(
                    'class' => 'mock3',
                    'priority' => -200,
                ),
            ),
        );

        $observerMap = array();
        for ($i = 0; $i < count($config['observers']); $i++) {
            $observerMock = $this->getMock(
                'Sugarcrm\Sugarcrm\JobQueue\Observer\ObserverInterface',
                array(),
                array(),
                'observer' . $i
            );
            $classKey = $config['observers'][$i]['class'];
            $observerMap[$classKey] = array($classKey, null, $observerMock);
        }

        /* @var Manager $manager */
        $manager = $this->getMockBuilder('Sugarcrm\Sugarcrm\JobQueue\Manager\Manager')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'getSystemConfig',
                'getClient',
                'getSystemObservers',
                'initObserver',
                'applyObserver'
            ))
            ->getMock();
        $manager->expects($this->any())->method('getClient')->will($this->returnValue(
            $this->getMock('Sugarcrm\Sugarcrm\JobQueue\Client\ClientInterface')
        ));
        $manager->expects($this->any())->method('getSystemConfig')->will($this->returnValue($config));
        $manager->expects($this->any())->method('getSystemObservers')->will($this->returnValue(array()));
        $manager->expects($this->any())->method('initObserver')->will($this->returnValueMap($observerMap));
        TestReflection::setProtectedValue($manager, 'logger', new NullLogger());

        $manager->expects($this->exactly(4))
            ->method('applyObserver')
            ->withConsecutive(
                array($observerMap['mock0'][2], null),
                array($observerMap['mock2'][2], null),
                array($observerMap['mock1'][2], null),
                array($observerMap['mock3'][2], null)
            );

        $manager->addJob($this->getMock('Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface'));
    }
}
