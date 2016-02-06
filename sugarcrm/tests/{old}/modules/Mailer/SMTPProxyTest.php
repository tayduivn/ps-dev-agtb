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


/**
 * @group email
 * @group mailer
 */
class SMTPProxyTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $logger;

    /**
     * Stores the logger so it can be restored.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$logger = $GLOBALS['log'];
    }

    /**
     * Restores the logger.
     */
    public static function tearDownAfterClass()
    {
        $GLOBALS['log'] = static::$logger;
    }

    public function noErrorProvider()
    {
        return array(
            array(array()),
            array(null),
            array(''),
        );
    }

    public function hasErrorProvider()
    {
        return array(
            array(
                'warn',
                array('error' => 'Error occurred'),
            ),
            array(
                'warn',
                'Error occurred',
            ),
            array(
                'fatal',
                array(
                    'error' => 'Error occurred',
                    'errno' => '421',
                ),
            ),
            array(
                'fatal',
                array(
                    'error' => 'Error occurred',
                    'smtp_code' => '421',
                ),
            ),
            array(
                'fatal',
                array(
                    'error' => 'Error occurred',
                    'errno' => '421',
                    'smtp_code' => '421',
                ),
            ),
        );
    }

    public function proxiedMethodValue()
    {
        return array(
            array(true),
            array(false),
            array(50),
            array('some value'),
        );
    }

    /**
     * @dataProvider noErrorProvider
     * @param $error
     */
    public function testHandleError_NothingIsLogged($error)
    {
        $GLOBALS['log'] = $this->createPartialMock('SugarMockLogger', array('__call'));
        $GLOBALS['log']->expects($this->never())->method('__call');

        SugarTestReflection::callProtectedMethod(new SMTPProxy(), 'handleError', array($error));
    }

    /**
     * @dataProvider hasErrorProvider
     * @param $level
     * @param $error
     */
    public function testHandleError_ErrorIsLogged($level, $error)
    {
        $GLOBALS['log'] = $this->createPartialMock('SugarMockLogger', array('__call'));
        $GLOBALS['log']->expects($this->any())->method('__call')->with($this->equalTo($level));

        SugarTestReflection::callProtectedMethod(new SMTPProxy(), 'handleError', array($error));
    }

    public function testCall_CallsHandleError()
    {
        $smtp = $this->createPartialMock('SMTP', array('connect'));
        $smtp->expects($this->once())->method('connect');

        $proxy = $this->createPartialMock('SMTPProxy', array('handleError'));
        $proxy->expects($this->once())->method('handleError');
        SugarTestReflection::setProtectedValue($proxy, 'smtp', $smtp);

        $proxy->connect('localhost');
    }

    /**
     * @dataProvider proxiedMethodValue
     * @param $value
     */
    public function testCall_ReturnsTheValueFromTheProxiedMethod($value)
    {
        $smtp = $this->createPartialMock('SMTP', array('connect'));
        $smtp->expects($this->once())->method('connect')->willReturn($value);

        $proxy = $this->createPartialMock('SMTPProxy', array('handleError'));
        $proxy->expects($this->once())->method('handleError');
        SugarTestReflection::setProtectedValue($proxy, 'smtp', $smtp);

        $actual = $proxy->connect('localhost');
        $this->assertEquals($value, $actual);
    }
}
