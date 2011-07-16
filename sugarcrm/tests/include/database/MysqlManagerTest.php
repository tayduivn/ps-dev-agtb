<?php
require_once 'include/database/MysqlManager.php';

class MysqlManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    static public function setupBeforeClass()
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

        $this->_db = new MysqlManager();
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
}
