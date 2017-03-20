<?php

namespace Sugarcrm\SugarcrmTestUnit\modules\Administration;

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
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->controller = $this->getMockBuilder(\AdministrationController::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSamlSettings', 'addErrorLogMessage'])
            ->getMock();

        $this->settings = $this->createMock(\OneLogin_Saml2_Settings::class);
        $this->controller->expects($this->any())
            ->method('getSamlSettings')
            ->willReturn($this->settings);
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
}
