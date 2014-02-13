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

require_once 'modules/MailMerge/merge_query.php';
require_once 'modules/MailMerge/controller.php';


/**
 * RS-147: Prepare MailMerge Module.
 */
class RS147Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array Beans created in tests.
     */
    protected static $createdBeans = array();

    /**
     * @var DBManager
     */
    protected static $db;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
        self::$db = DBManagerFactory::getInstance();
    }

    public static function tearDownAfterClass()
    {
        $_REQUEST = array();
        foreach (self::$createdBeans as $bean) {
            $bean->mark_deleted($bean->id);
        }
        self::$createdBeans = array();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testSearch()
    {
        $bean = BeanFactory::getBean('Contacts');
        $bean->first_name = 'RS147Test';
        $bean->save(false);
        array_push(self::$createdBeans, $bean);
        $_REQUEST = array(
            'term' => 'RS147Test',
            'qModule' => 'Contacts'
        );
        $controller = new MailMergeController();
        $this->expectOutputRegex('/"value":"RS147Test"/');
        $controller->action_search();
    }

    public function testModulesMerge()
    {
        $bean = BeanFactory::getBean('Contacts');
        $bean->save(false);
        $merge = BeanFactory::getBean('Notes');
        $merge->save(false);
        $bean->load_relationship('notes');
        $bean->notes->add($merge);
        array_push(self::$createdBeans, $bean, $merge);
        $query = get_merge_query($bean, 'Notes', $merge->id);
        $result = self::$db->query($query);
        $cnt = 0;
        while ($row = self::$db->fetchByAssoc($result)) {
            $cnt++;
        }
        $this->assertEquals(0, $cnt);
    }

    /**
     * @param string $to Module name to get info.
     * @param string $from Related module name to get info.
     * @param array $fieldsFrom Additional fields to initialize.
     * @param array $fieldsTo Additional fields to initialize.
     * @param string $rel Related field to connect to modules.
     * @dataProvider provider
     */
    public function testMerge($to, $from, $fieldsTo, $fieldsFrom, $rel)
    {
        $bean = BeanFactory::getBean($to);
        foreach ($fieldsTo as $field => $value) {
            $bean->$field = $value;
        }
        $bean->save(false);
        $merge = BeanFactory::getBean($from);
        foreach ($fieldsFrom as $field => $value) {
            $merge->$field = $value;
        }
        $merge->save(false);
        array_push(self::$createdBeans, $bean, $merge);
        $bean->load_relationship($rel);
        $bean->$rel->add($merge);
        $query = get_merge_query($bean, $from, $merge->id);
        $result = self::$db->query($query);
        $cnt = 0;
        while ($row = self::$db->fetchByAssoc($result)) {
            $cnt++;
        }
        $this->assertEquals(1, $cnt);
    }

    public function provider()
    {
        return array(
            array(
                'Contacts',
                'Accounts',
                array(),
                array(),
                'accounts'
            ),
            array(
                'Contacts',
                'Opportunities',
                array(),
                array(),
                'opportunities'
            ),
            array(
                'Contacts',
                'Cases',
                array(),
                array(),
                'cases'
            ),
            array(
                'Contacts',
                'Bugs',
                array(),
                array(),
                'bugs'
            ),
            array(
                'Contacts',
                'Quotes',
                array(),
                array(),
                'quotes'
            ),
            array(
                'Opportunities',
                'Accounts',
                array(),
                array(),
                'accounts'
            ),
            array(
                'Accounts',
                'Opportunities',
                array(),
                array(),
                'opportunities'
            )
        );
    }
}
