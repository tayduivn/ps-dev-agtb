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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Base\Principal;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class SugarPrincipalTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Base\Principal
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal
 */
class SugarPrincipalTest extends \PHPUnit_Framework_TestCase
{
    /** Mock object for Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal
     * @var
     */
    protected $principalMock;

    /**
     * Mock object for Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper
     * @var
     */
    protected $userHelperMock;

    /**
     * Mock object for \SugarQuery
     * @var
     */
    protected $sugarQueryMock;

    /**
     * Mock object for \User
     * @var
     */
    protected $userBeanMock;

    /**
     * Mock object for \Link2
     * @var
     */
    protected $relationShipMock;

    public function getPrincipalsByPrefixProvider()
    {
        return array(
            array(
                'prefixPath' => 'principals',
                'queryResult' => array(
                    array(
                        'id' => '1',
                        'user_name' => 'user_name',
                        'full_name' => 'first_name',
                        'email_address' => 'test@test.com'
                    ),
                    array(
                        'id' => '2',
                        'user_name' => 'user_name1',
                        'full_name' => 'first_name1',
                        'email_address' => 'test@test1.com'
                    ),
                ),
                'expectedResult' => array(
                    array(
                        'id' => '1',
                        'uri' => 'principals/user_name',
                        '{DAV:}displayname' => 'first_name last_name',
                        '{http://sabredav.org/ns}email-address' => 'test@test.com',
                    ),
                ),
            ),
        );
    }

    public function findByUriProvider()
    {
        return array(
            array(
                'uri' => 'mailto:test@test.com',
                'search' => array('{http://sabredav.org/ns}email-address' => 'test@test.com'),
                'principalPrefix' => 'principals',
            ),
        );
    }

    public function getPrincipalByPathProvider()
    {
        return array(
            array(
                'path' => 'principals/user',
                'user_name' => 'user',
                'full_name' => 'first_name last_name',
                'email' => 'test@test.com',
                'principalPrefix' => 'principals',
            ),
        );
    }

    public function searchPrincipalsProvider()
    {
        return array(
            array(
                'prefixPath' => 'principals',
                'searchProperties' => array(
                    '{DAV:}displayname' => 'test',
                ),
                'methods' => array(
                    'getNameFormatFields' => array('expects' => 1, 'return' => array('first_name', 'last_name')),
                    'contains' => array('expects' => 2),
                    'load_relationship' => array('expects' => 0),
                    'buildJoinSugarQuery' => array('expects' => 0, 'param' => array()),
                ),
                'test' => 'allof',
                'result' => array(
                    'principals/test',
                ),
            ),
            array(
                'prefixPath' => 'principals',
                'searchProperties' => array(
                    '{DAV:}displayname' => 'test1 test2',
                ),
                'methods' => array(
                    'getNameFormatFields' => array('expects' => 1, 'return' => array('first_name', 'last_name')),
                    'contains' => array('expects' => 4),
                    'load_relationship' => array('expects' => 0),
                    'buildJoinSugarQuery' => array('expects' => 0, 'param' => array()),
                ),
                'test' => 'allof',
                'result' => array(
                    'principals/test',
                ),
            ),
            array(
                'prefixPath' => 'principals',
                'searchProperties' => array(
                    '{DAV:}displayname' => 'test1 test2',
                ),
                'methods' => array(
                    'getNameFormatFields' => array('expects' => 1, 'return' => array('first_name')),
                    'contains' => array('expects' => 2),
                    'load_relationship' => array('expects' => 0),
                    'buildJoinSugarQuery' => array('expects' => 0, 'param' => array()),
                ),
                'test' => 'allof',
                'result' => array(
                    'principals/test',
                ),
            ),
            array(
                'prefixPath' => 'principals',
                'searchProperties' => array(
                    '{DAV:}displayname' => 'test1',
                ),
                'methods' => array(
                    'getNameFormatFields' => array('expects' => 1, 'return' => array('first_name')),
                    'contains' => array('expects' => 1),
                    'load_relationship' => array('expects' => 0),
                    'buildJoinSugarQuery' => array('expects' => 0, 'param' => array()),
                ),
                'test' => 'allof',
                'result' => array(
                    'principals/test',
                ),
            ),
            array(
                'prefixPath' => 'principals',
                'searchProperties' => array(
                    '{http://sabredav.org/ns}email-address' => 'test@test.com',
                ),
                'methods' => array(
                    'getNameFormatFields' => array('expects' => 1, 'return' => array('first_name')),
                    'contains' => array('expects' => 1),
                    'load_relationship' => array('expects' => 1),
                    'buildJoinSugarQuery' => array('expects' => 1, 'param' => array('joinType' => 'INNER')),
                ),
                'test' => 'allof',
                'result' => array(
                    'principals/test',
                ),
            ),
            array(
                'prefixPath' => 'principals',
                'searchProperties' => array(
                    '{http://sabredav.org/ns}email-address' => 'test@test.com',
                ),
                'methods' => array(
                    'getNameFormatFields' => array('expects' => 1, 'return' => array('first_name')),
                    'contains' => array('expects' => 1),
                    'load_relationship' => array('expects' => 1),
                    'buildJoinSugarQuery' => array('expects' => 1, 'param' => array('joinType' => 'LEFT')),
                ),
                'test' => 'allon',
                'result' => array(
                    'principals/test',
                ),
            ),
        );
    }

