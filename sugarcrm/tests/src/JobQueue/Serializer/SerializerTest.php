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

namespace Sugarcrm\SugarcrmTests\JobQueue\Serializer;

use Psr\Log\NullLogger;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator\Base64;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator\JSON;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator\PHPSerialize;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator\PHPSerializeSafe;
use Sugarcrm\Sugarcrm\JobQueue\Workload\Workload;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;

class SerializerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var WorkloadInterface
     */
    protected $workload;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', [true, 1]);
        $this->workload = new Workload('testRoute', ['key' => 'value', 2, 3], ['attribute']);
    }

    public function tearDown()
    {
        \SugarTestHelper::tearDown();
    }

    /**
     * Test decorators.
     * @dataProvider providerDecorator
     */
    public function testDecorator($decorator, $data)
    {
        /* @var $decorator \Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator\DecoratorInterface */
        $serializedData = $decorator->decorate($data, $this->workload);
        $actualData = $decorator->undecorate($serializedData);

        $this->assertEquals($data, $actualData);
    }

    public function providerDecorator()
    {
        $workload = new Workload('testRoute', ['key' => 'value', 2, 3], ['attribute']);
        $logger = new NullLogger();
        return [
            [new Base64($logger), 'test'],
            [new JSON($logger), 'test'],
            [new PHPSerialize($logger), $workload],
            [new PHPSerializeSafe($logger), $workload],
            [new PHPSerialize($logger, new Base64($logger)), $workload],
            [new PHPSerializeSafe($logger, new Base64($logger)), $workload],
            [new PHPSerializeSafe($logger, new Base64($logger, new JSON($logger))), $workload],
        ];
    }
}
