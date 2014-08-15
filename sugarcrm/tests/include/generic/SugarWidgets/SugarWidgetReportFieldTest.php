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

require_once 'include/generic/LayoutManager.php';

/**
 * Test for SugarWidgetReportField.
 */
class SugarWidgetReportFieldTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Bean to work with.
     * @var SugarBean
     */
    protected $bean;

    /**
     * Definition of layout for SugarWidget.
     * @var array
     */
    protected $layoutDef = array();

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
        parent::setUp();

        $this->bean = BeanFactory::getBean('Contacts');
        $this->bean->id = create_guid();
        $this->bean->new_with_id = true;
        $this->bean->save();

        $this->layoutDef = array(
            'table' => $this->bean->table_name,
            'table_alias' => $this->bean->table_name,
            'input_name0' => array(),
            'name' => 'first_name',
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->bean->mark_deleted($this->bean->id);
        parent::tearDown();
    }

    /**
     * @covers SugarWidgetReportField::queryFilterEmpty
     */
    public function testEmptyMethod()
    {
        $this->bean->first_name = '';
        $this->bean->save();

        $query = $this->getQueryObject();
        $widget = $this->getSugarWidget();

        $query->whereRaw($widget->queryFilterEmpty($this->layoutDef));
        $result = $query->execute();

        $this->assertCount(1, $result);
    }

    /**
     * @covers SugarWidgetReportField::queryFilterNot_Empty
     */
    public function testNotEmptyMethod()
    {
        $this->bean->first_name = 'testNotEmptyMethod';
        $this->bean->save();

        $query = $this->getQueryObject();
        $widget = $this->getSugarWidget();

        $query->whereRaw($widget->queryFilterNot_Empty($this->layoutDef));
        $result = $query->execute();

        $this->assertCount(1, $result);
    }

    /**
     * @return SugarQuery
     */
    protected function getQueryObject()
    {
        $query = new SugarQuery();
        $query->select(array('id'));
        $query->from($this->bean)
            ->whereRaw("id = '{$this->bean->id}'");
        return $query;
    }

    /**
     * @return SugarWidget
     */
    protected function getSugarWidget()
    {
        $lm = new LayoutManager();
        $reporter = new stdClass();
        $reporter->db = DBManagerFactory::getInstance();
        $reporter->report_def_str = '';
        $lm->setAttributePtr('reporter', $reporter);
        $widget = new SugarWidgetReportField($lm);
        return $widget;
    }
}
