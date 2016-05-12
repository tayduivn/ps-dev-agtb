<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'modules/DocumentRevisions/DocumentRevision.php';

class DocumentRevisionTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $docs = array();
    private static $files = array();

    public static function tearDownAfterClass()
    {
        foreach (static::$docs as $doc) {
            $doc->mark_deleted($doc->id);
        }

        foreach (static::$files as $file) {
            unlink($file);
        }

        parent::tearDownAfterClass();
    }

    public function testSave_FileSizeIsSaved()
    {
        $filename = create_guid();
        $file = "upload://{$filename}";
        file_put_contents($file, $filename);
        $filesize = filesize($file);

        $doc = BeanFactory::newBean('DocumentRevisions');
        $doc->id = $filename;
        $doc->new_with_id = true;
        $doc->save(false);
        static::$docs[] = $doc;

        $this->assertSame($filesize, $doc->file_size);
    }

    public function testSave_FileSizeIsZero()
    {
        $doc = BeanFactory::newBean('DocumentRevisions');
        $doc->id = create_guid();
        $doc->new_with_id = true;
        $doc->save(false);
        static::$docs[] = $doc;

        $this->assertSame(0, $doc->file_size);
    }
}
