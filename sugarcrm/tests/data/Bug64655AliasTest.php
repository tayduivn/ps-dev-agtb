<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

/**
 * @ticket 64655
 */
class Bug64655AliasTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Account
     */
    private $account;

    /**
     * 28 characters is the maximum allowed custom field name length
     *
     * @var string
     */
    private static $fieldName = 'bug64655_abcdefghijklmnopqrs';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, true));

        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('custom_field', array(
            'Users',
            array(
                'name' => self::$fieldName,
                'type' => 'varchar',
            ),
        ));

        // add custom field to the name format map under "x" alias
        $GLOBALS['dictionary']['User']['name_format_map'] = array(
            'x' => self::$fieldName . '_c',
        );
    }

    protected function setUp()
    {
        parent::setUp();

        // create regular user with custom field populated
        $user = $this->user = SugarTestUserUtilities::createAnonymousUser(false, 0);
        $user->{self::$fieldName . '_c'} = 'Custom Value';
        $user->save();

        // create account assigned to the user
        $account = $this->account = SugarTestAccountUtilities::createAccount();
        $account->assigned_user_id = $user->id;
        $account->save();
    }

    protected function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();

        parent::tearDownAfterClass();
    }

    public function testLongRelateAlias()
    {
        /** @var User */
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'x');

        $account = BeanFactory::retrieveBean('Accounts', $this->account->id, array(
            'use_cache' => false,
        ));
        $this->assertNotEmpty($account);

        // formatted assigned user name must contain the value of custom field
        $this->assertContains('Custom Value', $account->assigned_user_name);
    }
}