    /**
     * Setting up base mocks for tests
     * @param array $principalMethods Array of methods to mock in Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal
     * @param array $userHelperMethods Array of methods to mock in Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper
     */
    protected function setBaseMocks(array $principalMethods, array $userHelperMethods)
    {
        $this->principalMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal')
                                    ->setMethods($principalMethods)
                                    ->getMock();

        $this->userHelperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\UserHelper')
                                     ->disableOriginalConstructor()
                                     ->setMethods($userHelperMethods)
                                     ->getMock();

        $this->userBeanMock = $this->getMockBuilder('User')
                                   ->disableOriginalConstructor()
                                   ->setMethods(array('load_relationship', 'fetchFromQuery'))
                                   ->getMock();

        $this->sugarQueryMock = $this->getMockBuilder('SugarQuery')
                                     ->disableOriginalConstructor()
                                     ->setMethods(array('select', 'from', 'execute', 'where'))
                                     ->getMock();

        $this->relationShipMock = $this->getMockBuilder('Link2')
                                       ->disableOriginalConstructor()
                                       ->setMethods(array('buildJoinSugarQuery'))
                                       ->getMockForAbstractClass();

        $this->userBeanMock->email_addresses_primary = $this->relationShipMock;

        $this->principalMock->method('getUserHelper')->willReturn($this->userHelperMock);
        $this->principalMock->method('getSugarQuery')->willReturn($this->sugarQueryMock);
    }

    /**
     * @param string $prefixPath principal prefix
     * @param array $queryResult The result of \SugarQuery::execute
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::getPrincipalsByPrefix
     *
     * @dataProvider getPrincipalsByPrefixProvider
     */
    public function testGetPrincipalsByPrefix($prefixPath, array $queryResult)
    {
        $this->setBaseMocks(
            array('getSugarQuery', 'getUserHelper'),
            array('getUserBean', 'setPrincipalPrefix', 'getPrincipalArrayByUser', 'getUserByUserName'));

        $sugarQueryBuilderSelect = $this->getMockBuilder('SugarQuery_Builder_Select')
                                        ->disableOriginalConstructor()
                                        ->setMethods(array('addField'))
                                        ->getMock();

        $beans = array();

        foreach ($queryResult as $row) {
            $beanMock = $this->getMockBuilder('User')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

            foreach ($row as $key => $value) {
                $beanMock->$key = $value;
            }

            $beans[] = $beanMock;
        }

        $this->userHelperMock->expects($this->once())->method('getUserBean')->willReturn($this->userBeanMock);
        $this->userHelperMock->expects($this->once())->method('setPrincipalPrefix')->with($prefixPath);
        $this->userHelperMock->expects($this->exactly(count($beans)))->method('getPrincipalArrayByUser');

        $this->userBeanMock->expects($this->once())->method('load_relationship')->willReturn(true);
        $this->userBeanMock->expects($this->once())->method('fetchFromQuery')->willReturn($beans);

        $this->sugarQueryMock->expects($this->once())->method('select')->willReturn($sugarQueryBuilderSelect);


        $this->principalMock->getPrincipalsByPrefix($prefixPath);
    }

    /**
     * @param string $uri
     * @param array $searchPattern
     * @param string $principalPrefix
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::findByUri
     *
     * @dataProvider findByUriProvider
     */
    public function testFindByUri($uri, array $searchPattern, $principalPrefix)
    {
        $sugarPrincipal = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal')
                               ->disableOriginalConstructor()
                               ->setMethods(array('searchPrincipals'))
                               ->getMock();

        $sugarPrincipal->expects($this->once())->method('searchPrincipals')->with($principalPrefix, $searchPattern);

        $sugarPrincipal->findByUri($uri, $principalPrefix);
    }

