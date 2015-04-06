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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch;

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
 *
 */
class GlobalSearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getHandlers
     * @covers ::registerHandlers
     * @covers ::__construct
     */
    public function testGetHandlers()
    {
        $sut = new GlobalSearch();
        $this->assertInstanceOf('Iterator', $sut->getHandlers());
        $this->assertCount(4, $sut->getHandlers());
    }

    /**
     * @covers ::getSupportedTypes
     */
    public function testGetSupportedTypes()
    {
        $supported = array(
            'varchar',
            'name',
            'text',
            'datetime',
            'date',
            'int',
            'phone',
            'url',
            'id',
            'exact',
            'longtext',
            'htmleditable_tinymce',
            'enum',
            'assigned_user_name',
            'email',
        );
        $sut = new GlobalSearch();
        $this->assertEquals($supported, $sut->getSupportedTypes());
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
     */
    protected function getGlobalSearchMock(array $methods = null)
    {
        return $this->getMockBuilder('Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
