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
require_once("include/utils.php");

class EnsureJSCacheFilesExistTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $testFile = "cache/include/javascript/sugar_sidecar.min.js";
    protected $testFiles = array(
        "cache/include/javascript/sugar_grp1.js",
        "cache/include/javascript/sugar_grp1_jquery.js",
    );

    protected function setup() 
    {
        // Remove all current javascript cache files
        $files = glob("cache/include/javascript/*.js");
        foreach ($files as $file) {
            unlink($file);
        }
    }

    public function testEnsureJSCacheFilesExistSingle()
    {
        // Sanity check
        $this->assertFileNotExists($this->testFile, "Test file was not removed during setup");

        // Run the new method and ensure it was run
        $actual = ensureJSCacheFilesExist();
        $expect = "./{$this->testFile}";

        // Real assertions
        $this->assertFileExists($this->testFile, "Test file was created");
        $this->assertEquals($expect, $actual, "File returned was not what was expected");
    }

    public function testEnsureJSCacheFilesExistArray()
    {
        // Sanity check
        $this->assertFileNotExists($this->testFile, "Test file was not removed during setup");

        // Run the new method and ensure it was run against an array of files
        $actual = ensureJSCacheFilesExist($this->testFiles, '.', false);
        foreach ($this->testFiles as $f) {
            $expect[] = "./$f";
        }

        // Real assertions
        $this->assertFileExists($this->testFile, "Test file was created");
        $this->assertEquals($expect, $actual, "Files returned were not what was expected");
    }
}
