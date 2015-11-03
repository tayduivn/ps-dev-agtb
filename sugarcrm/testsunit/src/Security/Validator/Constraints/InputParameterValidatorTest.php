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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints;

use Sugarcrm\Sugarcrm\Security\InputValidation\Superglobals;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\InputParameters;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\InputParametersValidator;
use Sugarcrm\SugarcrmTests\Security\Validator\Constraints\AbstractConstraintValidatorTest;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\InputParametersValidator
 *
 */
class InputParameterValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new InputParametersValidator();
    }

    /**
     * @coversNothing
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testExpectValidInputType()
    {
        $constraint = new InputParameters(array(
            'inputType' => 'foobar',
        ));

        $this->validator->validate('xyz', $constraint);
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($type, $value)
    {
        $constraint = new InputParameters(array(
            'inputType' => $type,
        ));

        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    public function providerTestValidValues()
    {
        return array(
            array(Superglobals::GET, 'helloworld'),
            array(Superglobals::POST, array('foo', 'bar')),
            array(Superglobals::REQUEST, array(array('foo', 'bar'))),
        );
    }

    /**
     * @covers ::validate
     * @covers ::validateRecursive
     * @covers ::getErrorCode
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($type, $code, $value, $msg, array $expectedViolations)
    {
        $constraint = new InputParameters(array(
            $msg => 'testMessage',
            'inputType' => $type,
        ));

        $this->validator->validate($value, $constraint);

        foreach ($expectedViolations as $expectedViolation) {

            if (empty($violations)) {
                $violations = $this->buildViolation('testMessage')
                    ->setCode($code)
                    ->setInvalidValue($expectedViolation)
                    ->setParameter('%type%', $type);
            } else {
                $violations = $violations->buildNextViolation('testMessage')
                    ->setCode($code)
                    ->setInvalidValue($expectedViolation)
                    ->setParameter('%type%', $type);
            }
        }

        $violations->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return array(

            // generic non-string tests
            array(
                Superglobals::GET,
                InputParameters::ERROR_GET,
                15,
                'msgGeneric',
                array(15),
            ),
            array(
                Superglobals::POST,
                InputParameters::ERROR_POST,
                true,
                'msgGeneric',
                array(true),
            ),
            array(
                Superglobals::REQUEST,
                InputParameters::ERROR_REQUEST,
                null,
                'msgGeneric',
                array(null),
            ),
            array(
                Superglobals::GET,
                InputParameters::ERROR_GET,
                array('good1', 15, true, null, 'good2'),
                'msgGeneric',
                array(15, true, null),
            ),
            array(
                Superglobals::POST,
                InputParameters::ERROR_POST,
                array('good1', 15, true, null, 'good2'),
                'msgGeneric',
                array(15, true, null),
            ),
            array(
                Superglobals::REQUEST,
                InputParameters::ERROR_REQUEST,
                array('good1', 15, true, null, 'good2'),
                'msgGeneric',
                array(15, true, null),
            ),

            // null byte tests
            array(
                Superglobals::REQUEST,
                InputParameters::ERROR_REQUEST,
                'test.php' . chr(0) . '.gif',
                'msgNullBytes',
                array('test.php' . chr(0) . '.gif'),
            ),
            array(
                Superglobals::REQUEST,
                InputParameters::ERROR_REQUEST,
                chr(0) . '.gif',
                'msgNullBytes',
                array(chr(0) . '.gif'),
            ),
            array(
                Superglobals::REQUEST,
                InputParameters::ERROR_REQUEST,
                'test.php' . chr(0),
                'msgNullBytes',
                array('test.php' . chr(0)),
            ),
        );
    }
}
