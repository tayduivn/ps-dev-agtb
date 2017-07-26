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

namespace Sugarcrm\SugarcrmTests\Security\Validator\Constraints;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\ArrayRecursive;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\ArrayRecursiveValidator;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\ArrayRecursiveValidator
 *
 */
class ArrayRecursiveValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new ArrayRecursiveValidator();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $constraint = new ArrayRecursive(array(
            'constraints' => array(new NotBlank()),
        ));
        $this->validator->validate(null, $constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyArrayIsValid()
    {
        $constraint = new ArrayRecursive(array(
            'constraints' => array(new NotBlank()),
        ));
        $this->validator->validate('', $constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsArrayType()
    {
        $constraint = new ArrayRecursive(array(
            'constraints' => new NotBlank(),
        ));
        $this->validator->validate(new \stdClass(), $constraint);
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestArrayIsValid
     */
    public function testArrayIsValid($value, $expected)
    {
        $constraint = new ArrayRecursive(array(
            'constraints' => array(new NotBlank()),
        ));
        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
        $this->assertSame($expected, $constraint->getFormattedReturnValue());
    }

    public function providerTestArrayIsValid()
    {
        return array(
            array(
                array (
                    'roles' =>
                        array (
                            'ecaf8d4e-6e58-11e7-960b-56847afe9799' => '803a36bc-6e5f-11e7-a320-a45e60e64465',
                        ),
                ),
            array (
                    'roles' =>
                        array (
                            'ecaf8d4e-6e58-11e7-960b-56847afe9799' => '803a36bc-6e5f-11e7-a320-a45e60e64465',
                        ),
                ),
            ),
        );
    }
}
