<?php
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
require_once 'include/database/MysqlManager.php';

class MysqlManagerTest extends Sugar_PHPUnit_Framework_TestCase
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
        if ( $GLOBALS['db']->dbType != 'mysql' ) {
            $this->markTestSkipped('Only applies to MySQL');
        }

        $this->_db = new MysqlManagerTestMock();
    }

    public function testQuote()
    {
        $string = "'dog eat ";
        if(!$this->_db->valid()) $this->markTestSkipped("MySQL not enabled");
        $this->assertEquals($this->_db->quote($string),"\\'dog eat ");
    }

    public function testArrayQuote()
    {
        if(!$this->_db->valid()) $this->markTestSkipped("MySQL not enabled");
        $string = array("'dog eat ");
        $this->_db->arrayQuote($string);
        $this->assertEquals($string,array("\\'dog eat "));
    }

    public function providerConvert()
    {
        $returnArray = array(
            array(
                array('foo','nothing'),
                'foo'
                ),
                array(
                    array('foo','today'),
                    'CURDATE()'
                    ),
                array(
                    array('foo','left'),
                    'LEFT(foo)'
                ),
                array(
                    array('foo','left',array('1','2','3')),
                    'LEFT(foo,1,2,3)'
                    ),
                array(
                    array('foo','date_format'),
                    'DATE_FORMAT(foo,\'%Y-%m-%d\')'
                        ),
                array(
                    array('foo','date_format',array('1','2','3')),
                    'DATE_FORMAT(foo,\'1\')'
                    ),
                array(
                    array('foo','date_format',array("'1'","'2'","'3'")),
                    'DATE_FORMAT(foo,\'1\')'
                    ),
                    array(
                    array('foo','datetime',array("'%Y-%m'")),
                    'foo'
                        ),
                array(
                    array('foo','IFNULL'),
                    'IFNULL(foo,\'\')'
                    ),
                array(
                    array('foo','IFNULL',array('1','2','3')),
                    'IFNULL(foo,1,2,3)'
                    ),
                array(
                    array('foo','CONCAT',array('1','2','3')),
                    'CONCAT(foo,1,2,3)'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT'),
                    'CONCAT(1,2,3)'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT',array('foo', 'bar')),
                    'CONCAT(1,2,3,foo,bar)'
                    ),
                array(
                    array('foo','text2char'),
                    'foo'
                ),
                array(
                    array('foo','length'),
                    "LENGTH(foo)"
                ),
                array(
                    array('foo','month'),
                    "MONTH(foo)"
                ),
                array(
                    array('foo','quarter'),
                    "QUARTER(foo)"
                ),
                array(
                    array('foo','add_date',array(1,'day')),
                    "DATE_ADD(foo, INTERVAL 1 day)"
                ),
                array(
                    array('foo','add_date',array(2,'week')),
                    "DATE_ADD(foo, INTERVAL 2 week)"
                ),
                array(
                    array('foo','add_date',array(3,'month')),
                    "DATE_ADD(foo, INTERVAL 3 month)"
                ),
                array(
                    array('foo','add_date',array(4,'quarter')),
                    "DATE_ADD(foo, INTERVAL 4 quarter)"
                ),
                array(
                    array('foo','add_date',array(5,'year')),
                    "DATE_ADD(foo, INTERVAL 5 year)"
                ),
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
         $this->assertEquals("LTRIM(RTRIM(CONCAT(IFNULL(foo.col1,''),' ',IFNULL(foo.col2,''),' ',IFNULL(foo.col3,''))))", $ret);
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
                     '2009-01-01 12:00:00'
                     ),
                 array(
                     array('2009-01-01 12:00:00','time'),
                     '2009-01-01 12:00:00'
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

    public function providerEmptyValues()
    {
        $returnArray = array(
            array(
                array("'1970-01-01'", 'date'), true,
                ),
            array(
                array("'1970-01-01 00:00:00'", 'datetime'), true,
                ),
            array(
                array("'0000-00-00 00:00:00'", 'datetime'), true,
                ),
            array(
                array("'0000-00-00'", 'date'), true,
                ),
            array(
                array("'2013-01-01'", 'date'), false,
                ),
            array(
                array("'2013-01-01 09:04:32'", 'datetime'), false,
                ),
            array(
                array("'00:00:00'", 'time'), true,
                ),
            array(
                array("'12:32:30'", 'time'), false,
                ),
            );

        return $returnArray;
    }


    /**
     * @ticket BR-238
     * @dataProvider providerEmptyValues
     */
    public function testEmptyValues($parameters, $result)
    {
        $this->assertEquals($result, $this->_db->_emptyValue($parameters[0], $parameters[1]));
    }

    /**
     * This is the data provider for testSupports
     */
    public function supportsProvider() {
        return array(
            array('recursive_query', false),
            array('fix:report_as_condition', true)
        );
    }

    /**
     * This is a test for known supported features
     * @dataProvider supportsProvider
     */
    public function testSupports($feature, $expectedSupport) {
        $this->assertEquals($expectedSupport, $this->_db->supports($feature));
    }
}

class MysqlManagerTestMock extends MysqlManager
{
    public function _emptyValue($val, $type) {
        return parent::_emptyValue($val, $type);
    }
}
