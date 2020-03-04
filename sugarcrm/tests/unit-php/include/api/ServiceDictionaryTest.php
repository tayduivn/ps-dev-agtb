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

namespace Sugarcrm\SugarcrmTestsUnit\inc\api;

use PHPUnit\Framework\TestCase;

/**
 *
 * @coversDefaultClass \ServiceDictionaryRest
 *
 */
class ServiceDictionaryTest extends TestCase
{
    /**
     * @covers ::registerEndpoints
     * @covers ::preRegisterEndpoints
     * @covers ::addToPathArray
     * @covers ::getRegisteredEndpoints
     *
     * @dataProvider providerTestRegisterEndpoints
     */
    public function testRegisterEndpoints($endpoints, $file, $class, $platform, $custom, $expected)
    {
        $sut = $this->getServiceDictionaryRestMock();

        // verify preregistering
        $sut->preRegisterEndpoints();
        $this->assertSame(array(), $sut->getRegisteredEndpoints());

        // set endpoints
        $sut->registerEndpoints($endpoints, $file, $class, $platform, $custom);
        $this->assertEquals($expected, $sut->getRegisteredEndpoints());
    }

    public function providerTestRegisterEndpoints()
    {
        return array(

            // GET and POST definition combined, with custom score
            array(
                array(
                    'entry1' => array(
                        'reqType' => 'GET',
                        'path' => array('path1', '<data>'),
                        'pathVars' => array('pathvar1', 'pathvar2'),
                        'method' => 'serviceMethod1',
                    ),
                    'entry2' => array(
                        'reqType' => 'POST',
                        'path' => array('?', 'path3'),
                        'pathVars' => array('pathvar3'),
                        'method' => 'serviceMethod2',
                    ),
                ),
                'fileName',
                'className',
                'base',
                true,
                array(
                    2 => array(
                        'base' => array(
                            'GET' => array(
                                'path1' => array(
                                    '<data>' => array(
                                        array(
                                            'reqType' => 'GET',
                                            'path' => array('path1', '<data>'),
                                            'pathVars' => array('pathvar1', 'pathvar2'),
                                            'method' => 'serviceMethod1',
                                            'file' => 'fileName',
                                            'className' => 'className',
                                            'score' => 8.50,
                                        ),
                                    ),
                                ),
                            ),
                            'POST' => array(
                                '?' => array(
                                    'path3' => array(
                                        array(
                                            'reqType' => 'POST',
                                            'path' => array('?', 'path3'),
                                            'pathVars' => array('pathvar3'),
                                            'method' => 'serviceMethod2',
                                            'file' => 'fileName',
                                            'className' => 'className',
                                            'score' => 8.25,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            // two GET conditions - one as single reqType one as array reqType
            array(
                array(
                    'entry1' => array(
                        'reqType' => 'GET',
                        'path' => array('path1', 'path2'),
                        'pathVars' => array('pathvar1', 'pathvar2'),
                        'method' => 'serviceMethod1',
                    ),
                    'entry2' => array(
                        'reqType' => array('GET'),
                        'path' => array('?', 'path3'),
                        'pathVars' => array('pathvar3'),
                        'method' => 'serviceMethod2',
                    ),
                ),
                'fileName2',
                'className2',
                'base',
                false,
                array(
                    2 => array(
                        'base' => array(
                            'GET' => array(
                                'path1' => array(
                                    'path2' => array(
                                        array(
                                            'reqType' => 'GET',
                                            'path' => array('path1', 'path2'),
                                            'pathVars' => array('pathvar1', 'pathvar2'),
                                            'method' => 'serviceMethod1',
                                            'file' => 'fileName2',
                                            'className' => 'className2',
                                            'score' => 8.75,
                                        ),
                                    ),
                                ),
                                '?' => array(
                                    'path3' => array(
                                        array(
                                            'reqType' => 'GET',
                                            'path' => array('?', 'path3'),
                                            'pathVars' => array('pathvar3'),
                                            'method' => 'serviceMethod2',
                                            'file' => 'fileName2',
                                            'className' => 'className2',
                                            'score' => 7.75,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            // extra score
            array(
                array(
                    'entry1' => array(
                        'reqType' => 'GET',
                        'path' => array('path1'),
                    ),
                    'entry2' => array(
                        'reqType' => 'GET',
                        'path' => array('path2'),
                        'extraScore' => 9.50,
                    ),
                ),
                'fileName3',
                'className3',
                'mobile',
                false,
                array(
                    1 => array(
                        'mobile' => array(
                            'GET' => array(
                                'path1' => array(
                                    array(
                                        'reqType' => 'GET',
                                        'path' => array('path1'),
                                        'file' => 'fileName3',
                                        'className' => 'className3',
                                        'score' => 7.00,
                                    ),
                                ),
                                'path2' => array(
                                    array(
                                        'reqType' => 'GET',
                                        'path' => array('path2'),
                                        'extraScore' => 9.50,
                                        'file' => 'fileName3',
                                        'className' => 'className3',
                                        'score' => 16.50,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            // dual request type
            array(
                array(
                    'entry1' => array(
                        'reqType' => array('GET', 'POST'),
                        'path' => array('path1', 'path2'),
                        'pathVars' => array('pathvar1', 'pathvar2'),
                        'method' => 'serviceMethod1',
                    ),
                ),
                'fileName4',
                'className4',
                'base',
                false,
                array(
                    2 => array(
                        'base' => array(
                            'GET' => array(
                                'path1' => array(
                                    'path2' => array(
                                        array(
                                            'reqType' => 'GET',
                                            'path' => array('path1', 'path2'),
                                            'pathVars' => array('pathvar1', 'pathvar2'),
                                            'method' => 'serviceMethod1',
                                            'file' => 'fileName4',
                                            'className' => 'className4',
                                            'score' => 8.75,
                                        ),
                                    ),
                                ),
                            ),
                            'POST' => array(
                                'path1' => array(
                                    'path2' => array(
                                        array(
                                            'reqType' => 'POST',
                                            'path' => array('path1', 'path2'),
                                            'pathVars' => array('pathvar1', 'pathvar2'),
                                            'method' => 'serviceMethod1',
                                            'file' => 'fileName4',
                                            'className' => 'className4',
                                            'score' => 8.75,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            // invalid entry points (not array)
            array(
                null,
                null,
                null,
                null,
                false,
                array(),
            ),

        );
    }

    public function providerTestlookupRoute()
    {
        return array(
            'default route' => array(array('Accounts', 'config'), '10', 'foo1'),
            'exact match version and min/max Version' => array(array('Accounts', 'config'), '11', 'foo3'),
            'minor version in between min/max Version' => array(array('Accounts', 'config'), '11.3', 'foo4'),
            'foo4 has maxVersion specified' => array(array('Accounts', 'config'), '12', 'foo4'),
            'foo2 has minVersion' => array(array('Accounts', 'config'), '13', 'foo2'),
            'foo5 has higher minVersion' => array(array('Accounts', 'config'), '14', 'foo5'),
            'minor version and foo5 has higher minVersion' => array(array('Accounts', 'config'), '14.4', 'foo5'),
            'minor version and foo7 has higher minVersion' => array(array('Accounts', 'config'), '15.15', 'foo7'),
        );
    }

    /**
     * @covers ::<public>
     * @covers ::<protected>
     * @covers ::<private>
     *
     * @dataProvider providerTestlookupRoute
     */
    public function testlookupRoute($path, $version, $expected)
    {
        $vServiceDict = $this->getServiceDictionaryRestMock();
        $vServiceDict->preRegisterEndpoints();

        $vServiceDict->registerEndpoints($this->registerApiVersionRest(), 'fake/foo.php', 'fooClass', 'base', 0);
        $vServiceDict->dict = $vServiceDict->getRegisteredEndpoints();

        // Make sure we can find the best route
        $route = $vServiceDict->lookupRoute($path, $version, 'GET', 'base');
        $this->assertEquals($expected, $route['method'], "error in version/route");
    }

    protected function registerApiVersionRest()
    {
        return array(
            'foo1' => array(
                'reqType' => 'GET',
                'path' => array('Accounts', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'foo1',
            ),
            'foo2' => array(
                'reqType' => 'GET',
                'minVersion' => '11',
                'path' => array('Accounts', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'foo2',
            ),
            'foo3' => array(
                'reqType' => 'GET',
                'minVersion' => '11',
                'maxVersion' => '11',
                'path' => array('Accounts', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'foo3',
            ),
            'foo4' => array(
                'reqType' => 'GET',
                'minVersion' => '11',
                'maxVersion' => '12',
                'path' => array('Accounts', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'foo4',
            ),
            'foo5' => array(
                'reqType' => 'GET',
                'minVersion' => '14',
                'path' => array('Accounts', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'foo5',
            ),
            'foo6' => array(
                'reqType' => 'GET',
                'minVersion' => '15.2', // test that version 15.12 is > version 15.2
                'path' => array('Accounts', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'foo6',
            ),
            'foo7' => array(
                'reqType' => 'GET',
                'minVersion' => '15.12',  // test that version 15.12 is > version 15.2
                'path' => array('Accounts', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'foo7',
            ),
        );
    }

    public function providerTestlookupRouteCustom()
    {
        return array(
            array(array('Accounts', 'config'), '10', 'fooCustom1'),
            array(array('Accounts', 'config'), '11', 'fooCustom4'),
            array(array('Accounts', 'config'), '12', 'fooCustom4'),
            array(array('Accounts', 'config'), '13', 'fooCustom1'),
            array(array('Accounts', 'config'), '14', 'fooCustom5'),
            array(array('Accounts', 'config'), '15.2', 'fooCustom6'),
            array(array('Accounts', 'config'), '15.4', 'fooCustom6'),
        );
    }

    /**
     * @covers ::<public>
     * @covers ::<protected>
     * @covers ::<private>
     *
     * @dataProvider providerTestlookupRouteCustom
     */
    public function testlookupRouteCustom($path, $version, $expected)
    {

        $vcServiceDict = $this->getServiceDictionaryRestMock();
        $vcServiceDict->preRegisterEndpoints();

        $vcServiceDict->registerEndpoints($this->registerApiVersionRest(), 'fake/foo.php', 'fooClass', 'base', 0); // non custom
        $vcServiceDict->registerEndpoints($this->registerApiVersionCustomRest(), 'fake/foo.php', 'fooClass', 'base', 1); // custom module
        $vcServiceDict->dict = $vcServiceDict->getRegisteredEndpoints();

        // Make sure we can find the best route
        $route = $vcServiceDict->lookupRoute($path, $version, 'GET', 'base');
        $this->assertEquals($expected, $route['method'], "error in version/route");
    }

    protected function registerApiVersionCustomRest()
    {
        return array(
            'fooCustom1' => array(
                'reqType' => 'GET',
                'path' => array('Accounts', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'fooCustom1',
            ),
            'fooCustom2' => array(
                'reqType' => 'GET',
                'minVersion' => '11',
                'path' => array('<module>', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'fooCustom2',
            ),
            'fooCustom3' => array(
                'reqType' => 'GET',
                'minVersion' => '11',
                'maxVersion' => '11',
                'path' => array('?', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'fooCustom3',
            ),
            'fooCustom4' => array(
                'reqType' => 'GET',
                'minVersion' => '11',
                'maxVersion' => '12',
                'path' => array('Accounts', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'fooCustom4',
            ),
            'fooCustom5' => array(
                'reqType' => 'GET',
                'minVersion' => '14',
                'path' => array('Accounts', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'fooCustom5',
            ),
            'fooCustom6' => array(
                'reqType' => 'GET',
                'minVersion' => '15',
                'maxVersion' => '15.4',
                'path' => array('Accounts', 'config'),
                'pathVars' => array('module', 'record'),
                'method' => 'fooCustom6',
            ),
        );
    }

    public function providerTestlookupRouteModuleAndWildCard()
    {
        return array(
            array(
                array(
                    'fooModule1' => array(
                        'reqType' => 'GET',
                        'path' => array('Accounts', 'config'),
                        'pathVars' => array('module', 'record'),
                        'method' => 'fooModule1',
                    ),
                    'fooModule2' => array(
                        'reqType' => 'GET',
                        'minVersion' => '11',
                        'path' => array('<module>', 'config'),
                        'pathVars' => array('module', 'record'),
                        'method' => 'fooModule2',
                    ),
                    'fooModule3' => array(
                        'reqType' => 'GET',
                        'minVersion' => '11',
                        'path' => array('?', 'config'),
                        'pathVars' => array('module', 'record'),
                        'method' => 'fooModule3',
                    ),
                ),
                array('Accounts', 'config'),
                '11',
                'fooModule1',
            ), // exact match wins even no min/max version specified
            array(
                array(
                    'fooModule1' => array(
                        'reqType' => 'GET',
                        'minVersion' => '11',
                        'path' => array('<module>', 'config'),
                        'pathVars' => array('module', 'record'),
                        'method' => 'fooModule1',
                    ),
                    'fooModule2' => array(
                        'reqType' => 'GET',
                        'minVersion' => '12',
                        'maxVersion' => '12',
                        'path' => array('?', 'config'),
                        'pathVars' => array('module', 'record'),
                        'method' => 'fooModule2',
                    ),
                    'fooModule3' => array(
                        'reqType' => 'GET',
                        'minVersion' => '12',
                        'path' => array('<module>', 'config'),
                        'pathVars' => array('module', 'record'),
                        'method' => 'fooModule3',
                    ),
                ),
                array('Accounts', 'config'),
                '12',
                'fooModule3',
            ), // <module> wins over wildcard ?
            array(
                array(
                    'fooModule1' => array(
                        'reqType' => 'GET',
                        'minVersion' => '11',
                        'path' => array('<module>', 'config'),
                        'pathVars' => array('module', 'record'),
                        'method' => 'fooModule1',
                    ),
                    'fooModule2' => array(
                        'reqType' => 'GET',
                        'minVersion' => '11',
                        'path' => array('?', 'config'),
                        'pathVars' => array('module', 'record'),
                        'method' => 'fooModule2',
                    ),
                    'fooModule3' => array(
                        'reqType' => 'GET',
                        'minVersion' => '12',
                        'path' => array('<module>', 'config'),
                        'pathVars' => array('module', 'record'),
                        'method' => 'fooModule3',
                    ),
                ),
                array('Accounts', 'config'),
                '13',
                'fooModule3',
            ), // highest minVersion winds
        );
    }

    /**
     * @covers ::<public>
     * @covers ::<protected>
     * @covers ::<private>
     *
     * @dataProvider providerTestlookupRouteModuleAndWildCard
     */
    public function testlookupRouteModuleAndWildCard($endpoint, $path, $version, $expected)
    {

        $vmServiceDict = $this->getServiceDictionaryRestMock(array('matchModule'));
        $vmServiceDict->expects($this->any())
            ->method('matchModule')
            ->with('Accounts')
            ->willReturn(true);

        $vmServiceDict->preRegisterEndpoints();

        $vmServiceDict->registerEndpoints($endpoint, 'fake/foo.php', 'fooClass', 'base', 0); // non custom
        $vmServiceDict->dict = $vmServiceDict->getRegisteredEndpoints();

        // Make sure we can find the best route
        $route = $vmServiceDict->lookupRoute($path, $version, 'GET', 'any');
        $this->assertEquals($expected, $route['method'], "error in version/route");
    }

    /**
     *
     * @covers ::<public>
     * @covers ::<protected>
     * @covers ::<private>
     *
     * @expectedException \SugarApiExceptionNoMethod
     */
    public function testExceptionNoEntry()
    {

        require_once 'include/utils.php';
        $vServiceDict = $this->getServiceDictionaryRestMock();
        $vServiceDict->preRegisterEndpoints();

        $vServiceDict->registerEndpoints($this->registerApiVersionRest(), 'fake/foo.php', 'fooClass', 'base', 0);
        $vServiceDict->dict = $vServiceDict->getRegisteredEndpoints();

        // test no entry
        $route = $vServiceDict->lookupRoute(array('Accounts'), '11', 'GET', 'base');
    }

    /**
     *
     * @covers ::<public>
     * @covers ::<protected>
     * @covers ::<private>
     *
     * @expectedException \SugarApiExceptionNoMethod
     */
    public function testExceptionNoRoute()
    {

        require_once 'include/utils.php';
        $vServiceDict = $this->getServiceDictionaryRestMock();
        $vServiceDict->preRegisterEndpoints();

        $vServiceDict->registerEndpoints(
            array(
                'foo' => array(
                    'reqType' => 'GET',
                    'minVersion' => '11',
                    'path' => array('Accounts', 'config'),
                    'pathVars' => array('module', 'record'),
                    'method' => 'foo',
                ),
            ),
            'fake/foo.php',
            'fooClass',
            'base',
            0
        );

        $vServiceDict->dict = $vServiceDict->getRegisteredEndpoints();

        // test no route exception
        $route = $vServiceDict->lookupRoute(array('Accounts', 'config'), '10', 'GET', 'base');
    }

    /**
     * @param null|array $methods
     * @return \ServiceDictionaryRest
     */
    protected function getServiceDictionaryRestMock($methods = null)
    {
        return $this->getMockBuilder('ServiceDictionaryRest')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
