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
 * Class CollectionTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Base\Principal
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Principal\Collection
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Collection::searchPrincipals
     */
    public function testSearchPrincipals()
    {
        $searchProperties = array('{DAV:}displayname' => 'test');
        $test = 'allon';
        $searchResult = array(
            array('contacts/test'),
            array('leads/test1'),
        );

        $principalResult = array(
            'principals/contacts/test' => array('{http://sabredav.org/ns}email-address' => 'test@test.com'),
            'principals/leads/test1' => array('{http://sabredav.org/ns}email-address' => 'test1@test.com'),
        );

        $principalBackend = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal')
                                 ->disableOriginalConstructor()
                                 ->setMethods(array('searchPrincipals', 'getPrincipalByPath'))
                                 ->getMock();

        $sugarCollection = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Collection')
                                ->disableOriginalConstructor()
                                ->setMethods(array('getChildren', 'getPrincipalBackend', 'getName'))
                                ->getMock();

        $collections = array('contacts', 'leads');
        $principalCollections = array();
        $currentIndex = 0;
        foreach ($collections as $name) {
            $principalCollection = $this->getMockBuilder('Sabre\DAVACL\PrincipalCollection')
                                        ->disableOriginalConstructor()
                                        ->setMethods(array('getName'))
                                        ->getMock();

            $principalCollection->expects($this->any())->method('getName')->willReturn($name);
            $principalCollections[] = $principalCollection;
            $principalBackend->expects($this->at($currentIndex))->method('searchPrincipals')
                             ->with($name, $searchProperties, $test)->willReturn($searchResult[$currentIndex]);
            $currentIndex ++;
        }

        foreach ($principalResult as $uri => $data) {
            $principalBackend->expects($this->at($currentIndex ++))->method('getPrincipalByPath')
                             ->with($uri)->willReturn($data);

            $principalBackend->expects($this->at($currentIndex ++))->method('searchPrincipals')
                             ->with($this->anything(), $data, $this->anything())
                             ->willReturn(array());
            $principalBackend->expects($this->at($currentIndex ++))->method('searchPrincipals')
                             ->with($this->anything(), $data, $this->anything())
                             ->willReturn(array());
        }

        $sugarCollection->expects($this->exactly(3))->method('getChildren')->willReturn($principalCollections);
        $sugarCollection->expects($this->exactly(2))->method('getName')->willReturn('principals');
        $sugarCollection->expects($this->exactly(3))->method('getPrincipalBackend')->willReturn($principalBackend);

        $sugarCollection->searchPrincipals($searchProperties, $test);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Collection::findByUri
     */
    public function testFindByUriSearchValidEmail()
    {
        $sugarCollection = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Collection')
                                ->disableOriginalConstructor()
                                ->setMethods(array('searchPrincipals'))
                                ->getMock();

        $sugarCollection->expects($this->once())
                        ->method('searchPrincipals')
                        ->with(array('{http://sabredav.org/ns}email-address' => 'test@test.com'));

        $sugarCollection->findByUri('mailto:test@test.com');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Collection::findByUri
     */
    public function testFindByUriSearchNotEmail()
    {
        $sugarCollection = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Collection')
                                ->disableOriginalConstructor()
                                ->setMethods(array('searchPrincipals'))
                                ->getMock();

        $sugarCollection->expects($this->never())->method('searchPrincipals');

        $sugarCollection->findByUri('eee');
    }
}
