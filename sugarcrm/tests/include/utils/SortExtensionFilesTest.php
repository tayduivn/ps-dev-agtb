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

require_once 'include/utils.php';

class SortExtensionFilesTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static $test_directory = 'custom/sort_extension_files_test/';

    public static function setUpBeforeClass()
    {
        create_custom_directory('sort_extension_files_test');

        sugar_touch(self::getTestFilePath('file1.php'));
        sugar_touch(self::getTestFilePath('file2.php'));
        sugar_touch(self::getTestFilePath('file3.php'));
        sugar_touch(self::getTestFilePath('_overridefile3.php'));
    } 

    public static function tearDownAfterClass()
    {
        if (is_dir(self::$test_directory)) {
            rmdir_recursive(self::$test_directory);
        }
    }

    /**
     * Wraps filename with full test filepath
     * @param string $filename Filename
     * @return string Filepath
     */
    public static function getTestFilePath($filename) {
        return self::$test_directory.$filename;
    }

    /**
     * @dataProvider providerSortExtensionFiles
     */
    public function testSortExtensionFiles($files_for_test, $expected_result) 
    {
        $timestamp = time();
        $files = array();

        foreach($files_for_test as $file) {
            sugar_touch(self::getTestFilePath($file['filename']), $timestamp + $file['time_diff']);
            $files[] = self::getTestFilePath($file['filename']);
        }

        foreach ($expected_result as $id => $file) {
            $expected_result[$id] = self::getTestFilePath($file);
        }

        $files_sorted = sortExtensionFiles($files);

        $this->assertEquals($expected_result, $files_sorted);
    }

    function providerSortExtensionFiles() {
        return array(
            array(
                'files_for_test' => array(
                    array(
                        'filename' => 'file1.php',
                        'time_diff' => 2,
                    ),
                    array(
                        'filename' => 'file2.php',
                        'time_diff' => 0,
                    ),
                    array(
                        'filename' => 'file3.php',
                        'time_diff' => 1,
                    ),
                    array(
                        'filename' => '_overridefile3.php',
                        'time_diff' => 0,
                    ),
                ),
                'expected_result' => array(
                    'file2.php',
                    'file3.php',
                    'file1.php',
                    '_overridefile3.php',
                ),
            ),
            array(
                'files_for_test' => array(
                    array(
                        'filename' => 'file1.php',
                        'time_diff' => 0,
                    ),
                    array(
                        'filename' => 'file2.php',
                        'time_diff' => 0,
                    ),
                    array(
                        'filename' => 'file3.php',
                        'time_diff' => 0,
                    ),
                ),
                'expected_result' => array(
                    'file1.php',
                    'file2.php',
                    'file3.php',
                ),
            ),
            array(
                'files_for_test' => array(
                    array(
                        'filename' => 'file1.php',
                        'time_diff' => 2,
                    ),
                    array(
                        'filename' => 'file2.php',
                        'time_diff' => 2,
                    ),
                    array(
                        'filename' => 'file3.php',
                        'time_diff' => 1,
                    ),
                ),
                'expected_result' => array(
                    'file3.php',
                    'file1.php',
                    'file2.php',
                ),
            ),
            array(
                'files_for_test' => array(
                    array(
                        'filename' => 'file1.php',
                        'time_diff' => 2,
                    ),
                    array(
                        'filename' => 'file2.php',
                        'time_diff' => 0,
                    ),
                    array(
                        'filename' => 'file3.php',
                        'time_diff' => 1,
                    ),
                ),
                'expected_result' => array(
                    'file2.php',
                    'file3.php',
                    'file1.php',
                ),
            ),
        );
    }
}
