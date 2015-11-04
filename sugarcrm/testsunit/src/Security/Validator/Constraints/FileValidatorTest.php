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

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\File;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\FileValidator;
use Sugarcrm\SugarcrmTests\Security\Validator\Constraints\AbstractConstraintValidatorTest;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Validator\Constraints\FileValidator
 *
 */
class FileValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new FileValidator();
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestValidValues
     */
    public function testValidValues($value, array $baseDirs, $expected)
    {
        $constraint = new File(array(
            'baseDirs' => $baseDirs,
        ));
        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
        $this->assertSame($expected, $constraint->getFormattedReturnValue());
    }

    public function providerTestValidValues()
    {
        return array(
            array(
                dirname(__FILE__) . '/../../../../../modules/Accounts/vardefs.php',
                array(SUGAR_BASE_DIR),
                SUGAR_BASE_DIR . '/modules/Accounts/vardefs.php',
            ),
            array(
                dirname(__FILE__) . '/../../../../../modules/../modules/Accounts/vardefs.php',
                array(SUGAR_BASE_DIR),
                SUGAR_BASE_DIR . '/modules/Accounts/vardefs.php',
            ),
            array(
                '/etc/passwd',
                array('/etc', '/foobar'),
                '/etc/passwd',
            ),
        );
    }

    /**
     * @covers ::validate
     * @dataProvider providerTestInvalidValues
     */
    public function testInvalidValues($value, array $baseDirs, $code, $msg)
    {
        $constraint = new File(array(
            'message' => 'testMessage',
            'baseDirs' => $baseDirs,
        ));

        $this->validator->validate($value, $constraint);

        $this->buildViolation('testMessage')
            ->setParameter('%msg%', $msg)
            ->setCode($code)
            ->setInvalidValue($value)
            ->assertRaised();
    }

    public function providerTestInvalidValues()
    {
        return array(
            array(
                array('blah'),
                array(SUGAR_BASE_DIR),
                File::ERROR_STRING_REQUIRED,
                'string expected',
            ),
            array(
                'modules/Foobar/vardefs.php',
                array(SUGAR_BASE_DIR),
                File::ERROR_FILE_NOT_FOUND,
                'file not found',
            ),
            array(
                'modules/Accounts/vardefs.php' . chr(0) . '.gif',
                array(SUGAR_BASE_DIR),
                File::ERROR_NULL_BYTES,
                'null bytes detected',
            ),
            array(
                '/etc/passwd',
                array(SUGAR_BASE_DIR),
                File::ERROR_OUTSIDE_BASEDIR,
                'file outside basedir',
            ),
        );
    }
}
