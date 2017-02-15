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
namespace Sugarcrm\SugarcrmTestUnit\IdentityProvider\Authentication\Encoder;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Encoder\SugarPreAuthPassEncoder;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Encoder\SugarPreAuthPassEncoder
 */
class SugarPreAuthPassEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SugarPreAuthPassEncoder
     */
    protected $encoder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->encoder = new SugarPreAuthPassEncoder();
        parent::setUp();
    }

    /**
     * @covers ::encodePassword
     */
    public function testEncodePassword()
    {
        $password = 'test';
        $this->assertEquals(
            strtolower(md5($password)),
            $this->encoder->encodePassword($password, '', false)
        );
    }

    /**
     * @covers ::isPasswordValid
     */
    public function testIsPasswordValid()
    {
        $this->assertFalse($this->encoder->isPasswordValid('test', 'test', ''));
    }
}
