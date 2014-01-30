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

require_once 'modules/TeamNotices/TeamNotice.php';

/**
 * RS-78 Prepare TeamNotices Module.
 */
class RS78Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TeamNotice
     */
    protected $bean;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));

        $this->bean = BeanFactory::getBean('TeamNotices');
        $this->bean->name = 'RS78Test';
        $this->bean->description = 'RS78TestDesc';
        $this->bean->save();
    }

    protected function tearDown()
    {
        $this->bean->mark_deleted($this->bean->id);
        SugarTestHelper::tearDown();
    }

    public function testTeamNotices()
    {
        $this->bean->fill_in_additional_list_fields();
        $this->assertEquals('RS78TestDesc', $this->bean->description);
        $list = $this->bean->get_list_view_data();
        $this->assertNotEmpty($list);
        $where = $this->bean->build_generic_where_clause('RS78Test');
        $this->assertNotEmpty($where);
    }
}
