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

namespace Sugarcrm\SugarcrmTest\Elasticsearch;

use Sugarcrm\Sugarcrm\Elasticsearch\Logger;

/**
 * Test for the Elastic Logger.
 */
class LoggerTest extends \Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test encoding data for log messages.
     * @param $inputData
     * @param $outputData
     * @dataProvider providerEncodeData
     */
    public function testEncodeData($inputData, $outputData)
    {
        $logger = new Logger(\LoggerManager::getLogger());
        $encoded = \SugarTestReflection::callProtectedMethod($logger, 'encodeData', array($inputData));
        $this->assertEquals($outputData, $encoded);
    }

    /**
     * Data provider to test encodeData().
     * @return array
     */
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
