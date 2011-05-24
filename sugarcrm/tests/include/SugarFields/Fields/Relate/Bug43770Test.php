<?php
require_once('include/SugarFields/Fields/Relate/SugarFieldRelate.php');

class Bug43770Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group	bug43770
     */
    public function testCustomIdName()
    {
        $sfr = new SugarFieldRelate('relate');
        $vardef = array(
            'name' => 'assigned_user_name',
            'id_name' => 'assigned_user_id',
            'module' => 'Users'
        );
        $displayParams = array(
            'idName' => 'Contactsassigned_user_name'
        );
        $result = $sfr->getEditViewSmarty(array(), $vardef, $displayParams, 1);
        $this->assertContains('id="Contacts{$Array.assigned_user_name.id_name}"', $result);
    }
}