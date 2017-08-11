<?php

namespace Sugarcrm\SugarcrmTestUnit\modules\Administration;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Parser\XmlIdpMetadataParser;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @coversDefaultClass \AdministrationController
 */
class AdministrationControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \AdministrationController|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $controller;

    /**
     * @var \OneLogin_Saml2_Settings|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $settings;

    /**
     * @var XmlIdpMetadataParser|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $parser;

    /**
     * @var UploadedFile|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $file;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->controller = $this->getMockBuilder(\AdministrationController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getSamlSettings',
                'addErrorLogMessage',
                'terminate',
                'getUploadedMetadataFile',
                'translateModuleError',
                'getXmlMetadataParser',
            ])
            ->getMock();

        $this->controller->expects($this->any())
            ->method('terminate')
            ->willReturn(true);

        $this->settings = $this->createMock(\OneLogin_Saml2_Settings::class);
        $this->controller->expects($this->any())
            ->method('getSamlSettings')
            ->willReturn($this->settings);

        $this->parser = $this->createMock(XmlIdpMetadataParser::class);
        $this->controller->expects($this->any())
            ->method('getXmlMetadataParser')
            ->willReturn($this->parser);

        $this->file = $this->createMock(UploadedFile::class);
        $this->file->expects($this->any())
            ->method('__toString')
            ->willReturn('dump');
    }

    /**
     * @covers ::action_exportMetaDataFile
     */
    public function testAction_exportMetaDataFileException()
    {
        $this->settings->expects($this->once())
            ->method('getSPMetadata')
            ->willThrowException(new \OneLogin_Saml2_Error('parse metadata error'));

        $this->settings->expects($this->never())
            ->method('validateMetadata')
            ->with($this->isType('string'))
            ->willReturn([]);

        $this->controller->expects($this->once())
            ->method('addErrorLogMessage')
            ->with($this->contains('parse metadata error'));

        ob_start();
        $this->controller->action_exportMetaDataFile();
        $content = ob_get_clean();
        $this->assertContains('action=PasswordManager', $content);
    }

    /**
     * @covers ::action_exportMetaDataFile
     */
    public function testAction_exportMetaDataFileHasErrors()
    {
        $this->settings->expects($this->once())
            ->method('getSPMetadata')
            ->willReturn('metadata');

        $this->settings->expects($this->once())
            ->method('validateMetadata')
            ->with($this->isType('string'))
            ->willReturn(['validate error']);

        $this->controller->expects($this->once())
            ->method('addErrorLogMessage')
            ->with($this->contains('validate error'));

        ob_start();
        $this->controller->action_exportMetaDataFile();
        $content = ob_get_clean();
        $this->assertContains('action=PasswordManager', $content);
    }

    /**
     * @covers ::action_exportMetaDataFile
     */
    public function testAction_exportMetaDataFile()
    {
        $this->settings->expects($this->once())
            ->method('getSPMetadata')
            ->willReturn('metadata');

        $this->settings->expects($this->once())
            ->method('validateMetadata')
            ->with($this->isType('string'))
            ->willReturn([]);
        ob_start();
        $this->controller->action_exportMetaDataFile();
        $content = ob_get_clean();
        $this->assertContains('metadata', $content);
    }

    /**
     * @covers ::action_parseImportSamlXmlFile
     */
    public function testAction_parseImportSamlXmlFileNoFile()
    {
        $this->controller->expects($this->once())
            ->method('getUploadedMetadataFile')
            ->willReturn(false);
        $this->controller->expects($this->once())
            ->method('translateModuleError')
            ->with($this->equalTo('WRONG_IMPORT_FILE_NOT_FOUND_ERROR'))
            ->willReturn('error');

        ob_start();
        $this->controller->action_parseImportSamlXmlFile();
        $content = ob_get_clean();
        $this->assertContains('error', $content);
    }

    /**
     * @covers ::action_parseImportSamlXmlFile
     */
    public function testAction_parseImportSamlXmlFileParseErrors()
    {
        $this->controller->expects($this->once())
            ->method('translateModuleError')
            ->with($this->equalTo('WRONG_IMPORT_FILE_NOT_FOUND_ERROR'))
            ->willReturn('error');

        $this->controller->expects($this->once())
            ->method('getUploadedMetadataFile')
            ->willReturn($this->file);

        $this->parser->expects($this->once())
            ->method('loadFromFile')
            ->with($this->equalTo('dump'))
            ->willReturn(false);
        $this->parser->expects($this->once())
            ->method('getErrors')
            ->willReturn(['error']);

        ob_start();
        $this->controller->action_parseImportSamlXmlFile();
        $content = ob_get_clean();
        $this->assertContains('error', $content);
    }

    /**
     * @covers ::action_parseImportSamlXmlFile
     */
    public function testAction_parseImportSamlXmlFile()
    {
        $this->controller->expects($this->once())
            ->method('translateModuleError')
            ->with($this->equalTo('WRONG_IMPORT_FILE_NOT_FOUND_ERROR'))
            ->willReturn('error');

        $this->controller->expects($this->once())
            ->method('getUploadedMetadataFile')
            ->willReturn($this->file);

        $this->parser->expects($this->once())
            ->method('loadFromFile')
            ->with($this->equalTo('dump'))
            ->willReturn(true);

        $this->parser->expects($this->never())
            ->method('getErrors');

        $this->parser->expects($this->once())
            ->method('getSsoUrl')
            ->willReturn('url');

        ob_start();
        $this->controller->action_parseImportSamlXmlFile();
        $content = ob_get_clean();
        $this->assertContains('url', $content);
    }
}
