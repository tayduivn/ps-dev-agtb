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
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once "tests/upgrade/UpgradeTestCase.php";

class CategoryTypeRecordViewFixTest extends UpgradeTestCase
{
    protected $file = 'custom/modules/Products/clients/base/views/record/record.php';
    protected $fileContents = false;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        if (SugarAutoLoader::fileExists($this->file)) {
            $this->fileContents = sugar_file_get_contents($this->file);
        }

        sugar_mkdir(dirname($this->file), null, true);
        sugar_file_put_contents(
            $this->file,
            file_get_contents(__DIR__ . '/_files/record.php')
        );
    }

    public function tearDown()
    {
        rmdir_recursive(dirname($this->file));

        if (is_string($this->fileContents)) {
            sugar_mkdir(dirname($this->file), null, true);
            sugar_file_put_contents($this->file, $this->fileContents);
        }

        parent::tearDown();
    }

    public function testRun()
    {
        $this->upgrader->setVersions('6.7.4', 'ent', '7.2.0', 'ent');
        $script = $this->upgrader->getScript('post', '7_CategoryTypeRecordViewFix');
        $script->run();

        $viewdefs = null;

        include($this->file);

        $this->assertNotEmpty($viewdefs);
        $fields = $viewdefs['Products']['base']['view']['record']['panels'][1]['fields'];
        foreach ($fields as $field) {
            $this->assertNotContains('_id', $field['name']);
            $this->assertContains('_name', $field['name']);
        }

        $viewdefs = null;
    }
}
