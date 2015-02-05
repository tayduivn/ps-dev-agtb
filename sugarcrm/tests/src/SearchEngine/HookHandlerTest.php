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

namespace Sugarcrm\SugarcrmTest\SearchEngine;

use Sugarcrm\Sugarcrm\SearchEngine\SearchEngine;
use Sugarcrm\Sugarcrm\SearchEngine\HookHandler;

/**
 *
 * Tests for \Sugarcrm\Sugarcrm\SearchEngine\HookHandler
 *
 */
class HookHandlerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @covers \Sugarcrm\Sugarcrm\SearchEngine\HookHandler::indexBean
     * @dataProvider dataProviderTestIndexBean
     * @group unit
     *
     * @param \SugarBean|null $bean
     * @param integer $count
     */
    public function testIndexBean($bean, $count)
    {
        $hook = $this->getMockBuilder('\\Sugarcrm\\Sugarcrm\\SearchEngine\\HookHandler')
            ->setMethods(array('getSearchEngine', 'getLogger'))
            ->getMock();

        $hook->expects($this->exactly($count))
            ->method('getSearchEngine')
            ->will($this->returnValue($this->getSearchEngineMock($count)));

        $hook->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue(new \SugarNullLogger()));

        $hook->indexBean($bean, 'event', array());
    }

    public function dataProviderTestIndexBean()
    {
        return array(
            array(
                $this->getMock('SugarBean'),
                1,
            ),
            array(
                null,
                0,
            )
        );
    }

    /**
     * @param integer $callIndexBeanCount
     * @return \Sugarcrm\Sugarcrm\SearchEngine\SearchEngine
     */
    protected function getSearchEngineMock($callIndexBeanCount)
    {
        $engine = $this->getMock('\\Sugarcrm\\Sugarcrm\\SearchEngine\\Engine\\EngineInterface');

        $engine->expects($this->exactly($callIndexBeanCount))
            ->method('indexBean');

        return new SearchEngine($engine);
    }
}
