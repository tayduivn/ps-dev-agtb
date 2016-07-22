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

require_once 'tests/SugarTestACLUtilities.php';

/**
 * Class ACLRolesTest
 *
 * Class containing tests for ACLRole bean.
 */
class ACLRolesTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var ACLRole
     */
    private $role;

    protected function setUp()
    {
        parent::setUp();

        $this->user = SugarTestUserUtilities::createAnonymousUser();

        $this->role = SugarTestACLUtilities::createRole('test-role', array('Accounts'), array('access'));
        $this->role->load_relationship('users');
        $this->role->users->add($this->user->id);
        $this->role->save();

        // Some manipulation to set user date_modified to some date in the past.
        $this->user->setModifiedDate(TimeDate::getInstance()->asDb(new SugarDateTime('2016-01-01')));
        $oldUpdateDateModified = $this->user->update_date_modified;
        $this->user->update_date_modified = false;
        $this->user->save();
        $this->user->update_date_modified = $oldUpdateDateModified;
    }

    protected function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestACLUtilities::tearDown();
        parent::tearDown();
    }

    /**
     * Test to check that when we unlink user from some role, user hash is updated.
     */
    public function testUserHashChangedWhenUserUnlinked()
    {
        $oldUserMDHash = $this->user->getUserMDHash();

        $this->role->load_relationship('users');
        $this->role->users->delete($this->role->id, $this->user->id);

        $this->user->retrieve();
        $this->assertNotEquals($oldUserMDHash, $this->user->getUserMDHash());
    }
}
