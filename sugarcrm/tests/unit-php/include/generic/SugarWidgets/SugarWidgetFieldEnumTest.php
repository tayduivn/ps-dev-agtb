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

/** @noinspection PhpUndefinedFieldInspection */

namespace Sugarcrm\SugarcrmTestsUnit\inc\generic\SugarWidgets;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class SugarWidgetFieldEnumTest
 * @package Sugarcrm\SugarcrmTestsUnit\inc\generic\SugarWidgets
 * @coversDefaultClass \SugarWidgetFieldEnum
 */
class SugarWidgetFieldEnumTest extends TestCase
{
    public function setUp()
    {
        $this->reporter = $this->createPartialMock(\Report::class, []);
        $db = $this->createPartialMock(\MysqliManager::class, ['convert', 'quoted', 'getIsNullSQL', 'getIsNotNullSQL']);
        $this->reporter->db = $db;
        $lm = $this->createPartialMock(\LayoutManager::class, []);
        $lm->setAttributePtr('reporter', $this->reporter);
        $this->widgetField = $this->createPartialMock(\SugarWidgetFieldEnum::class, ['getInputValue']);
        $this->widgetField->layout_manager = $lm;
        TestReflection::setProtectedValue($this->widgetField, 'reporter', $this->reporter);
    }

    /**
     * @covers ::queryFilterEmpty
     */
    public function testQueryFilterEmpty()
    {
        $expected = "(coalesce(LENGTH(IFNULL(accounts.industry, 0)),0) = 0 OR IFNULL(accounts.industry, 0) = '^^')";
        $layoutDef = $layoutDef =  array(
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'empty',
            'input_name0' => 'empty',
            'input_name1' => 'on',
            'table_alias' => 'accounts',
            'column_key' => 'self:industry',
            'type' => 'enum',
        );
        $this->reporter->db->expects($this->at(0))
            ->method('convert')
            ->with($this->equalTo('accounts.industry'), $this->equalTo('IFNULL'))
            ->willReturnCallback(function ($str) {
                return "IFNULL($str, 0)";
            });
        $this->reporter->db->expects($this->at(1))
            ->method('convert')
            ->with($this->equalTo('IFNULL(accounts.industry, 0)'), 'length')
            ->willReturnCallback(function ($str) {
                return "LENGTH($str)";
            });
        $filter = $this->widgetField->queryFilterEmpty($layoutDef);
        $this->assertEquals($expected, $filter);
    }

    /**
     * @covers ::queryFilterNot_Empty
     */
    public function testQueryFilterNotEmpty()
    {
        $expected = "(coalesce(LENGTH(IFNULL(accounts.industry, 0)),0) > 0 AND IFNULL(accounts.industry, 0) != '^^' )";
        $expected .= "\n";

        $layoutDef = $layoutDef =  array(
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'not_empty',
            'input_name0' => 'not_empty',
            'input_name1' => 'on',
            'table_alias' => 'accounts',
            'column_key' => 'self:industry',
            'type' => 'enum',
        );
        $this->reporter->db->expects($this->at(0))
            ->method('convert')
            ->with($this->equalTo('accounts.industry'), $this->equalTo('IFNULL'))
            ->willReturnCallback(function ($str) {
                return "IFNULL($str, 0)";
            });
        $this->reporter->db->expects($this->at(1))
            ->method('convert')
            ->with($this->equalTo('IFNULL(accounts.industry, 0)'), 'length')
            ->willReturnCallback(function ($str) {
                return "LENGTH($str)";
            });
        $filter = $this->widgetField->queryFilterNot_Empty($layoutDef);
        $this->assertEquals($expected, $filter);
    }


    /**
     * @covers ::queryFilteris
     */
    public function testQueryFilterIs()
    {
        $expected = "accounts.industry = \"Banking\"\n";
        $layoutDef = $layoutDef =  array(
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'is',
            'table_alias' => 'accounts',
            'input_name0' => ['Banking'],
            'column_key' => 'self:industry',
            'type' => 'enum',
        );
        $this->widgetField->expects($this->once())
            ->method('getInputValue')
            ->willReturnCallback(function ($def) {
                return $def['input_name0'][0];
            });
        $this->reporter->db->expects($this->once())
            ->method('quoted')
            ->willReturnCallback(function ($str) {
                return '"' . $str . '"';
            });
        $filter = $this->widgetField->queryFilteris($layoutDef);
        $this->assertEquals($expected, $filter);
    }

    /**
     * @covers ::queryFilteris_not
     */
    public function testQueryFilterIsNot()
    {
        $expected = 'accounts.industry <> "Banking" OR (accounts.industry IS NULL AND "Banking" IS NOT NULL)';
        $layoutDef = $layoutDef =  array(
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'is_not',
            'table_alias' => 'accounts',
            'input_name0' => ['Banking'],
            'column_key' => 'self:industry',
            'type' => 'enum',
        );
        $this->widgetField->expects($this->once())
            ->method('getInputValue')
            ->willReturnCallback(function ($def) {
                return $def['input_name0'][0];
            });
        $this->reporter->db->expects($this->once())
            ->method('quoted')
            ->willReturnCallback(function ($str) {
                return '"' . $str . '"';
            });
        $this->reporter->db->expects($this->once())
            ->method('getIsNullSQL')
            ->willReturnCallback(function ($str) {
                return "$str IS NULL";
            });
        $this->reporter->db->expects($this->once())
            ->method('getIsNotNullSQL')
            ->willReturnCallback(function ($str) {
                return "$str IS NOT NULL";
            });
        $filter = $this->widgetField->queryFilteris_not($layoutDef);
        $this->assertEquals($expected, $filter);
    }

    /**
     * @covers ::queryFilterone_of
     */
    public function testQueryFilterOneOf()
    {
        $expected = "accounts.industry IN (\"Banking\",\"Technology\")\n";
        $layoutDef = $layoutDef =  array(
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'one_of',
            'table_alias' => 'accounts',
            'input_name0' => ['Banking', 'Technology'],
            'column_key' => 'self:industry',
            'type' => 'enum',
        );
        $this->reporter->db->expects($this->exactly(2))
            ->method('quoted')
            ->willReturnCallback(function ($str) {
                return '"' . $str . '"';
            });
        $filter = $this->widgetField->queryFilterone_of($layoutDef);
        $this->assertEquals($expected, $filter);
    }

    /**
     * @covers ::queryFilternot_one_of
     */
    public function testQueryFilterNotOneOf()
    {
        $expected = "accounts.industry NOT IN (\"Banking\",\"Technology\") OR accounts.industry IS NULL\n";
        $layoutDef = $layoutDef =  array(
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'not_one_of',
            'table_alias' => 'accounts',
            'input_name0' => ['Banking', 'Technology'],
            'column_key' => 'self:industry',
            'type' => 'enum',
        );
        $this->reporter->db->expects($this->exactly(2))
            ->method('quoted')
            ->willReturnCallback(function ($str) {
                return '"' . $str . '"';
            });
        $this->reporter->db->expects($this->once())
            ->method('getIsNullSQL')
            ->willReturnCallback(function ($str) {
                return "$str IS NULL";
            });
        $filter = $this->widgetField->queryFilternot_one_of($layoutDef);
        $this->assertEquals($expected, $filter);
    }
}
