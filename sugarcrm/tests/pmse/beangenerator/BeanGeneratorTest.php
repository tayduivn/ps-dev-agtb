<?php

class BeanGeneratorTest extends  PHPUnit_Framework_TestCase
{
    private $bg;

    protected function setUp() {
        $this->bg = new BeanGenerator(array());
    }

    function testGetErrorMessage() {
        $this->bg->schema = 'pmse.xml';
        $this->bg->errorCode = PMGEN_SCHEMA_ERROR;
        $this->assertEquals("\nError: '".$this->bg->schema."' does not exist!\n\n", $this->bg->getErrorMessage());
        $this->bg->errorCode = PMGEN_SCHEMA_INVALID;
        $this->assertEquals("\nError: Schema '".$this->bg->schema."' is invalid!\n\n", $this->bg->getErrorMessage());
        $this->bg->errorCode = PMGEN_DIRECTORY_ERROR;
        $this->assertEquals("\nError: '".$this->bg->writingPath."' is not a valid output directory\n\n", $this->bg->getErrorMessage());
        $this->bg->errorCode = PMGEN_DIRECTORY_WRITING;
        $this->assertEquals("\nError: '".$this->bg->writingPath."' is not a writable directory\n\n", $this->bg->getErrorMessage());
        $this->bg->errorCode = PMGEN_GENERATING_ERROR;
        $this->assertEquals("\nError: Classes cannot be generated!\n\n", $this->bg->getErrorMessage());
        $this->bg->errorCode = PMGEN_WRITING_ERROR;
        $this->assertEquals("\nError: '".$this->bg->fileName."' cannot be created!.\n\n", $this->bg->getErrorMessage());
    }

    function testConvertField() {
        $variables = array('VARCHAR','LONGVARCHAR','MEDIUMTEXT','LONGTEXT','DATE','TINYINT','INTEGER','TIMESTAMP','FLOAT');
        $values = array('varchar','text','mediumtext','longtext','datetimecombo','int','int','datetimecombo','float');
        $count = 0;
        foreach ($variables as $variable) {
            $this->assertEquals($values[$count],$this->bg->convertField($variable));
            $count++;
        }
    }

    function testWriteComment() {
        $message = "Test Message";
        $this->bg->isVerbose = true;
        $this->assertEquals("Test Message", $this->bg->writeComment($message));
        $this->assertEquals("Test Message\n", $this->bg->writeComment($message, true));
        $this->bg->isVerbose = false;
        $this->assertEquals(null, $this->bg->writeComment($message));
        $this->assertEquals(null, $this->bg->writeComment($message, true));
    }

    function testLoadSchemaWithValidFile() {
        $count = 0;
        $bg = new BeanGenerator(array(
            'schema' => 'tests/beangenerator/fixtures/validSchema.xml'
        ));
        $this->assertEquals(true, $bg->loadSchema());
        $this->assertEquals(0, $bg->errorCode);
        foreach ($bg->tableNodes as $node) {
            $count++;
        }
        $this->assertEquals(2,$count);
    }

    function testLoadSchemaWithInvalidFile() {
        $bg = new BeanGenerator(array(
            'schema' => 'tests/beangenerator/fixtures/invalidSchema.xml'
        ));
        $this->assertFalse($bg->loadSchema());
        $this->assertEquals(PMGEN_SCHEMA_INVALID, $bg->errorCode);
    }

    function testLoadSchemaWithNonExistentFile() {
        $bg = new BeanGenerator(array(
            'schema' => 'tests/beangenerator/fixtures/nonExistingSchema.xml'
        ));
        $this->assertFalse($bg->loadSchema());
        $this->assertEquals(PMGEN_SCHEMA_ERROR, $bg->errorCode);
    }

    function testCheckDirectoryWriteWithWritableFolder() {
        $path = 'tests/beangenerator/tmp';
        $file = 'tests/beangenerator/fixtures/validSchema.xml';
        mkdir($path,0644);
        $bg = new BeanGenerator(array(
            'path' => $path
        ));
        $this->assertEquals(true, $bg->checkDirectoryWrite());
        rmdir($path);
        $bg = new BeanGenerator(array(
            'path' => $file
        ));
        $this->assertFalse($bg->checkDirectoryWrite());
        $this->assertEquals(PMGEN_DIRECTORY_ERROR, $bg->errorCode);
    }

    function testCheckDirectoryWriteWithInvalidFolder() {
        $path = 'tests/beangenerator/tmpx/kk';
        $bg = new BeanGenerator(array(
            'path' => $path
        ));
        $this->assertFalse($bg->checkDirectoryWrite());
        $this->assertEquals(PMGEN_DIRECTORY_PATH_ERROR, $bg->errorCode);
    }

    function testCheckDirectoryWriteWithReadOnlyFolder() {
        $path = 'tests/beangenerator/tmpreadonly';
        mkdir($path, 0444);
        $bg = new BeanGenerator(array(
            'path' => $path
        ));
        $response = $bg->checkDirectoryWrite();
        system("chmod 777 ".$path);
        rmdir($path);
        $this->assertFalse($response);
        $this->assertEquals(PMGEN_DIRECTORY_WRITING, $bg->errorCode);
    }

    function testDelTree() {
        $path = 'tests/beangenerator/tmp/inside';
        mkdir($path,0777,true);
        system("touch ".$path."/test.txt");
        $this->bg->delTree('tests/beangenerator/tmp');
        $realpath = realpath('tests/beangenerator/tmp');
        $this->assertEquals(null, $realpath);
    }

    function testGetTableColumnsAsVariables() {
        $expected = "\n";
        $fields = array('act_uid','prj_id','pro_id','act_type','act_is_for_compensation','act_start_quantity');
        foreach ($fields as $field) {
            $expected .= "    var \$".$field.";\n";
        }
        $xml = new DOMDocument();
        $xml->load('tests/beangenerator/fixtures/validSchema.xml');
        $rootNode = $xml->documentElement;
        $tables = $rootNode->getElementsByTagName('table');
        foreach($tables as $table) {
            $result = $this->bg->getTableColumnsAsVariables($table);
            $this->assertEquals($expected, $result);
            break;
        }
    }

}