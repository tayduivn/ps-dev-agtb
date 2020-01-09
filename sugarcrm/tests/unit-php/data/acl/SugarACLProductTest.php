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

namespace Sugarcrm\SugarcrmTestsUnit\data\acl;

use PHPUnit\Framework\TestCase;
use ProductTemplate;

/**
 * @coversDefaultClass \SugarACLProduct
 */
class SugarACLProductTest extends TestCase
{
    protected function setUp()
    {
        \BeanFactory::setBeanClass('ProductTemplates', ProductMock::class);
    }

    protected function tearDown()
    {
        \BeanFactory::unsetBeanClass('ProductTemplates');
    }

    public function checkAccessProvider()
    {
        return [
            ['ProductCategories', 'team_security', true],
            ['ProductCategories', 'access', true],
            ['ProductTyes', 'list', true],
            ['ProductTyes', 'view', true],
            ['Manufacturers', 'create', false],
            ['Manufacturers', 'edit', false],
            ['Manufacturers', 'field', true],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkAccessProvider
     */
    public function testCheckAccess($module, $view, $result)
    {
        $acl = new \SugarACLProduct();
        $this->assertSame($result, $acl->checkAccess($module, $view, array()));
    }
}

class ProductMock extends \ProductTemplate
{
    public $result = [
        'access' => true,
        'list' => true,
        'view' => true,
        'edit' => false,
        'create' => false,
    ];

    public function __construct()
    {
    }

    public function ACLAccess($view, $context)
    {
        return isset($this->result[$view]) ? $this->result[$view] : false;
    }
}
