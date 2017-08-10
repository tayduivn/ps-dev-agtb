<?php

namespace Sugarcrm\SugarcrmTestUnit\modules\Administration;

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
                'getParsedIdPMetadata',
            ])
            ->getMock();

        $this->controller->expects($this->any())
            ->method('terminate')
            ->willReturn(true);

        $this->settings = $this->createMock(\OneLogin_Saml2_Settings::class);
        $this->controller->expects($this->any())
            ->method('getSamlSettings')
            ->willReturn($this->settings);

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
        $this->controller->expects($this->atLeastOnce())
            ->method('translateModuleError')
            ->withConsecutive(
                ['WRONG_IMPORT_FILE_NOT_FOUND_ERROR'],
                ['WRONG_IMPORT_METADATA_INVALID_SOURCE_ERROR'],
                ['WRONG_IMPORT_XML_FILE_NO_MAIN_SECTION_ERROR'],
                ['WRONG_IMPORT_XML_FILE_NO_IDP_SECTION_ERROR']
            )
            ->willReturn('error');

        $this->controller->expects($this->once())
            ->method('getUploadedMetadataFile')
            ->willReturn($this->file);

        $this->controller->expects($this->once())
            ->method('getParsedIdPMetadata')
            ->with($this->equalTo('dump'))
            ->willReturn([]);

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

        $this->controller->expects($this->once())
            ->method('getParsedIdPMetadata')
            ->with($this->equalTo('dump'))
            ->willReturn([
                'idp' => [
                    'entityId' => 'SomeEntityID',
                    'singleSignOnService' => [
                        'url' => 'http://sso.com',
                    ],
                ],
            ]);

        ob_start();
        $this->controller->action_parseImportSamlXmlFile();
        $content = ob_get_clean();
        $this->assertContains('url', $content);
        $this->assertContains('entityId', $content);
    }
}
