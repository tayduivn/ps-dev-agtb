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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Hook;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler
 */

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerImportExportTest
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler::run
     * @param \SugarBean|\CalDavEvent $beanClass
     * @param string $expectedFunction
     * @param array $shouldntCalled list of function that shouldn't be called during hook run processing
     */

    public function testRunHook($beanClass, $expectedFunction, $shouldntCalled = array())
    {
        /**@var \Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler $hookHandlerMock */
        /**@var \Sugarcrm\Sugarcrm\Dav\Cal\Handler $davHandlerMock */

        $hookHandlerMock = $this->getHandlerMock('\Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler', array('getDavHandler'));
        $davHandlerMock = $this->getHandlerMock('\Sugarcrm\Sugarcrm\Dav\Cal\Handler', array('import', 'export'));

        $hookHandlerMock->method('getDavHandler')->willReturn($davHandlerMock);
        if ($expectedFunction) {
            $davHandlerMock->expects($this->once())->method($expectedFunction)->with($beanClass);
        }
        foreach ($shouldntCalled as $methodName) {
            $davHandlerMock->expects($this->never())->method($methodName);
        }

        $hookHandlerMock->run($beanClass, null, null);
    }

    /**
     * Get data for testImportBean function
     * @return array
     */

    public function providerImportExportTest()
    {
        return array(
            array(new \CalDavEvent(), 'import', array('export')),
            array(new \SugarBean(), 'export', array('import')),
            array(new \stdClass(), '', array('import','export'))
        );
    }

    /**
     * Get Mock object for hook handler
     * @param string $classPath
     * @param array|null $methods
     * @return mixed
     */
    protected function getHandlerMock($classPath, array $methods = null)
    {
        return $this->getMockBuilder($classPath)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
