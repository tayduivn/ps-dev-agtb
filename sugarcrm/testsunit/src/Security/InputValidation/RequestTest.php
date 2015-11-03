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

use Sugarcrm\Sugarcrm\Security\Validator\Validator;
use Sugarcrm\Sugarcrm\Security\InputValidation\Request;
use Sugarcrm\Sugarcrm\Security\InputValidation\Superglobals;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Tests\Fixtures\ConstraintA;
use Symfony\Component\Validator\Tests\Fixtures\FailingConstraint;
use Psr\Log\LoggerInterface;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\InputValidation\Request
 *
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->validator = Validator::create();
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
    }

    /**
     * @covers ::getValidInput
     * @covers ::getValidInputGet
     * @covers ::getValidInputPost
     * @covers ::getValidInputRequest
     * @dataProvider providerTestGetValidInput
     */
    public function testGetValidInput(array $get, array $post, $key, $call, $constraint, $default, $expected)
    {
        $superglobals = new Superglobals($get, $post);
        $request = new Request($superglobals, $this->validator, $this->logger);
        $this->assertSame($expected, $request->$call($key, $constraint, $default));
    }

    public function providerTestGetValidInput()
    {
        return array(
            // $_GET test
            array(
                array('foo' => 'VALID'),
                array(),
                'foo',
                'getValidInputGet',
                new ConstraintA(),
                null,
                'VALID',
            ),
            // $_POST test
            array(
                array(),
                array('foo' => 'VALID'),
                'foo',
                'getValidInputPost',
                new ConstraintA(),
                null,
                'VALID',
            ),
            // $_REQUEST test
            array(
                array('foo' => 'INVALID'),
                array('foo' => 'VALID'),
                'foo',
                'getValidInputRequest',
                new ConstraintA(),
                null,
                'VALID',
            ),
            // Test without null constraint
            array(
                array('foo' => 'VALID'),
                array(),
                'foo',
                'getValidInputGet',
                null,
                null,
                'VALID',
            ),
            // Test default
            array(
                array(),
                array(),
                'foo',
                'getValidInputGet',
                new ConstraintA(),
                'default',
                'default',
            ),
        );
    }

    /**
     * @covers ::getValidInput
     * @expectedException \Sugarcrm\Sugarcrm\Security\InputValidation\Exception\ViolationException
     */
    public function testGetValidInputViolationException()
    {
        $superglobals = new Superglobals(array('foo' => 'bar'), array());
        $request = new Request($superglobals, $this->validator, $this->logger);
        $request->getValidInput(Superglobals::GET, 'foo', new FailingConstraint());
    }

    /**
     * @covers ::getValidInput
     * @expectedException \Sugarcrm\Sugarcrm\Security\InputValidation\Exception\SuperglobalException
     */
    public function testGetValidInputSuperglobalException()
    {
        $superglobals = new Superglobals(array(), array());
        $request = new Request($superglobals, $this->validator, $this->logger);
        $request->getValidInput('foo', 'bar');
    }

    /**
     * @covers ::getValidInput
     */
    public function testSoftFailNoException()
    {
        $superglobals = new Superglobals(array('foo' => 'VALID'), array());
        $request = new Request($superglobals, $this->validator, $this->logger);
        $request->setSoftFail(true);
        $request->getValidInput(Superglobals::GET, 'foo', array(
            new FailingConstraint(),
            new ConstraintA(),
            new FailingConstraint(),
        ));
        $this->assertEquals(2, count($request->getViolations()));
    }
}
