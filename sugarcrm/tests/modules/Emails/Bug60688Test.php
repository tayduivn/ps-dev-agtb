<?php
//FILE SUGARCRM flav=pro ONLY
/* * *******************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 * ****************************************************************************** */

/**
 * Bug #60688
 * Role that sets email to owner read/owner write still allows non-admin user to email the contact or see email address.
 *
 * @ticket 60688
 */
class Bug60688Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var ACLRoles
     */
    protected $role = null;

    /**
     * @var Email
     */
    protected $email = null;

    /**
     * @var Account
     */
    protected $contact = null;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true));
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->email = BeanFactory::newBean('Emails');

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->email2 = 'bug60688role@example.com';
        $this->contact->created_by = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->contact->save();

        $this->role = BeanFactory::newBean('ACLRoles');
        $this->role->name = 'bug60688role';
        $this->role->description = 'Temp role.';
        $this->role->save();

        $this->role->load_relationship('users');
        $this->role->users->add($GLOBALS['current_user']);
    }

    public function tearDown()
    {
        $this->role->mark_deleted($this->role->id);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    /**
     * @group 60688
     */
    public function testEmptyEmailLinkForDisabledAccess()
    {
        $aclField = new ACLField();
        // Primary email address for Contact module
        $aclField->setAccessControl($this->contact->module_name, $this->role->id, 'email1', ACL_OWNER_READ_WRITE);
        // Alternative email address for Contact module
        $aclField->setAccessControl($this->contact->module_name, $this->role->id, 'email2', ACL_OWNER_READ_WRITE);

        $aclField->loadUserFields(
            $this->contact->module_name,
            $this->contact->object_name,
            $GLOBALS['current_user']->id,
            true
        );

        $actualEmailLink = $this->email->getNamePlusEmailAddressesForCompose(
            $this->contact->module_name,
            array($this->contact->id)
        );

        $this->assertEmpty($actualEmailLink, 'E-mail should be empty. We disabled both primary and secondary e-mails with ACL.');
    }

    /**
     * @group 60688
     */
    public function testAlternativeEmailLinkWhenPrimaryDisabled()
    {
        $aclField = new ACLField();
        // Primary email address for Contact module
        $aclField->setAccessControl($this->contact->module_name, $this->role->id, 'email1', ACL_OWNER_READ_WRITE);

        $aclField->loadUserFields(
            $this->contact->module_name,
            $this->contact->object_name,
            $GLOBALS['current_user']->id,
            true
        );

        $actualEmailLink = $this->email->getNamePlusEmailAddressesForCompose(
            $this->contact->module_name,
            array($this->contact->id)
        );

        $this->assertContains($this->contact->email2, $actualEmailLink, 'Should get secondary e-mail. Primary was disabled with ACL.');
    }
}
