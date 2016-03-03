<?php
class SugarLoggerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function startQueryDataProvider()
    {
        return array(
            array('Query:SELECT "test" FROM DUAL', array(), array()),
            array('Query:SELECT "test" FROM DUAL' . PHP_EOL . 'Params:["some-param"]', array('some-param'), array()),
            array(
                'Query:SELECT "test" FROM DUAL' . PHP_EOL . 'Params:["some-param"]' . PHP_EOL . 'Types:["param-type"]',
                array('some-param'),
                array('param-type')
            )
        );
    }

    /**
     * @dataProvider startQueryDataProvider
     */
    public function testStartQuery($expectedMessage, $params, $types)
    {
        $sugarLogger = $this->getMockBuilder('\Sugarcrm\Sugarcrm\Dbal\Logging\SugarLogger')
            ->setMethods(array('log'))
            ->getMock();
        $sugarLogger->expects($this->once())
            ->method('log')
            ->with($expectedMessage);
        $loggerMock = $this->getMockBuilder('LoggerManager')
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->any())
            ->method('wouldLog')
            ->will($this->returnValue(true));
        SugarTestReflection::setProtectedValue($sugarLogger, 'logger', $loggerMock);

        $sugarLogger->startQuery('SELECT "test" FROM DUAL', $params, $types);
    }
}
