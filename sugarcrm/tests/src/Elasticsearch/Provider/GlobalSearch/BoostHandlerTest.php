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

namespace Sugarcrm\SugarcrmTest\Elasticsearch\Provider\GlobalSearch;

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\BoostHandler;

/**
 * BoostHandler tests
 */
class BoostHandlerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\BoostHandler::setWeighted
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\BoostHandler::getBoostedField
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\BoostHandler::getBoostValue
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\BoostHandler::normalizeBoost
     * @covers \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\BoostHandler::weight
     * @dataProvider dataProviderTestGetBoostedField
     * @group unit
     */
    public function testGetBoostedField($weighted, $field, array $defs, $type, $expected)
    {
        $bh = new BoostHandler();
        $bh->setWeighted($weighted);

        $this->assertEquals($expected, $bh->getBoostedField($field, $defs, $type));
    }

    public function dataProviderTestGetBoostedField()
    {
        return array(
            array(
                array(),
                'field.sub',
                array(),
                'foo',
                'field.sub^1',
            ),
            array(
                array(),
                'field.sub',
                array('full_text_search' => array()),
                'foo',
                'field.sub^1',
            ),
            array(
                array(),
                'field.sub',
                array('full_text_search' => array('boost' => 2.138)),
                'foo',
                'field.sub^2.14',
            ),
            array(
                array('foo' => 0.5),
                'field.sub',
                array('full_text_search' => array('boost' => 2.138)),
                'foo',
                'field.sub^1.07',
            ),
        );
    }
}
