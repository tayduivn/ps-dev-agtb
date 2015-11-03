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

namespace Sugarcrm\SugarcrmTestsUnit\Security\InputValidation;

use Sugarcrm\Sugarcrm\Security\InputValidation\Superglobals;
use Sugarcrm\Sugarcrm\Security\Validator\Validator;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\InputValidation\Superglobals
 *
 */
class SuperglobalsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Superglobals
     */
    protected $globals;

    /**
     * Fixture $_GET values
     * @var array
     */
    protected $inputGet = array(
        'batman' => 'robin',
        'superman' => array('fly' => 'away'),
    );

    /**
     * Fixture $_POST values
     * @var array
     */
    protected $inputPost = array(
        'batman' => 'catwoman',
        'green' => array('lantarn' => 'man'),
    );

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->globals = new Superglobals($this->inputGet, $this->inputPost);
    }

    /**
     * @covers ::setRawGet
     * @covers ::setRawPost
     * @covers ::getRawGet
     * @covers ::getRawPost
     * @covers ::getRawRequest
     * @covers ::hasRawGet
     * @covers ::hasRawPost
     * @covers ::hasRawRequest
     */
    public function testGetPostMerge()
    {
        // start situation
        $this->assertSame($this->inputGet, TestReflection::getProtectedValue($this->globals, 'rawGet'));
        $this->assertSame($this->inputPost, TestReflection::getProtectedValue($this->globals, 'rawPost'));


        // set $_GET which is not present in $_POST and should reflect in $_REQUEST
        $this->globals->setRawGet('more', 'beer');

        $this->assertTrue($this->globals->hasRawGet('more'));
        $this->assertSame('beer', $this->globals->getRawGet('more'));

        $this->assertFalse($this->globals->hasRawPost('more'));
        $this->assertNull($this->globals->getRawPost('more'));

        $this->assertTrue($this->globals->hasRawRequest('more'));
        $this->assertSame('beer', $this->globals->getRawRequest('more'));


        // set $_POST which should overwrite the one from $_GET in $_REQUEST
        $this->globals->setRawPost('more', 'coke');
        $this->assertSame('beer', $this->globals->getRawGet('more'));
        $this->assertSame('coke', $this->globals->getRawPost('more'));
        $this->assertSame('coke', $this->globals->getRawRequest('more'));

        // test defaults for unknown keys
        $this->assertSame('default', $this->globals->getRawGet('doesnotexist', 'default'));
        $this->assertSame('default', $this->globals->getRawPost('doesnotexist', 'default'));
        $this->assertSame('default', $this->globals->getRawRequest('doesnotexist', 'default'));
    }
}
