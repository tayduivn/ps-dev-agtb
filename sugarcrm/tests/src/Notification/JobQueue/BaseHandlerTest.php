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

namespace Sugarcrm\SugarcrmTests\Notification\JobQueue;

require_once 'tests/src/Notification/JobQueue/NotificationSomeClass.php';

/**
 * Class BaseHandlerTest
 * @package Sugarcrm\SugarcrmTests\Notification\JobQueue
 */
class BaseHandlerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    public function possibleArguments()
    {
        $obj = new \NotificationSomeClassMock();
        return array(
            'true 5' => array(
                array(true, 5),
                array(null, array('', serialize(true)), array('', serialize(5)))
            ),
            '2.2' => array(
                array(2.2),
                array(null, array('', serialize(2.2)))
            ),
            'String null array' => array(
                array('String', null, array('1', '2')),
                array(
                    'some_user_id',
                    array('', serialize('String')),
                    array('', serialize(null)),
                    array('', serialize(array('1', '2')))
                )
            ),
            'true $object' => array(
                array(true, $obj),
                array(
                    'some_user_id',
                    array('', serialize(true)), array(__DIR__ . '/NotificationSomeClass.php', serialize($obj))
                )
            ),
        );
    }

    /**
     * @dataProvider possibleArguments
     * @param $arguments
     * @param $wrapped
     */
    public function testConstructor($arguments, $wrapped)
    {
        $reflect = new \ReflectionClass('Sugarcrm\SugarcrmTests\Notification\JobQueue\CustomHandlerReceiver');
        $reflect->newInstanceArgs($wrapped);

        $this->assertEquals($arguments, CustomHandlerReceiver::$arguments);
    }
}
