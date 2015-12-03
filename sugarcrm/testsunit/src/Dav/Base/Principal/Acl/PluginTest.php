<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
namespace Sugarcrm\SugarcrmTestsUnit\Dav\Base\Principal\Acl;

use Sabre\DAV\Xml\Property\Href;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class PluginTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Base\Principal\Acl
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Principal\Acl\Plugin
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{

    public function filterResultsProvider()
    {
        $href = array(
            new Href(array('/foo/bar', 'mailto:test1@test.com')),
            new Href(array('/foo/bar1', 'mailto:test@test.com')),
            new Href(array('/foo/bar2', 'mailto:test@test.com')),
            new Href(array('/foo/bar3', 'mailto:test2@test.com')),
        );

        return array(
            array(
                'searchResult' => array(
                    0 => array(
                        '{DAV:}displayname' => 'Test',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[0])
                    ),
                    1 => array(
                        '{DAV:}displayname' => 'Test1',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[1])
                    ),
                    2 => array(
                        '{DAV:}displayname' => 'Test2',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[2])
                    ),
                    3 => array(
                        '{DAV:}displayname' => 'Test3',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[3])
                    ),
                ),
                'expectedResult' => array(
                    0 => array(
                        '{DAV:}displayname' => 'Test',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[0])
                    ),
                    1 => array(
                        '{DAV:}displayname' => 'Test1',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[1])
                    ),
                    3 => array(
                        '{DAV:}displayname' => 'Test3',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[3])
                    ),
                ),
            ),
            array(
                'searchResult' => array(
                    0 => array(
                        '{DAV:}displayname' => 'Test',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[0])
                    ),
                    1 => array(
                        '{DAV:}displayname' => 'Test1',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[1])
                    ),
                    2 => array(
                        '{DAV:}displayname' => 'Test3',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[3])
                    ),
                ),
                'expectedResult' => array(
                    0 => array(
                        '{DAV:}displayname' => 'Test',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[0])
                    ),
                    1 => array(
                        '{DAV:}displayname' => 'Test1',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[1])
                    ),
                    2 => array(
                        '{DAV:}displayname' => 'Test3',
                        200 => array('{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => $href[3])
                    ),
                ),
            ),
        );
    }

    /**
     * @param array $searchResult
     * @param array $expectedResult
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\Acl\Plugin::filterResults
     * @dataProvider filterResultsProvider
     */
    public function testFilterResults($searchResult, $expectedResult)
    {
        $aclPlugin = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Acl\Plugin')
                          ->disableOriginalConstructor()
                          ->setMethods(null)
                          ->getMock();

        $result = TestReflection::callProtectedMethod($aclPlugin, 'filterResults', array($searchResult));

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\Acl\Plugin::principalSearch
     */
    public function testPrincipalSearch()
    {
        $aclPlugin = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Acl\Plugin')
                          ->disableOriginalConstructor()
                          ->setMethods(array('filterResults'))
                          ->getMock();

        TestReflection::setProtectedValue($aclPlugin, 'principalCollectionSet', array());

        $aclPlugin->expects($this->once())->method('filterResults')->with(array());

        $aclPlugin->principalSearch(array('{DAV:}displayname' => 'test'), array(), null);
    }
}
