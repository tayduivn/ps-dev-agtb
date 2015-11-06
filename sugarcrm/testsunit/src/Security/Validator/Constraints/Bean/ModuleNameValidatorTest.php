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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints\Bean;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Bean\ModuleName;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Bean\ModuleNameValidator;
use Sugarcrm\SugarcrmTests\Security\Validator\Constraints\AbstractConstraintValidatorTest;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\Bean\ModuleNameValidator
 *
 */
class ModuleNameValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new ModuleNameValidator();
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new ModuleName());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new ModuleName());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new \stdClass(), new ModuleName());
    }

    /**
     * @covers ::validate
     * @covers ::isValidModule
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value)
    {
        $constraint = new ModuleName();
        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    public function providerTestValidValues()
    {
        return array(
            array('Accounts'),
            array('Contacts'),
        );
    }

    /**
     * @covers ::validate
     * @covers ::isValidModule
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($value, $violation, $code)
    {
        $constraint = new ModuleName(array(
            'message' => 'testMessage',
        ));

        $this->validator->validate($value, $constraint);

        $this->buildViolation('testMessage')
            ->setInvalidValue($value)
            ->setParameter('%module%', $violation)
            ->setCode($code)
            ->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return array(
            array('FooBar', 'FooBar',  ModuleName::ERROR_UNKNOWN_MODULE),
            array('MailMerge', 'MailMerge',  ModuleName::ERROR_UNKNOWN_MODULE),
        );
    }
}
