<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

/**
 * @covers SugarEmailAddress
 */
class SugarEmailAddressTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var SugarEmailAddress */
    private $ea;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    protected function setUp()
    {
        $this->ea = BeanFactory::getBean('EmailAddresses');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();

        parent::tearDownAfterClass();
    }

    public function testAddressesAreZeroBased()
    {
        // make sure that initially there are no addresses
        $this->assertCount(0, $this->ea->addresses);

        $this->ea->addAddress('test@example.com');
        $this->ea->addAddress('test@example.com');

        // make sure duplicate address is replaced
        $this->assertCount(1, $this->ea->addresses);

        reset($this->ea->addresses);
        $this->assertEquals(0, key($this->ea->addresses), 'Email addresses is not a 0-based array');
    }
}
