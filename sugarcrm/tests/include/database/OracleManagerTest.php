<?php
//FILE SUGARCRM flav=ent ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'include/database/OracleManager.php';

class OracleManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    static public function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    static public function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    public function setUp()
    {
        $this->_db = new OracleManager();
    }

    public function testQuote()
    {
        $string = "'dog eat ";
        $this->assertEquals($this->_db->quote($string),"''dog eat ");
    }

    public function testArrayQuote()
    {
        $string = array("'dog eat ");
        $this->_db->arrayQuote($string);
        $this->assertEquals($string,array("''dog eat "));
    }

    public function providerConvert()
    {
        $returnArray = array(
                array(
                    array('foo','nothing'),
                    'foo'
                ),
                array(
                    array('foo','date'),
                    "to_date(foo, 'YYYY-MM-DD')"
                    ),
                array(
                    array('foo','time'),
                    "to_date(foo, 'HH24:MI:SS')"
                    ),
                array(
                    array('foo','datetime'),
                    "to_date(foo, 'YYYY-MM-DD HH24:MI:SS')"
                    ),
                array(
                    array('foo','datetime',array(1,2,3)),
                    "to_date(foo, 'YYYY-MM-DD HH24:MI:SS',1,2,3)"
                    ),
                array(
                    array('foo','today'),
                    'sysdate'
                    ),
                array(
                    array('foo','left'),
                    "LTRIM(foo)"
                    ),
                array(
                    array('foo','left',array(1,2,3)),
                    "LTRIM(foo,1,2,3)"
                    ),
                array(
                    array('foo','date_format'),
                    "TO_CHAR(foo, 'YYYY-MM-DD')"
                    ),
                array(
                    array('foo','date_format',array("'%Y-%m'")),
                    "TO_CHAR(foo, 'YYYY-MM')"
                    ),
               array(
                    array('foo','date_format',array(1,2,3)),
                    "TO_CHAR(foo, 'YYYY-MM-DD')"
                    ),
                array(
                    array('foo','time_format'),
                    "TO_CHAR(foo,'HH24:MI:SS')"
                    ),
                array(
                    array('foo','time_format',array(1,2,3)),
                    "TO_CHAR(foo,1,2,3)"
                    ),
                array(
                    array('foo','IFNULL'),
                    "NVL(foo,'')"
                    ),
                array(
                    array('foo','IFNULL',array(1,2,3)),
                    "NVL(foo,1,2,3)"
                    ),
                array(
                    array('foo','CONCAT'),
                    "foo"
                    ),
                array(
                    array('foo','CONCAT',array(1,2,3)),
                    "foo||1||2||3"
                    ),
                array(
                    array('foo','text2char'),
                    "to_char(foo)"
                    ),
                array(
                    array('foo','length'),
                    "LENGTH(foo)"
                ),
                array(
                    array('foo','month'),
                    "TO_CHAR(foo, 'MM')"
                ),
                array(
                    array('foo','quarter'),
                    "TO_CHAR(foo, 'Q')"
                ),
                array(
                    array('foo','add_date',array(1,'day')),
                    "(foo + 1)"
                ),
                array(
                    array('foo','add_date',array(2,'week')),
                    "(foo + 2*7)"
                ),
                array(
                    array('foo','add_date',array(3,'month')),
                    "ADD_MONTHS(foo, 3)"
                ),
                array(
                    array('foo','add_date',array(4,'quarter')),
                    "ADD_MONTHS(foo, 4*3)"
                ),
                array(
                    array('foo','add_date',array(5,'year')),
                    "ADD_MONTHS(foo, 5*12)"
                ),
                array(
                    array('1.23','round',array(6)),
                    "round(1.23, 6)"
                )
        );
        return $returnArray;
    }

    /**
     * @ticket 33283
     * @dataProvider providerConvert
     */
    public function testConvert(array $parameters, $result)
    {
        $this->assertEquals($result, call_user_func_array(array($this->_db, "convert"), $parameters));
     }

     /**
      * @ticket 33283
      */
     public function testConcat()
     {
         $ret = $this->_db->concat('foo',array('col1','col2','col3'));
         $this->assertEquals("LTRIM(RTRIM(NVL(foo.col1,'')||' '||NVL(foo.col2,'')||' '||NVL(foo.col3,'')))", $ret);
     }

     public function providerFromConvert()
     {
         $returnArray = array(
             array(
                 array('foo','nothing'),
                 'foo'
                 ),
                 array(
                     array('2009-01-01 12:00:00','date'),
                     '2009-01-01'
                     ),
                 array(
                     array('2009-01-01 12:00:00','time'),
                     '12:00:00'
                     )
                 );

         return $returnArray;
     }

     /**
      * @ticket 33283
      * @dataProvider providerFromConvert
      */
     public function testFromConvert(
         array $parameters,
         $result
         )
     {
         $this->assertEquals(
             $this->_db->fromConvert($parameters[0],$parameters[1]),
             $result);
    }
}
