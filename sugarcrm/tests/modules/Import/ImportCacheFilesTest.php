<?php
require_once 'modules/Import/ImportCacheFiles.php';

class ImportCacheFilesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->importdir = ImportCacheFiles::getImportDir();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testgetDuplicateFileName()
    {
        $filename = ImportCacheFiles::getDuplicateFileName();

        $this->assertEquals(
            "{$this->importdir}/dupes_{$GLOBALS['current_user']->id}.csv", $filename);
    }

    public function testgetErrorFileName()
    {
        $filename = ImportCacheFiles::getErrorFileName();

        $this->assertEquals(
            "{$this->importdir}/error_{$GLOBALS['current_user']->id}.csv", $filename);
    }

    public function testgetStatusFileName()
    {
        $filename = ImportCacheFiles::getStatusFileName();

        $this->assertEquals(
            "{$this->importdir}/status_{$GLOBALS['current_user']->id}.csv", $filename);
    }

    public function testclearCacheFiles()
    {
        // make sure there is a file in there
        file_put_contents(ImportCacheFiles::getStatusFileName(),'foo');

        ImportCacheFiles::clearCacheFiles();

        $this->assertFalse(is_file(ImportCacheFiles::getStatusFileName()));
    }
}
