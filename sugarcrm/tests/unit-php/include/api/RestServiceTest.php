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

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class RestServiceTest
 * @coversDefaultClass \RestService
 */
class RestServiceTest extends \PHPUnit_Framework_TestCase
{
    const SITE_URL = 'sugarcrm.com/';

    protected function setup()
    {
        parent::setUp();
        // pre-setting
        $_REQUEST['__sugar_url'] = 'sugar.com';
    }

    /**
     *
     * @covers ::getResourceURI
     * @covers ::setResourceURIBase
     *
     * @dataProvider providerGetResourceURI
     */
    public function testGetResourceURI($resource, $hasRoute, $options, $requestUrl, $scriptName, $expected)
    {

        $_SERVER['REQUEST_URI'] = $requestUrl;
        $_SERVER['SCRIPT_NAME'] = $scriptName;

        $requestMock = $this->createPartialMock('RestRequest', array('getVersion', 'getResourceURIBase'));
        $requestMock->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('11'));
        if ($options['relative'] == true) {
            $requestMock->expects($this->any())
                ->method('getResourceURIBase')
                ->will($this->returnValue('/rest/v11/'));
        } else {
            $requestMock->expects($this->any())
                ->method('getResourceURIBase')
                ->will($this->returnValue(self::SITE_URL . 'rest/v11/'));
        }

        $serviceMock = $this->getMockBuilder('RestService')
            ->disableOriginalConstructor()
            ->setMethods(array('findRoute', 'getSiteUrl'))
            ->getMock();

        $serviceMock->expects($this->any())
            ->method('findRoute')
            ->will($this->returnValue($hasRoute));

        $serviceMock->expects($this->any())
            ->method('getSiteUrl')
            ->will($this->returnValue(self::SITE_URL));

        TestReflection::setProtectedValue($serviceMock, 'request', $requestMock);

        $result = $serviceMock->getResourceURI($resource, $options);
        $this->assertSame($expected, $result);
    }

    public function providerGetResourceURI()
    {
        return array(
            // resource is a string and has route
            array(
                'modules/Accounts',
                true,
                array('relative' => true),
                'a.com',
                'b.com',
                'rest/v11/modules/Accounts',
            ),
            // resource is array, has route
            array(
                array('Notes', 'id-123', 'file', 'filename'),
                true,
                array('relative' => true),
                'a.com',
                'b.com',
                'rest/v11/Notes/id-123/file/filename',
            ),
            // no route
            array(
                'modules/abcd/efg',
                false,
                array('relative' => true),
                'a.com',
                'b.com',
                '',
            ),
            // resource is a string and has route, relative = false
            array(
                'modules/Accounts',
                true,
                array('relative' => false),
                'a.com',
                'b.com',
                self::SITE_URL . 'rest/v11/modules/Accounts',
            ),
            // resource is array, has route, , relative = false
            array(
                array('modules', 'Accounts'),
                true,
                array('relative' => false),
                'a.com',
                'b.com',
                self::SITE_URL . 'rest/v11/modules/Accounts',
            ),
            // resource is array, has route, relative = false
            array(
                array('Notes', 'id-123', 'file', 'filename'),
                true,
                array('relative' => false),
                'a.com',
                'b.com',
                self::SITE_URL . 'rest/v11/Notes/id-123/file/filename',
            ),
            // no route, relative is false
            array(
                'modules/abcd/efg',
                false,
                array('relative' => false),
                'a.com',
                'b.com',
                '',
            ),
            // resource is array, has route, relative = false, URL and SCRIPT_NAME
            array(
                array('Notes', 'id-123', 'file', 'filename'),
                true,
                array('relative' => false),
                'a.com',
                'a.com',
                self::SITE_URL . 'rest/v11/Notes/id-123/file/filename',
            ),
            // resource is array, has route, URL and SCRIPT_NAME is the same
            array(
                array('Notes', 'id-123', 'file', 'filename'),
                true,
                array('relative' => true),
                'a.com',
                'a.com',
                'api/rest.php/v11/Notes/id-123/file/filename',
            ),
            // no route, URL and SCRIPT_NAME
            array(
                'modules/abcd/efg',
                false,
                array('relative' => false),
                'a.com',
                'a.com',
                '',
            ),
        );
    }
}
