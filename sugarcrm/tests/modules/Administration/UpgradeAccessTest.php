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

/**
 * UpgradeAccessTest.php
 *
 * This file tests the code run when UpgradeAccess.php is invoked.
 */

class UpgradeAccessTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        if(!file_exists('.htaccess'))
        {
            $this->markTestSkipped('This may be an instance that does not support the use of .htaccess files');
            return;
        }

        if(!is_writable('.htaccess'))
        {
            $this->markTestSkipped('Cannot write to .htaccess file.');
            return;
        }

        SugarTestHelper::setUp('files');
        SugarTestHelper::setUp('mod_strings', array('Administration'));

        SugarTestHelper::saveFile(".htaccess");
    }


    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }


    /**
     * This function tests to see the UpgradeAccess file correctly builds the .htaccess file when run.
     * In particular, the mod rewrite rule for rest URLs should be created.
     * @bug 56889
     */
    public function testUpgradeAccessCreatesRewriteRule()
    {
        require('modules/Administration/UpgradeAccess.php');
        $contents = file_get_contents('.htaccess');

        preg_match('/RewriteRule \^rest\/\(\.\*\)\$ api\/rest.php\?\_\_sugar\_url=\$1 \[L\,QSA\]/', $contents, $matches);
        $this->assertNotEmpty($matches, 'Could not find RewriteRule');
        $this->assertEquals(1, count($matches), 'Duplicate blocks were created for the RewriteRule');
        $this->assertContains('<FilesMatch', $contents, 'Code outside of restrictions was not copied over');
    }

}