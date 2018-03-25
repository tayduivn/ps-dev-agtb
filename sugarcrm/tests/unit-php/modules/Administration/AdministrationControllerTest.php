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

namespace Sugarcrm\SugarcrmTestUnit\modules\Administration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @coversDefaultClass \AdministrationController
 */
class AdministrationControllerTest extends TestCase
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

    public function parseImportSamlXmlFileDataProvider()
    {
        return [
            'single cert' => [
                [
                    'idp' => [
                        'entityId' => 'SomeEntityID',
                        'singleSignOnService' => ['url' => 'http://sso.com'],
                        'x509cert' => 'x509cert==',
                    ],
                ],
            ],
            'multi cert' => [
                [
                    'idp' => [
                        'entityId' => 'SomeEntityID',
                        'singleSignOnService' => ['url' => 'http://sso.com'],
                        'x509certMulti' => [
                            'signing' => ['x509cert=='],
                            'encryption' => ['x509certEncryption=='],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider parseImportSamlXmlFileDataProvider
     * @covers ::action_parseImportSamlXmlFile
     */
    public function testAction_parseImportSamlXmlFile($parsedMetadata)
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
            ->willReturn($parsedMetadata);

        ob_start();
        $this->controller->action_parseImportSamlXmlFile();
        $content = ob_get_clean();
        $content = json_decode($content);
        $this->assertEquals('http://sso.com', $content->SAML_loginurl);
        $this->assertEquals('SomeEntityID', $content->SAML_idp_entityId);
        $this->assertEquals('x509cert==', $content->SAML_X509Cert);
    }
}
