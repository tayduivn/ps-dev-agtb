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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Base\Principal\Search;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class BaseTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Base\Principal\Search
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Base
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Base::getPrincipalArray
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Users::getPrincipalArray
     */
    public function testGetPrincipalArray()
    {
        $beanMock = $this->getMockBuilder('\SugarBean')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        $beanMock->id = 1;
        $beanMock->user_name = 'test';
        $beanMock->full_name = 'test';
        $beanMock->email1 = 'test@test.com';
        $beanMock->module_name = 'test';

        $searchBaseMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Base')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMockForAbstractClass();

        $searchUsersMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Users')
                                ->disableOriginalConstructor()
                                ->setMethods(null)
                                ->getMockForAbstractClass();

        TestReflection::setProtectedValue($searchBaseMock, 'prefixPath', 'principals/users/');
        TestReflection::setProtectedValue($searchUsersMock, 'prefixPath', 'principals/users/');

        $resultBase = TestReflection::callProtectedMethod($searchBaseMock, 'getPrincipalArray', array($beanMock));
        $resultUsers = TestReflection::callProtectedMethod($searchUsersMock, 'getPrincipalArray', array($beanMock));

        $expectedBase = array(
            'id' => 1,
            'uri' => 'principals/users/1',
            '{DAV:}displayname' => 'test',
            '{http://sabredav.org/ns}email-address' => 'test@test.com',
            '{http://sugarcrm.com/ns}x-sugar-module' => 'test',
        );

        $expectedUsers = array(
            'id' => 1,
            'uri' => 'principals/users/test',
            '{DAV:}displayname' => 'test',
            '{http://sabredav.org/ns}email-address' => 'test@test.com',
            '{http://sugarcrm.com/ns}x-sugar-module' => 'test',
        );

        $this->assertEquals($expectedBase, $resultBase);
        $this->assertEquals($expectedUsers, $resultUsers);
    }
}
