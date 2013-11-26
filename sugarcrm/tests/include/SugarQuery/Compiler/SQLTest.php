<?php

require_once 'include/SugarQuery/Compiler/SQL.php';

class SugarQuery_Compiler_SQLTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @param SugarBean $bean
     * @param array $fields
     * @param string $expected
     *
     * @dataProvider getData
     */
    public function testCompileSelect($bean, $fields, $expected)
    {
        $compiler = new SugarQuery_Compiler_SQL($GLOBALS['db']);
        $query = new SugarQuery();
        $select = new SugarQuery_Builder_Select($query, $fields);
        $rc = new ReflectionObject($compiler);

        $compileFrom = $rc->getMethod('compileFrom');
        $compileFrom->setAccessible(true);
        $compileFrom->invokeArgs($compiler, array($bean));

        $sugarQuery = $rc->getProperty('sugar_query');
        $sugarQuery->setAccessible(true);
        $sugarQuery->setValue($compiler, new SugarQuery());

        $compileSelect = $rc->getMethod('compileSelect');
        $compileSelect->setAccessible(true);
        $actual = $compileSelect->invokeArgs($compiler, array($select));

        $this->assertEquals($expected, $actual);
    }

    public static function getData()
    {
        return array(
            // contacts.id should be removed because it's selected by contacts.*
            array(
                new Contact(),
                array(
                    'contacts.*',
                    'contacts.id',
                ),
                'contacts.*',
            ),
            // first_name, last_name, salutation, title from full_name should be ignored because they're already selected
            array(
                new Contact(),
                array(
                    'first_name',
                    'contacts.first_name',
                    'contacts.last_name',
                    'contacts.salutation',
                    'contacts.title',
                    'full_name',
                ),
                'contacts.first_name, contacts.last_name, contacts.salutation, contacts.title',
            ),
            // we should be able select the same field with different aliases
            array(
                new Contact(),
                array(
                    array('first_name', 'a1'),
                    'first_name',
                ),
                'contacts.first_name AS a1, contacts.first_name',
            ),
            // account.id should be ignored because we already selected id from contact, maybe we need to log error here
            array(
                new Contact(),
                array(
                    'contacts.id',
                    array('accounts.id', 'id'),
                ),
                'contacts.id',
            ),
        );
    }
}
