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

require_once 'soap/SoapPortalHelper.php';

class SoapPortalHelperTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @covers validateOrderBy()
     * @dataProvider providerOrderByValid
     */
    public function testValidateOrderByValid($orderBy)
    {
        validateOrderBy($orderBy);
    }

    /**
     * @covers validateOrderBy()
     * @dataProvider providerOrderByInvalid
     * @expectedException RuntimeException
     */
    public function testValidateOrderByInvalid($orderBy)
    {
        validateOrderBy($orderBy);
    }

    public function providerOrderByValid()
    {
        return array(
            array(''),
            array('0a0'),
            array('a0a'),
            array('a0.a0'),
            array('a0.0a'),
            array('0a.a0'),
            array('0a.0a'),
            array('col1'),
            array(' col_1 '),
            array('table.col1'),
            array('table.col1   asc'),
            array('table.col1 desc'),
            array('table.col1, table.col2'),
            array('table.col1, table.col2 desc'),
            array('table.col1      desc,table.col2 desc'),
            array('table.col1   asc, table.col2  '),
            array('table.col1, table.col_2, table.col_3, table.col4'),
            array('  col1 asc  ,   col3   , tbl1.col4 desc '),
            array('col1,'),
            array(','),
            array(',,'),
            array(' '),
            array('    '),
            array('col1, col2 asc,'),
            array(null),
            array(false),
        );
    }

    public function providerOrderByInvalid()
    {
        return array(
            array(0),
            array(0.0),
            array('0.0'),
            array('a0.0'),
            array('0.0a'),
            array('dd.dd.aa'),
            array('tbl.'),
            array('.col'),
            array('tbl.col1.'),
            array('`col1`'),
            array('0'),
            array('col 1'),
            array('"col1"'),
            array('"col1()"'),
            array('col-1'),
            array('table col1'),
            array('table col1 asc'),
            array('table.col1 asc desc'),
            array('SUBSTR(col1, 1, 3)'),
            array('SELECT SLEEP(4) FROM users WHERE 1=1'),
            array('1,extractvalue(0x0a,concat(0x0a,(select database())))'),
        );
    }
}
