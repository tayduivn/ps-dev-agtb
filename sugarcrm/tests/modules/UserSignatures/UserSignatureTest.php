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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */
require_once 'modules/UserSignatures/UserSignature.php';

class UserSignatureTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $createdSignatures = array();

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        if (!empty(static::$createdSignatures)) {
            $ids = implode("','", static::$createdSignatures);
            $GLOBALS['db']->query("DELETE FROM users_signatures WHERE id IN ('{$ids}')");
        }
        parent::tearDownAfterClass();
    }

    public function testSave_UserIdIsEmpty_CurrentUserIdIsUsed()
    {
        $signature = BeanFactory::newBean('UserSignatures');
        static::$createdSignatures[] = $signature->save();
        $this->assertEquals(
            $GLOBALS['current_user']->id,
            $signature->user_id,
            "user_id should match the current user's ID"
        );
        $this->assertEquals(
            $GLOBALS['current_user']->id,
            $signature->created_by,
            'Should have been created by the current user'
        );
    }

    public function testSave_UserIdIsNotEmptyAndCreatedByIsEmpty_UserIdIsUsedForCreatedBy()
    {
        $expected = create_guid();
        $signature = BeanFactory::newBean('UserSignatures');
        $signature->user_id = $expected;
        static::$createdSignatures[] = $signature->save();
        $this->assertEquals($expected, $signature->user_id, "user_id should not have changed");
        $this->assertEquals($expected, $signature->created_by, 'Should match user_id');
    }

    public function testSave_CreatedByDoesNotMatchUserId_UserIdIsUsedForCreatedBy()
    {
        $expected = create_guid();
        $signature = BeanFactory::newBean('UserSignatures');
        $signature->user_id = $expected;
        $signature->created_by = create_guid();
        static::$createdSignatures[] = $signature->save();
        $this->assertEquals($expected, $signature->created_by, 'Should match user_id');
    }
}

