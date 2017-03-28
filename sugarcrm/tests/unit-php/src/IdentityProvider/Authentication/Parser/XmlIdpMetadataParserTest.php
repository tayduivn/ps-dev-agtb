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
namespace Sugarcrm\SugarcrmTestUnit\IdentityProvider\Authentication\Parser;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Parser\XmlIdpMetadataParser;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Parser\XmlIdpMetadataParser
 */
class XmlIdpMetadataParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var XmlIdpMetadataParser|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $parser;

    protected function setUp()
    {
        $this->parser = $this->getMockBuilder(XmlIdpMetadataParser::class)
            ->disableOriginalConstructor()
            ->setMethods(['translateError'])
            ->getMock();
    }

    /**
     * @covers ::loadFromFile
     * @covers ::parse
     */
    public function testLoadFromFileFail()
    {
        $this->parser->expects($this->once())
            ->method('translateError')
            ->with($this->equalTo('WRONG_IMPORT_METADATA_INVALID_SOURCE_ERROR'))
            ->willReturn('test');
        $this->assertFalse($this->parser->loadFromFile('test/dummy.txt'));
        $this->assertCount(1, $this->parser->getErrors());
    }

    /**
     * @covers ::loadFromFile
     * @covers ::parse
     * @covers ::getErrors
     */
    public function testLoadFromStringFail()
    {
        $this->parser->expects($this->once())
            ->method('translateError')
            ->with($this->equalTo('WRONG_IMPORT_METADATA_INVALID_SOURCE_ERROR'))
            ->willReturn('test');
        $this->assertFalse($this->parser->loadFromString('dummy'));
        $this->assertCount(1, $this->parser->getErrors());
    }

    /**
     * @covers ::loadFromFile
     * @covers ::parse
     * @covers ::getErrors
     */
    public function testLoadFromStringParseErrors()
    {
        $this->parser->expects($this->exactly(2))
            ->method('translateError')
            ->withConsecutive(
                [$this->equalTo('WRONG_IMPORT_XML_FILE_NO_MAIN_SECTION_ERROR')],
                [$this->equalTo('WRONG_IMPORT_XML_FILE_NO_IDP_SECTION_ERROR')]
            )
            ->willReturn('test');

        $this->assertFalse($this->parser->loadFromString($this->getNoIdpXMLString()));
        $this->assertCount(2, $this->parser->getErrors());
    }

    /**
     * @covers ::loadFromFile
     * @covers ::parse
     * @covers ::chooseCorrectBinding
     * @covers ::getEntityId
     * @covers ::getSsoUrl
     * @covers ::getSsoBinding
     * @covers ::getSloUrl
     * @covers ::getSloBinding
     * @covers ::getX509Cert
     * @covers ::getX509CertPem
     */
    public function testLoadFromString()
    {
        $this->parser->expects($this->never())
            ->method('translateError');

        $this->assertTrue($this->parser->loadFromString($this->getXMLString()));
        $this->assertEquals('http://www.okta.com/exk9oj4us8D9fVf370h7', $this->parser->getEntityId());
        $this->assertEquals('test', $this->parser->getSsoUrl());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect', $this->parser->getSsoBinding());
        $this->assertEquals('test', $this->parser->getSloUrl());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect', $this->parser->getSloBinding());
        $this->assertEquals('Certificate', $this->parser->getX509Cert());
        $this->assertEquals(
            '-----BEGIN CERTIFICATE-----' . PHP_EOL . 'Certificate' . PHP_EOL . '-----END CERTIFICATE-----',
            $this->parser->getX509CertPem()
        );
    }

    /**
     * @return string
     */
    protected function getNoIdpXMLString()
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<md:EntityErrorDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="http://www.okta.com/exk9oj4us8D9fVf370h7">
    <md:IDPSSODescriptor WantAuthnRequestsSigned="false" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
        <md:KeyDescriptor use="signing">
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:X509Data><ds:X509Certificate>Certificate</ds:X509Certificate></ds:X509Data>
            </ds:KeyInfo>
        </md:KeyDescriptor>
        <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="test"/>
        <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="test"/>
        <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress</md:NameIDFormat>
        <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</md:NameIDFormat>
        <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="test"/>
        <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="test"/>
    </md:IDPSSODescriptor>
</md:EntityErrorDescriptor>
XML;
    }
    /**
     * @return string
     */
    protected function getXMLString()
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" entityID="http://www.okta.com/exk9oj4us8D9fVf370h7">
    <md:IDPSSODescriptor WantAuthnRequestsSigned="false" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
        <md:KeyDescriptor use="signing">
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:X509Data><ds:X509Certificate>Certificate</ds:X509Certificate></ds:X509Data>
            </ds:KeyInfo>
        </md:KeyDescriptor>
        <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="test"/>
        <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="test"/>
        <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress</md:NameIDFormat>
        <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</md:NameIDFormat>
        <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="test"/>
        <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="test"/>
    </md:IDPSSODescriptor>
</md:EntityDescriptor>
XML;
    }
}
