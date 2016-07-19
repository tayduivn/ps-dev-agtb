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

class RestRequestTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider versionProvider
     */
    public function testVersion($req, $header, $expUrlVersion, $expVersion)
    {
        $service = array_merge(array('REQUEST_METHOD' => 'GET'), $header);
        $restRequest = new RestRequest($service, array('__sugar_url' => $req));
        $urlVersion = SugarTestReflection::getProtectedValue($restRequest, 'urlVersion');
        $this->assertSame($expUrlVersion, $urlVersion);
        $this->assertSame($expVersion, $restRequest->getVersion());
    }

    public function versionProvider()
    {
        $headerName = 'HTTP_ACCEPT';
        return array(
            // no header, only URL
            array("v10/Accounts/by_country", array(), '10', 10),
            // double path
            array("//v99/Accounts/by_country/", array(), '99', 99),
            // only Accept header has version
            array(
                "/Accounts/by_country",
                array($headerName => 'application/vnd.sugarcrm.core+xml; version=11'),
                null,
                11,
            ),
            // only Accept header has version, with no response type
            array(
                "/Accounts/by_country",
                array($headerName => 'application/vnd.sugarcrm.core; version=10'),
                null,
                10,
            ),
            // Header with qualifier indicator
            array(
                "/Accounts/by_country",
                array($headerName =>
                    'application/vnd.sugarcrm.core+xml; version=11;
                    q=0.5, application/vnd.sugarcrm.core+json; version=11',
                ),
                null,
                11,
            ),
            // url version not 2-digit, will not be detected and header version will be used
            array(
                "v10.1/Accounts/by_country",
                array($headerName => 'application/vnd.sugarcrm.core+xml; version=11'),
                null,
                11,
            ),
        );
    }

    /**
     *  @expectedException SugarApiExceptionIncorrectVersion
     * @dataProvider versionExceptionProvider
     */
    public function testVersionException($req, $header)
    {
        $service = array_merge(array('REQUEST_METHOD' => 'GET'), $header);
        $restRequest = new RestRequest($service, array('__sugar_url' => $req));
        $restRequest->getVersion();
    }

    public function versionExceptionProvider()
    {
        $headerName = 'HTTP_ACCEPT';
        return array(
            // both header and URL have version
            array(
                "v10/Accounts/by_country",
                array($headerName => 'application/vnd.sugarcrm.core+xml; version=11'),
            ),
            // neither Header nor Url has versoin
            array("/Accounts/by_country/", array()),
            // not 2-digit url version
            array("v42.3/Accounts/by_country?foo=bar", array()),
            // not 2-digit url version
            array("//v7/Accounts/by_country/", array()),
            // 3-digit, and no URL version
            array(
                "/Accounts/by_country",
                array($headerName => 'application/vnd.sugarcrm.core+xml; version=101'),
            ),
            // header version with ".", and no URL version
            array(
                "/Accounts/by_country",
                array($headerName => 'application/vnd.sugarcrm.core+xml; version=10.1'),
            ),
            // header version has random string, and no URL version
            array(
                "/Accounts/by_country",
                array($headerName => 'application/vnd.sugarcrm.core+xml; version=v10.1x'),
            ),
        );
    }

    public function testMethod()
    {
        $serv = array('REQUEST_METHOD' => 'GET');
        $r = new RestRequest($serv, array());
        $this->assertEquals("GET", $r->getMethod());

        $serv = array('REQUEST_METHOD' => 'POST');
        $r = new RestRequest($serv, array());
        $this->assertEquals("POST", $r->getMethod());
    }

    /**
     * @dataProvider pathProvider
     * @param string $path
     * @param array $parsedpath
     */
    public function testParsePath($path, $parsedpath)
    {
        $r = new RestRequest(array('REQUEST_METHOD' => 'GET'), array('__sugar_url' => $path));
        $this->assertEquals($r->path, $parsedpath);
    }

    public function pathProvider()
    {
        return array(
            array("v10/metadata/public", array('metadata', 'public')),
            array("//v10/metadata/public//", array('metadata', 'public')),
            array("v42/metadata/123/", array('metadata', '123')),
            array("blah/metadata/123/", array('blah','metadata', '123')),
            array("/v12/metadata/../public/", array('metadata', '..', 'public')),
        );
    }

    /**
     * @dataProvider pathVarsProvider
     */
    public function testGetPathVars($path, $route, $vars)
    {
        $r = new RestRequest(array('REQUEST_METHOD' => 'GET'), array('__sugar_url' => $path));
        $this->assertEquals($r->getPathVars($route), $vars);

    }

    public function pathVarsProvider()
    {
        return array(
            array('v10/metadata/public', array(), array()),
            array('v10/metadata/public', array("pathVars" => array('foo')), array('foo' => 'metadata')),
            array('v10/metadata/public', array("pathVars" => array('', 'foo')), array('foo' => 'public')),
            array('v10/metadata/public', array("pathVars" => array('', '', 'foo')), array()),
        );
    }

    /**
     * @dataProvider headersProvider
     */
    public function testGetRequestHeaders($serv, $header, $value)
    {
        $serv['REQUEST_METHOD'] = 'GET';
        $r = new RestRequest($serv, array('__sugar_url' => 'v10/metadata/public'));
        if(empty($value)) {
            $this->assertArrayNotHasKey($header, $r->getRequestHeaders());
        } else {
            $this->assertEquals($value, $r->getHeader($header));
        }
    }

    public function headersProvider()
    {
        return array(
            array(array("HTTP_HOST" => 'foo'), 'HOST', 'foo'),
            array(array("HTTP_PORT" => '123'), 'HOST', null),
            array(array("HTTP_PORT_NUMBER" => '123'), 'PORT_NUMBER', '123'),
        );
    }


    public function testGetResourceURIBase()
    {
        $r = new RestRequest(array(
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => '__sugar_url=v10/metadata/public&type_filter=&module_filter=&platform=base&_hash=688d8896f98ff0d0db7fca1aad465809',
            'REQUEST_URI' => '/sugar7/rest/v10/metadata/public?type_filter=&module_filter=&platform=base&_hash=688d8896f98ff0d0db7fca1aad465809',
            'SCRIPT_NAME' => '/sugar7/api/rest.php',
        ), array('__sugar_url' => 'v10/metadata/public'));

        $this->assertEquals($GLOBALS['sugar_config']['site_url']."/rest/v10/", $r->getResourceURIBase(10));
    }

    /**
     * @dataProvider rawPathProvider
     * @param array $req
     * @param string $path
     */
    public function testGetRawPath($req, $path)
    {
        $serv = array('REQUEST_METHOD' => 'GET');
        $r = new RestRequest($serv, $req);

        $this->assertEquals($path, $r->getRawPath());

        if(!empty($req['__sugar_url'])) {
            $serv['PATH_INFO'] = $req['__sugar_url'];
            unset($req['__sugar_url']);
            $r = new RestRequest($serv, $req);

            $this->assertEquals($path, $r->getRawPath());
        }
    }

    public function rawPathProvider()
    {
        return array(
            array(array(), '/'),
            array(array('' => "/foo"), '/'),
            array(array('__sugar_url' => "/foo"), '/foo'),
            array(array('PATH_INFO' => '/foo'), '/'),
            array(array('__sugar_url' => "//foo//../bar"), '//foo//../bar'),
            array(null, '/'),
        );
    }
}