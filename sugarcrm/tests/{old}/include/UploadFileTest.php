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

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass UploadFile
 */
class UploadFileTest extends TestCase
{
    private $db;

    protected function setUp() : void
    {
        $this->db = SugarTestHelper::setUp('mock_db');
    }

    protected function tearDown() : void
    {
        SugarTestHelper::tearDown();
    }

    public function unlinkFileDataProvider()
    {
        return array(
            array(
                array(
                    'upload_id' => '123',
                ),
                true,
            ),
            array(
                array(),
                false,
            ),
        );
    }

    /**
     * @covers ::unlink_file
     * @dataProvider unlinkFileDataProvider
     */
    public function testUnlinkFile($rows, $expected)
    {
        $id = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $file = "upload://{$id}";
        file_put_contents($file, $id);

        $this->db->addQuerySpy(
            'upload_id',
            "/SELECT upload_id FROM notes WHERE upload_id='{$id}'/",
            array($rows)
        );

        $actual = UploadFile::unlink_file($id);
        $this->assertSame($expected, file_exists($file), 'The filesystem is not correct');
        $this->assertSame(!$expected, $actual, 'The result of the function call is not correct');

        unlink($file);
    }

    /**
     * @covers ::unlink_file
     */
    public function testUnlinkFile_FileDoesNotExist()
    {
        $id = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();

        $rows = array();
        $this->db->addQuerySpy(
            'upload_id',
            "/SELECT upload_id FROM notes WHERE upload_id='{$id}'/",
            array($rows)
        );

        $actual = UploadFile::unlink_file($id);
        $this->assertFalse($actual);
    }
}
