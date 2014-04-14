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


require_once("include/Expressions/Expression/Date/MaxRelatedDateExpression.php");
require_once("include/Expressions/Expression/Parser/Parser.php");

class MaxRelatedDateExpressionTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function dataProviderDateFormatCheck()
    {
        return array(
            array('04/14/2014', 'm/d/Y'),
            array('14/04/2014', 'd/m/Y'),
            array('2014/04/14', 'Y/m/d'),
            array('04.14.2014', 'm.d.Y'),
            array('14.04.2014', 'd.m.Y'),
            array('2014.04.14', 'Y.m.d'),
            array('04-14-2014', 'm-d-Y'),
            array('14-04-2014', 'd-m-Y'),
            array('2014-04-14', 'Y-m-d'),
        );
    }

    /**
     * @dataProvider dataProviderDateFormatCheck
     *
     * @param $date
     * @param $format
     */
    public function testMaxRelatedDateEvaluate($date, $format)
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save'))
            ->getMock();

        $link2 = $this->getMockBuilder('Link2')
            ->setConstructorArgs(array('revenuelineitems', $opp))
            ->setMethods(array('getBeans'))
            ->getMock();

        $rel_bean1 = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save'))
            ->getMock();
        $db_date = SugarDateTime::createFromFormat($format, $date)->setTime(0, 0, 0)->asDbDate();
        /* @var $rel_bean1 RevenueLineItem */
        $rel_bean1->date_closed = $date;
        $rel_bean1->fetched_row['date_closed'] = $db_date;


        $rel_bean2 = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save'))
            ->getMock();

        $db_date2 = SugarDateTime::createFromFormat($format, $date)->modify('-20 days')->setTime(0, 0, 0);
        /* @var $rel_bean2 RevenueLineItem */
        $rel_bean2->date_closed = $db_date2->format($format);
        $rel_bean2->fetched_row['date_closed'] = $db_date2->asDbDate();

        $link2->expects($this->any())
            ->method('getBeans')
            ->will($this->returnValue(array($rel_bean1, $rel_bean2)));

        /* @var $opp Opportunity */
        $opp->revenuelineitems = $link2;

        $expr = 'maxRelatedDate($revenuelineitems, "date_closed")';
        $result = Parser::evaluate($expr, $opp)->evaluate();

        $this->assertEquals($result, '2014-04-14');
    }
}
