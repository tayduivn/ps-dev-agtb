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

namespace Sugarcrm\SugarcrmTests\Dav\Base\Principal;

/**
 * Class SugarPrincipalTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Base\Principal
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal
 */
class SugarPrincipalTest extends \PHPUnit_Framework_TestCase
{
    public function getPrincipalsByPrefixProvider()
    {
        return array(
            array(
                'prefixPath' => 'principals/users',
                'expectedMethod' => 'getPrincipalsByPrefix',
                'expectedCount' => 1,
            ),
            array(
                'prefixPath' => 'principals/contacts',
                'expectedMethod' => 'getPrincipalsByPrefix',
                'expectedCount' => 1,
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
                'searchPrincipalsCallCount' => 1,
            ),
            array(
                'uri' => 'MAILTO:test@test.com',
                'search' => array('{http://sabredav.org/ns}email-address' => 'test@test.com'),
                'principalPrefix' => 'principals',
                'searchPrincipalsCallCount' => 1,
            ),
            array(
                'uri' => 'test@test.com',
                'search' => array('{http://sabredav.org/ns}email-address' => 'test@test.com'),
                'principalPrefix' => 'principals',
                'searchPrincipalsCallCount' => 0,
            ),
            array(
                'uri' => 'aamailto:test@test.com',
                'search' => array('{http://sabredav.org/ns}email-address' => 'test@test.com'),
                'principalPrefix' => 'principals',
                'searchPrincipalsCallCount' => 0,
            ),
            array(
                'uri' => 'test',
                'search' => array(),
                'principalPrefix' => 'principals',
                'searchPrincipalsCallCount' => 0,
            ),
        );
    }

    public function getPrincipalByPathProvider()
    {
        return array(
            array(
                'path' => 'principals/users/user1',
                'prefixPath' => 'principals/users',
                'identify' => 'user1',
                'searchObject' => 'Users',
                'expectedMethod' => 'getPrincipalByIdentify',
                'expectedCount' => 1,
            ),
        );
    }

    public function searchPrincipalsProvider()
    {
        return array(
            array(
                'prefixPath' => 'principals/users',
                'expectedMethod' => 'searchPrincipals',
                'expectedCount' => 1,
            ),
            array(
                'prefixPath' => 'principals/contacts',
                'expectedMethod' => 'searchPrincipals',
                'expectedCount' => 1,
            ),
        );
    }

    /**
     * @param string $prefixPath
     * @param string $expectedMethod
     * @param string $expectedCount
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::getPrincipalsByPrefix
     *
     * @dataProvider getPrincipalsByPrefixProvider
     */
    public function testGetPrincipalsByPrefix($prefixPath, $expectedMethod, $expectedCount)
    {
        $principalMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal')
                              ->setMethods(array('getManager'))
                              ->getMock();
        $managerMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Manager')
                            ->setMethods(array('getPrincipalsByPrefix'))
                            ->getMock();

        $principalMock->expects($this->once())->method('getManager')->willReturn($managerMock);

        $managerMock->expects($this->exactly($expectedCount))->method($expectedMethod);

        $principalMock->getPrincipalsByPrefix($prefixPath);

    }

    /**
     * @param string $uri
     * @param array $searchPattern
     * @param string $principalPrefix
     * @param int $searchPrincipalsCallCount
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::findByUri
     *
     * @dataProvider findByUriProvider
     */
    public function testFindByUri($uri, array $searchPattern, $principalPrefix, $searchPrincipalsCallCount)
    {
        $sugarPrincipal = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal')
                               ->disableOriginalConstructor()
                               ->setMethods(array('getManager'))
                               ->getMock();

        $managerMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Manager')
                            ->setMethods(array('searchPrincipals'))
                            ->getMock();

        $sugarPrincipal->expects($this->once())->method('getManager')->willReturn($managerMock);

        $managerMock->expects($this->exactly($searchPrincipalsCallCount))
                    ->method('searchPrincipals')
                    ->with($principalPrefix, $searchPattern);

        $sugarPrincipal->findByUri($uri, $principalPrefix);
    }

    /**
     * @param string $path
     * @param string $prefixPath
     * @param string $identify
     * @param string $searchObject
     * @param string $expectedMethod
     * @param string $expectedCount
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::getPrincipalByPath
     *
     * @dataProvider getPrincipalByPathProvider
     */
    public function testGetPrincipalByPath(
        $path,
        $prefixPath,
        $identify,
        $searchObject,
        $expectedMethod,
        $expectedCount
    ) {
        $principalMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal')
                              ->setMethods(array('getManager'))
                              ->getMock();

        $managerMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Manager')
                            ->setMethods(array('getSearchObject'))
                            ->getMock();

        $searchMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\\' . $searchObject)
                           ->setConstructorArgs(array($prefixPath))
                           ->setMethods(array('getPrincipalByIdentify'))
                           ->getMock();

        $principalMock->expects($this->once())->method('getManager')->willReturn($managerMock);
        $managerMock->expects($this->once())->method('getSearchObject')->willReturn($searchMock);


        $searchMock->expects($this->exactly($expectedCount))->method($expectedMethod)->with($identify);

        $principalMock->getPrincipalByPath($path);
    }

    /**
     * @param string $prefixPath
     * @param string $expectedMethod
     * @param string $expectedCount
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal::searchPrincipals
     *
     * @dataProvider searchPrincipalsProvider
     */
    public function testSearchPrincipals($prefixPath, $expectedMethod, $expectedCount)
    {
        $principalMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal')
                              ->setMethods(array('getManager'))
                              ->getMock();

        $managerMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Manager')
                            ->setMethods(array('searchPrincipals'))
                            ->getMock();

        $principalMock->expects($this->once())->method('getManager')->willReturn($managerMock);

        $managerMock->expects($this->exactly($expectedCount))->method($expectedMethod);

        $principalMock->searchPrincipals($prefixPath, array());
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