    /**
     * @param string $path
     * @param string $username
     * @param string $full_name
     * @param string $email
     * @param string $principalPrefix
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::getPrincipalByPath
     *
     * @dataProvider getPrincipalByPathProvider
     */
    public function testGetPrincipalByPath($path, $username, $full_name, $email, $principalPrefix)
    {
        $this->setBaseMocks(
            array('getSugarQuery', 'getUserHelper'),
            array('getUserBean', 'getUserByUserName'));

        $this->userHelperMock->expects($this->once())->method('getUserByUserName')->with($username)
                             ->willReturn($this->userBeanMock);

        $this->userBeanMock->id = 1;
        $this->userBeanMock->user_name = $username;
        $this->userBeanMock->full_name = $full_name;
        $this->userBeanMock->email1 = $email;

        $result = $this->principalMock->getPrincipalByPath($path);

        $this->assertEquals(array(
            'id' => 1,
            'uri' => $path,
            '{DAV:}displayname' => $full_name,
            '{http://sabredav.org/ns}email-address' => $email,
        ), $result);
    }

    /**
     * @param string $prefixPath
     * @param array $searchProperties
     * @param array $methods rules for calling methods
     * @param string $test
     * @param array $expected found principals
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::searchPrincipals
     *
     * @dataProvider searchPrincipalsProvider
     */
    public function testSearchPrincipals($prefixPath, array $searchProperties, array $methods, $test, $expected)
    {
        $this->setBaseMocks(
            array('getSugarQuery', 'getUserHelper'),
            array('getUserBean', 'getUserByUserName', 'getNameFormatFields'));

        $this->userBeanMock->user_name = 'test';

        $sugarQueryBuilder = $this->getMockBuilder('SugarQuery_Builder_Where')
                                  ->disableOriginalConstructor()
                                  ->setMethods(array('contains', 'queryOr', 'queryAnd'))
                                  ->getMock();

        $this->sugarQueryMock->expects($this->any())->method('where')->willReturn($sugarQueryBuilder);

        $this->userHelperMock->expects($this->once())->method('getUserBean')->willReturn($this->userBeanMock);
        $this->userHelperMock->expects($this->once())
                             ->method('getNameFormatFields')
                             ->with($this->userBeanMock)
                             ->willReturn($methods['getNameFormatFields']['return']);

        $this->relationShipMock->expects($this->exactly($methods['buildJoinSugarQuery']['expects']))
                               ->method('buildJoinSugarQuery')
                               ->with($this->sugarQueryMock, $methods['buildJoinSugarQuery']['param']);
        
        $this->userBeanMock->expects($this->exactly($methods['load_relationship']['expects']))
                           ->method('load_relationship')
                           ->with('email_addresses_primary')
                           ->willReturn(true);

        $sugarQueryBuilder->expects($this->any())->method('queryOr')->willReturn($sugarQueryBuilder);
        $sugarQueryBuilder->expects($this->any())->method('queryAnd')->willReturn($sugarQueryBuilder);
        $sugarQueryBuilder->expects($this->exactly($methods['contains']['expects']))->method('contains');

        $this->userBeanMock->expects($this->once())->method('fetchFromQuery')->willReturn(array($this->userBeanMock));

        $result = $this->principalMock->searchPrincipals($prefixPath, $searchProperties, $test);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::updatePrincipal
     *
     * @expectedException \Sabre\DAV\Exception\Forbidden
     */
    public function testUpdatePrincipal()
    {
        $sugarPrincipal = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        $propPatch = $this->getMockBuilder('\Sabre\DAV\PropPatch')
                          ->disableOriginalConstructor()
                          ->setMethods(null)
                          ->getMock();
        $sugarPrincipal->updatePrincipal('principals/test', $propPatch);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::setGroupMemberSet
     *
     * @expectedException \Sabre\DAV\Exception\Forbidden
     */
    public function testSetGroupMemberSet()
    {
        $sugarPrincipal = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        $sugarPrincipal->setGroupMemberSet('principals/test', array());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::getGroupMembership
     */
    public function testGetGroupMembership()
    {
        $sugarPrincipal = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        $this->assertEquals(array(), $sugarPrincipal->getGroupMembership('principals/test'));
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::getGroupMemberSet
     */
    public function testGetGroupMemberSet()
    {
        $sugarPrincipal = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        $this->assertEquals(array(), $sugarPrincipal->getGroupMemberSet('principals/group'));
    }
}
