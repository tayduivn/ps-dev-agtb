<?php
require_once 'include/database/MssqlManager.php';

class MssqlManagerTest extends Sugar_PHPUnit_Framework_TestCase
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
        $this->_db = new MssqlManager();
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
                    array('foo','today'),
                    'GETDATE()'
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
                    'LEFT(CONVERT(varchar(10),foo,120),10)'
                    ),
                array(
                    array('foo','date_format',array('1','2','3')),
                    'LEFT(CONVERT(varchar(10),foo,120),10)'
                    ),
                array(
                    array('foo','date_format',array("'%Y-%m'")),
                    'LEFT(CONVERT(varchar(7),foo,120),7)'
                    ),
                array(
                    array('foo','IFNULL'),
                    'ISNULL(foo,\'\')'
                    ),
                array(
                    array('foo','IFNULL',array('1','2','3')),
                    'ISNULL(foo,1,2,3)'
                    ),
                array(
                    array('foo','CONCAT',array('1','2','3')),
                    'foo+1+2+3'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT'),
                    '1+2+3'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT',array('foo', 'bar')),
                    '1+2+3+foo+bar'
                    ),
                array(
                    array('foo','text2char'),
                    'CAST(foo AS varchar(8000))'
                ),
                array(
                    array('foo','length'),
                    "LEN(foo)"
                ),
                array(
                    array('foo','month'),
                    "MONTH(foo)"
                ),
                array(
                    array('foo','quarter'),
                    "DATEPART(quarter, foo)"
                ),
                array(
                    array('foo','add_date',array(1,'day')),
                    "DATEADD(day,1,foo)"
                ),
                array(
                    array('foo','add_date',array(2,'week')),
                    "DATEADD(week,2,foo)"
                ),
                array(
                    array('foo','add_date',array(3,'month')),
                    "DATEADD(month,3,foo)"
                ),
                array(
                    array('foo','add_date',array(4,'quarter')),
                    "DATEADD(quarter,4,foo)"
                ),
                array(
                    array('foo','add_date',array(5,'year')),
                    "DATEADD(year,5,foo)"
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
         $this->assertEquals("LTRIM(RTRIM(ISNULL(foo.col1,'')+' '+ISNULL(foo.col2,'')+' '+ISNULL(foo.col3,'')))", $ret);
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
