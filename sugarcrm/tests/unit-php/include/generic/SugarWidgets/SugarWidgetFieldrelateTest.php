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

namespace Sugarcrm\SugarcrmTestsUnit\inc\generic\SugarWidgets;

use PHPUnit\Framework\TestCase;

/**
 * Class SugarWidgetFieldrelateTest
 *
 * @coversDefaultClass \SugarWidgetFieldRelate
 */
class SugarWidgetFieldrelateTest extends TestCase
{
    /**
     * @covers ::queryFilterEquals
     */
    public function testQueryFilterEquals()
    {
        $expected = "cases_cstm.user_id_c IN ('seed_sarah_id')";
        $layoutDef =  [
            "adhoc" => 1,
            "name" => "user_id_c",
            "table_key" => "self",
            "qualifier_name" => "equals",
            "input_name0" => "Sarah Smith",
            "table_alias" => "cases_cstm",
            "column_key" => "self:owner_c",
            "type" => "relate",
        ];
        $reporter = new \stdClass();
        $reporter->all_fields["self:owner_c"] = [
            "name" => "owner_c",
            "vname" => "LBL_OWNER",
            "type" => "relate",
            "id_name" => "user_id_c",
            "ext2" => "Users",
            "module" => "Cases",
            "rname" => "name",
            "id" => "Casesowner_c",
            "custom_module" => "Cases",
            "real_table" => "cases_cstm",
            "secondary_table" => "users",
            "rep_rel_name" => "owner_c_0",
        ];
        $lm = $this->createPartialMock('LayoutManager', []);
        $lm->setAttributePtr('reporter', $reporter);
        $widgetField = $this->getMockBuilder('SugarWidgetFieldRelate')
            ->setMethods(['getRelateIds'])
            ->setConstructorArgs([&$lm])
            ->getMock();
        $widgetField->expects($this->once())
            ->method('getRelateIds')
            ->will($this->returnValue(['seed_sarah_id']));
        $filter = $widgetField->queryFilterEquals($layoutDef);
        $this->assertEquals($expected, $filter);
    }
}
