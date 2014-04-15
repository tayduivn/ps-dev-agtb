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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
require_once 'jssource/minify_utils.php';

class SugarMinifyUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * The file that is built by this process
     * 
     * @var string
     */
    protected $builtFile = 'cache/include/javascript/unit_test_built.min.js';

    public function setup()
    {
        $obj = new SugarMinifyUtilsForTesting;
        $obj->ConcatenateFiles('tests');
    }

    public function tearDown()
    {
        @unlink($this->builtFile);
    }

    public function testConcatenateFiles()
    {
        // Test the file was created
        $this->assertFileExists($this->builtFile);
        
        // Test the contents of the file. Using contains instead of equals so
        // systems without JSMin won't fail hard
        $content = file_get_contents($this->builtFile);
        $expect1 = file_get_contents('tests/jssource/minify/expect/var.js');
        $expect2 = file_get_contents('tests/jssource/minify/expect/if.js');
        $this->assertContains($expect1, $content);
        $this->assertContains($expect2, $content);
    }
}

class SugarMinifyUtilsForTesting extends SugarMinifyUtils
{
    protected function getJSGroupings()
    {
        return array(
            array(
                'jssource/minify/test/var.js' => 'include/javascript/unit_test_built.min.js',
                'jssource/minify/test/if.js' => 'include/javascript/unit_test_built.min.js',
            ),
        );
    }
}

