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
namespace Sugarcrm\SugarcrmTestUnit\IdentityProvider\Authentication\UserProvider;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarSAMLUserProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarSAMLUserProvider
 */
class SugarSAMLUserProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::loadUserByUsername
     */
    public function testLoadUserByUsername()
    {
        $samlUserProvider = new SugarSAMLUserProvider();
        $user = $samlUserProvider->loadUserByUsername('test-name');
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test-name', $user->getUsername());
    }
}
