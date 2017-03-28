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

namespace Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Parser;

require_once 'include/utils.php';

class XmlIdpMetadataParser
{
    /**
     * @var bool|\SimpleXMLElement
     */
    protected $xml;

    /**
     * error messages array
     * @var array
     */
    protected $errors = [];

    /**
     * SAML IDP entity id
     * @var string
     */
    protected $entityId;

    /**
     * SAML log in url
     * @var string
     */
    protected $ssoUrl;

    /**
     * SAML log in binding
     * @var string
     */
    protected $ssoBinding;

    /**
     * SAML log out url
     * @var string
     */
    protected $sloUrl;

    /**
     * SAML log out binding
     * @var string
     */
    protected $sloBinding;

    /**
     * encoded X.509 certificate
     * @var string
     */
    protected $x509Cert;

    /**
     * prepared and encoded X.509 certificate
     * @var string
     */
    protected $x509CertPem;

    /**
     * load XML from file and parse it
     * @param $path
     * @return bool
     */
    public function loadFromFile($path)
    {
        $this->xml = @simplexml_load_file($path);
        if (!$this->xml) {
            $this->errors[] = $this->translateError('WRONG_IMPORT_METADATA_INVALID_SOURCE_ERROR');
        }
        return $this->parse();
    }

    /**
     * load XML from string and parse it
     * @param $xmlString
     * @return bool
     */
    public function loadFromString($xmlString)
    {
        $this->xml = @simplexml_load_string($xmlString);
        if (!$this->xml) {
            $this->errors[] = $this->translateError('WRONG_IMPORT_METADATA_INVALID_SOURCE_ERROR');
        }
        return $this->parse();
    }

    /**
     * parse xml metadata file
     * @return bool
     */
    protected function parse()
    {
        if (!$this->xml instanceof \SimpleXMLElement) {
            return false;
        }

        $previous = libxml_use_internal_errors(false);

        $this->xml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');

        $elements = $this->xml->xpath('//md:EntityDescriptor');
        if (is_array($elements) && count($elements)) {
            $this->entityId = (string) $elements[0]['entityID'];
        } else {
            $this->errors[] = $this->translateError('WRONG_IMPORT_XML_FILE_NO_MAIN_SECTION_ERROR');
        }

        $elements = $this->xml->xpath('//md:EntityDescriptor/md:IDPSSODescriptor');
        if (!is_array($elements) || !count($elements)) {
            $this->errors[] = $this->translateError('WRONG_IMPORT_XML_FILE_NO_IDP_SECTION_ERROR');
        }

        $elements = $this->xml->xpath('//md:EntityDescriptor/md:IDPSSODescriptor/md:SingleSignOnService');
        if (is_array($elements) && count($elements)) {
            list($this->ssoUrl, $this->ssoBinding) = $this->chooseCorrectBinding($elements);
        }

        $elements = $this->xml->xpath('//md:EntityDescriptor/md:IDPSSODescriptor/md:SingleLogoutService');
        if (is_array($elements) && count($elements)) {
            list($this->sloUrl, $this->sloBinding) = $this->chooseCorrectBinding($elements);
        }

        $elements = $this->xml->xpath('//md:EntityDescriptor/md:IDPSSODescriptor/md:KeyDescriptor');
        if (is_array($elements) && count($elements)) {
            $this->x509Cert = str_replace(
                [" ", "\t", "\n", "\r", "\0" , "\x0B"],
                '',
                (string) $elements[0]->children("http://www.w3.org/2000/09/xmldsig#")
                    ->KeyInfo
                    ->X509Data
                    ->X509Certificate
            );
            $this->x509CertPem = sprintf(
                '-----BEGIN CERTIFICATE-----%s-----END CERTIFICATE-----',
                PHP_EOL . wordwrap($this->x509Cert, 64, PHP_EOL, true) . PHP_EOL
            );
        }

        libxml_use_internal_errors($previous);

        return empty($this->errors);
    }

    /**
     * return array of errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * return SAML entity id
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getSsoUrl()
    {
        return $this->ssoUrl;
    }

    /**
     * @return string
     */
    public function getSsoBinding()
    {
        return $this->ssoBinding;
    }

    /**
     * @return string
     */
    public function getSloUrl()
    {
        return $this->sloUrl;
    }

    /**
     * @return string
     */
    public function getSloBinding()
    {
        return $this->sloBinding;
    }

    /**
     * @return string
     */
    public function getX509Cert()
    {
        return $this->x509Cert;
    }

    /**
     * @return string
     */
    public function getX509CertPem()
    {
        return $this->x509CertPem;
    }

    /**
     * Choose HTTP-Redirect binding
     * @param array $elements
     * @return array
     */
    protected function chooseCorrectBinding(array $elements)
    {
        $result = [(string) $elements[0]['Location'], (string) $elements[0]['Binding']];

        foreach ($elements as $element) {
            if (strpos((string) $element['Binding'], 'HTTP-Redirect') !== false) {
                $result = [(string) $element['Location'], (string) $element['Binding']];
                break;
            }
        }

        return $result;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function translateError($key)
    {
        return translate($key, 'Administration');
    }
}
