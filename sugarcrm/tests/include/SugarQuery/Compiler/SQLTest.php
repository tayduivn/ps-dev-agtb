<?php

require_once 'include/SugarQuery/Compiler/SQL.php';

class SugarQuery_Compiler_SQLTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @param SugarBean $bean
     * @param array $fields
     *
     * @dataProvider getData
     */
    public function testCompileSelect($bean, $fields)
    {
        $compiler = new SugarQuery_Compiler_SQL($GLOBALS['db']);
        $query = new SugarQuery();
        $query->from(new Contact());
        $query->select($fields);
        $rc = new ReflectionObject($compiler);

        $compileFrom = $rc->getMethod('compileFrom');
        $compileFrom->setAccessible(true);
        $compileFrom->invokeArgs($compiler, array($bean));

        $sugarQuery = $rc->getProperty('sugar_query');
        $sugarQuery->setAccessible(true);
        $sugarQuery->setValue($compiler, new SugarQuery());

        $compileSelect = $rc->getMethod('compileSelect');
        $compileSelect->setAccessible(true);
        $result = $compileSelect->invokeArgs($compiler, array($query->select));
        $result = explode(',', $result);
        $actual = array();
        foreach ($result as $field) {
            $field = explode(' ', trim($field));
            $field = end($field);
            $this->assertNotContains($field, $actual);
            $actual[] = $field;
        }
        $this->assertNotEmpty($actual);
    }

    public static function getData()
    {
        return array(
            // contacts.id should be removed because it's selected by contacts.*
            array(
                new Contact(),
                array(
                    array('contacts.id', 'id'),
                    'contacts.id',
                    'contacts.*',
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
            ),
            // we should be able select the same field with different aliases
            array(
                new Contact(),
                array(
                    array('first_name', 'a1'),
                    'first_name',
                ),
            ),
            // account.id should be ignored because we already selected id from contact, maybe we need to log error here
            array(
                new Contact(),
                array(
                    'contacts.id',
                    array('accounts.id', 'id'),
                ),
            ),
        );
    }
}
