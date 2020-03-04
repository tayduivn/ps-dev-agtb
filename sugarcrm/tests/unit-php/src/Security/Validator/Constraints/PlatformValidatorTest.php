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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Validator\Constraints;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Platform;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\PlatformValidator;
use Sugarcrm\SugarcrmTests\Security\Validator\Constraints\AbstractConstraintValidatorTest;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\PlatformValidator
 */
class PlatformValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new PlatformValidator(array(
            'base' => 0,
            'mobile' => 1,
            'portal' => 2,
            'custom' => 3,
        ));
    }

    /**
     * @covers ::validate
     */
    public function testNullIsValid()
    {
        $this->validator->validate(null, new Platform());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     */
    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new Platform());
        $this->assertNoViolation();
    }

    /**
     * @covers ::validate
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new \stdClass(), new Platform());
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value)
    {
        $this->validator->validate($value, new Platform());
        $this->assertNoViolation();
    }

    public function providerTestValidValues()
    {
        return array(
            array('base'),
            array('mobile'),
            array('portal'),
            array('custom'),
        );
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestInvalidFormat
     */
    public function testInvalidFormat($value, $reason)
    {
        $constraint = new Platform(array(
            'message' => 'testMessage',
        ));

        $this->validator->validate($value, $constraint);

        $this
            ->buildViolation('testMessage')
            ->setCode(Platform::ERROR_INVALID_PLATFORM_FORMAT)
            ->setInvalidValue($value)
            ->setParameters(array('%platform%' => $value, '%reason%' => $reason))

            // we will always hit unknown platforms
            ->buildNextViolation('testMessage')
            ->setCode(Platform::ERROR_INVALID_PLATFORM)
            ->setInvalidValue($value)
            ->setParameters(array('%platform%' => $value, '%reason%' => 'unknown platform'))

            ->assertRaised();
    }

    public function providerTestInvalidFormat()
    {
        return array(
            array(
                str_repeat('x', 128),
                'maximum length of 127 characters exceeded',
            ),
            array(
                'abc-123-ABC_890$',
                'invalid characters (a-z, 0-9, dash and underscore allowed)',
            ),
        );
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestUnknownPlatform
     */
    public function testUnknownPlatform($value)
    {
        $constraint = new Platform(array(
            'message' => 'testMessage',
        ));

        $this->validator->validate($value, $constraint);

        $this
            ->buildViolation('testMessage')
            ->setCode(Platform::ERROR_INVALID_PLATFORM)
            ->setInvalidValue($value)
            ->setParameters(array('%platform%' => $value, '%reason%' => 'unknown platform'))
            ->assertRaised();
    }

    public function providerTestUnknownPlatform()
    {
        return array(
            array('foo'),
            array('bar'),
        );
    }
}
