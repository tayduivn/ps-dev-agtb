<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

// php phpunit.php --group=portal2 portal2/PortalIndex.php
require_once('jssource/minify_utils.php');

class PortalIndexTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $rootDir;
    public $pathToSidecar;
    public $sugarsidecar;

    public function setUp()
    {
        parent::setUp();
        $this->rootDir = getcwd() . '/tests/tmp';
        $this->pathToSidecar = $this->rootDir . '/cache/include/javascript/';
        $this->sugarsidecar = $this->pathToSidecar . 'sugar_sidecar.min.js';
        sugar_mkdir($this->pathToSidecar,null,true);
    }
    public function tearDown()
    {
        parent::tearDown();
        if(is_file($this->sugarsidecar)) {
            unlink($this->sugarsidecar);
        }
        if(is_dir($this->pathToSidecar)) {
            rmdir($this->pathToSidecar);
        }
    }

    /**
     * @group portal2
     */
    public function testEnsureCacheWhenNoFiles()
    {
        $minifyUtilsMock = $this->getMock('SugarMinifyUtils', array('ConcatenateFiles'));
        $minifyUtilsMock->expects($this->once())
            ->method("ConcatenateFiles")
            ->with($this->rootDir);
        $actual = ensureCache($minifyUtilsMock, $this->rootDir);
        $this->assertEquals($this->sugarsidecar, $actual, "Should return the path to sidecar min");
    }
    /**
     * @group portal2
     */
    public function testEnsureCacheAlreadySidecarMin()
    {
        sugar_touch($this->sugarsidecar);
        sugar_file_put_contents($this->sugarsidecar, "adfs");
        $minifyUtilsMock = $this->getMock('SugarMinifyUtils', array('ConcatenateFiles'));
        // We don't call ConcatenateFiles if min file already there
        $minifyUtilsMock->expects($this->never())
            ->method("ConcatenateFiles");
        $actual = ensureCache($minifyUtilsMock, $this->rootDir);
        $this->assertEquals($this->sugarsidecar, $actual, "Should still return the path to sidecar min");
    }
}
