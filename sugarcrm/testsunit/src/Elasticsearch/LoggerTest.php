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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Elasticsearch\Logger;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Logger
 *
 */
class LoggerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers ::encodeData
     * @dataProvider providerEncodeData
     *
     * @param string $inputData
     * @param string $outputData
     */
    public function testEncodeData($inputData, $outputData)
    {
        $logger = $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Logger')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock(); 
        $encoded = TestReflection::callProtectedMethod($logger, 'encodeData', array($inputData));
        $this->assertEquals($outputData, $encoded);
    }

    public function providerEncodeData()
    {
        return array(
            array(
                "Hello world",
                "Hello world",
            ),
            array(
                array("1" => "Hello", "2" => "world"),
                '{"1":"Hello","2":"world"}',
            ),
        );
    }
}
