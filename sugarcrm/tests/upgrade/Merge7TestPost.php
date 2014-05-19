<?php
require_once "tests/upgrade/UpgradeTestCase.php";

class Merge7TestPost extends UpgradeTestCase
{

    protected $new_dir;

    public function setUp()
    {
        parent::setUp();
        $this->upgrader->setVersions("7.0.0", "ent", "7.2.0", "ent");
    }

    public function tearDown()
    {
        parent::tearDown();
        rmdir_recursive("modules/Accounts/clients/test");
        rmdir_recursive("custom/modules/Accounts/clients/test");
    }


    protected function createView($viewname, $data, $prefix = '')
    {
        $filename = "modules/Accounts/clients/test/views/$viewname/$viewname.php";
        if($prefix) {
            $filename = "$prefix/$filename";
        }
        $pdata = array('panels' => $data);
        mkdir_recursive(dirname($filename));
        SugarTestHelper::saveFile($filename);
        write_array_to_file("viewdefs['Accounts']['test']['view']['$viewname']", $pdata, $filename);
    }

    public function mergeData()
    {
        return array(
            // add field
            array(
                    // pre
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email', 'phone', 'fax')
                    )),
                    // post
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email', 'phone', 'fax', 'description')
                    )),
                    // custom
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email', 'phone', 'fax', "custom_c")
                    )),
                    // result
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email', 'phone', 'fax', "custom_c", 'description')
                    )),
            ),
            // add field to another panel
            array(
                // pre
                array( array(
                        'name' => 'panel1',
                        'fields' => array('email')
                        ),
                        array(
                        'name' => 'panel2',
                        'fields' => array('phone', 'fax')
                        ),
                ),
                // post
                array( array(
                        'name' => 'panel1',
                        'fields' => array('email')
                        ),
                        array(
                        'name' => 'panel2',
                        'fields' => array('phone', 'fax', array("name" => 'description', "type" => "text"))
                        ),
                ),
                // custom
                array( array(
                        'name' => 'panel1',
                        'fields' => array('custom_c')
                        ),
                        array(
                        'name' => 'panel2',
                        'fields' => array('phone', 'fax')
                        ),
                ),
                // result
                array( array(
                        'name' => 'panel1',
                        'fields' => array('custom_c')
                        ),
                        array(
                        'name' => 'panel2',
                        'fields' => array('phone', 'fax', array("name" => 'description', "type" => "text"))
                        ),
                ),
            ),
            // remove field
            array(
                    // pre
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email', 'phone', 'fax', array("name" => "address"))
                    )),
                    // post
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email', 'phone', 'description')
                    )),
                    // custom
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email',  'phone', 'fax', "custom_c")
                    ),
                    array("name" => "panel2",
                        'fields' => array(array("name" => "address"))
                    )),
                    // result
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email', 'phone', "custom_c", 'description')
                    ),
                    array("name" => "panel2",
                        'fields' => array()
                    )),
            ),
            // field changed in new
            array(
                    // pre
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email', 'phone', array("name" => 'fax', "type" => "text"))
                    )),
                    // post
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email', 'phone', array("name" => 'fax', "type" => "phone"), 'description')
                    )),
                    // custom
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email', 'phone', array("name" => 'fax', "type" => "text"), "custom_c")
                    )),
                    // result
                    array( array(
                            'name' => 'panel1',
                            'fields' => array('email', 'phone', array("name" => 'fax', "type" => "phone"), "custom_c", 'description')
                    )),
            ),
        // field changed in custom
        array(
                // pre
                array( array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', array("name" => 'fax', "type" => "text"))
                )),
                // post
                array( array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', array("name" => 'fax', "type" => "phone"), 'description')
                )),
                // custom
                array( array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', array("name" => 'fax', "type" => "enum"), "custom_c")
                )),
                // result
                array( array(
                        'name' => 'panel1',
                        'fields' => array('email', 'phone', array("name" => 'fax', "type" => "enum"), "custom_c", 'description')
                )),
        ),

        );
    }

    /**
     * Test for Merge7Templates
     * @dataProvider mergeData
     */
    public function testMerge7Pre($pre_data, $post_data, $custom_data, $result)
    {
        $this->createView("mergetest", $post_data);
        $this->createView("mergetest", $custom_data, "custom");
        $this->upgrader->state['for_merge']["modules/Accounts/clients/test/views/mergetest/mergetest.php"]['Accounts']['test']['view']['mergetest']['panels'] = $pre_data;

        $script = $this->upgrader->getScript("post", "7_Merge7Templates");
        $script->run();
        $this->assertFileExists("custom/modules/Accounts/clients/test/views/mergetest/mergetest.php");
        include 'custom/modules/Accounts/clients/test/views/mergetest/mergetest.php';
        $this->assertEquals($result, $viewdefs['Accounts']['test']['view']['mergetest']['panels']);
    }
}