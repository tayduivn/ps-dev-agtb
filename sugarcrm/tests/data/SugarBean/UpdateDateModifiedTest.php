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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'data/Relationships/One2MBeanRelationship.php';

class UpdateDateModifiedTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $hook = array(
        'Accounts',
        'before_save',
        array(1, 'Accounts::before_save', __FILE__, 'UpdateDateModifiedTestHook', 'beforeSave')
    );

    public static function setUpBeforeClass()
    {
        global $disable_date_format;
        $disable_date_format = true;

        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('timedate');
    }

    public static function tearDownAfterClass()
    {
        unset($GLOBALS['PHPUNIT_BEAN_DATE_MODIFIED']);

        self::tearDownHook();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        parent::tearDownAfterClass();
    }

    private static function setUpHook()
    {
        LogicHook::refreshHooks();
        call_user_func_array('check_logic_hook_file', self::$hook);
    }

    private static function tearDownHook()
    {
        call_user_func_array('remove_logic_hook', self::$hook);
    }

    public function testUpdateDateModified()
    {
        global $timedate;
        unset($GLOBALS['PHPUNIT_BEAN_DATE_MODIFIED']);

        $original_date = '2015-06-02 11:24:00';
        $current_date = '2015-08-13 18:13:00';

        $time = $timedate->fromString($current_date);
        $timedate->setNow($time);

        $account = SugarTestAccountUtilities::createAccount();
        $account->date_modified = $original_date;
        $account->update_date_modified = false;
        $account->save();

        $account = BeanFactory::getBean('Accounts', $account->id, array('use_cache' => false));
        self::setUpHook();
        $account->save();
        $this->assertEquals(
            $current_date,
            $GLOBALS['PHPUNIT_BEAN_DATE_MODIFIED'],
            'Logic hook should have been called with the current date'
        );

        $account = BeanFactory::getBean('Accounts', $account->id, array('use_cache' => false));
        $this->assertEquals(
            $original_date,
            $account->date_modified,
            'The modification date should have remained unchanged'
        );
    }
}

class UpdateDateModifiedTestHook
{
    public function beforeSave($bean)
    {
        $GLOBALS['PHPUNIT_BEAN_DATE_MODIFIED'] = $bean->date_modified;
        $bean->update_date_modified = false;
    }
}
