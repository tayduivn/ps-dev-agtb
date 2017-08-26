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

namespace Sugarcrm\SugarcrmTestsUnit;

/**
 * Class TestMockHelper
 *
 * Helper class to create Mock Object, wrapper of original \PHPUnit_Framework_TestCase methods
 *
 * @package Sugarcrm\SugarcrmTestsUnit
 */
class TestMockHelper
{
    /**
     * Helper method, creates a mock object using a fluent interface.
     *
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param string $className
     * @param array|null $methods
     * @param bool $disableOriginalConstructor
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */

    public static function getObjectMock(
        \PHPUnit_Framework_TestCase $testCase,
        $className,
        array $methods = null,
        $disableOriginalConstructor = true
    ) {
        $mockObject = $testCase->getMockBuilder($className);

        if ($disableOriginalConstructor) {
            $mockObject->disableOriginalConstructor();
        }

        $mockObject->setMethods($methods);
        return $mockObject->getMock();
    }

    /**
     *
     * helper method, create mock for abstract class
     *
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param $className
     * @param array|null $methods
     * @param bool $disableOriginalConstructor
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public static function getMockForAbstractClass(
        \PHPUnit_Framework_TestCase $testCase,
        $className,
        array $methods = null,
        $disableOriginalConstructor = true
    ) {
        $mockObject = $testCase->getMockBuilder($className);

        if ($disableOriginalConstructor) {
            $mockObject->disableOriginalConstructor();
        }

        $mockObject->setMethods($methods);
        return $mockObject->getMockForAbstractClass();
    }

    /**
     * Returns a test double for the specified class.
     *
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param string $originalClassName
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     *
     * @throws PHPUnit_Framework_Exception
     *
     */
    public static function createMock(\PHPUnit_Framework_TestCase $testCase, $originalClassName)
    {
        return TestReflection::callProtectedMethod($testCase, 'createMock', array($originalClassName));
    }

    /**
     * Returns a partial test double for the specified class.
     *
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param string $originalClassName
     * @param array  $methods
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     *
     * @throws PHPUnit_Framework_Exception
     */

    public static function createPartialMock(\PHPUnit_Framework_TestCase $testCase, $originalClassName, array $methods)
    {
        return TestReflection::callProtectedMethod($testCase, 'createPartialMock', array($originalClassName, $methods));
    }
}
