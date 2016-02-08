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

use Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal;

class SugarPrincipalTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function searchUserDisplaynamePrincipalsProvider()
    {
        return array(
            array(
                'prefixPath' => 'principals/users',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'first',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/users',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'last',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/users',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'first_name last_name',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/users',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'last_name first_name',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/users',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'NotFoundValue',
                'find' => false,
            ),
        );
    }

    public function searchContactDisplaynamePrincipalsProvider()
    {
        return array(
            array(
                'prefixPath' => 'principals/contacts',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'first',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/contacts',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'last',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/contacts',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'first_name last_name',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/contacts',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'last_name first_name',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/contacts',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'NotFoundValue',
                'find' => false,
            ),
        );
    }

    public function searchUserEmailPrincipalsProvider()
    {
        return array(
            array(
                'prefixPath' => 'principals/users',
                'email' => 'asdfskdod@test.com',
                'search string' => 'asdfskdod@',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/users',
                'email' => 'asdfskdod@test.com',
                'search string' => 'AsDfskdod@',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/users',
                'email' => 'asdfskdod@test.com',
                'search string' => '@test.com',
                'find' => false,
            ),
            array(
                'prefixPath' => 'principals/users',
                'email' => 'ASDaskdod@test.com',
                'search string' => 'asdaskdod@test.com',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/users',
                'email' => 'asdfskdod@test1.com',
                'search string' => '@test.com',
                'find' => false,
            ),
        );
    }

    public function searchContactEmailPrincipalsProvider()
    {
        return array(
            array(
                'prefixPath' => 'principals/contacts',
                'email' => 'asdfskdod@test.com',
                'search string' => 'asdfskdod@',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/contacts',
                'email' => 'asdfskdod@test.com',
                'search string' => '@test.com',
                'find' => false,
            ),
            array(
                'prefixPath' => 'principals/contacts',
                'email' => 'asdfskdod@test.com',
                'search string' => 'asdfskdod@test.com',
                'find' => true,
            ),
            array(
                'prefixPath' => 'principals/contacts',
                'email' => 'asdfskdod@test1.com',
                'search string' => '@test.com',
                'find' => false,
            ),
        );
    }

    /**
     * Creates two new users and check their exists in principals array
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Users::getPrincipalsByPrefix
     */
    public function testUserGetPrincipalsByPrefix()
    {
        $firstUser = SugarTestUserUtilities::createAnonymousUser();
        $secondUser = SugarTestUserUtilities::createAnonymousUser();

        $firstUser->email1 = 'first@example.com';
        $secondUser->email1 = 'second@example.com';

        $firstUser->save();
        $secondUser->save();

        $localization = new Localization();
        $principalBackend = new SugarPrincipal();

        $principals = $principalBackend->getPrincipalsByPrefix('principals/users');

        $idData = $uriData = $nameData = $emailData = array();
        foreach ($principals as $principal) {
            $idData[] = $principal['id'];
            $uriData[] = $principal['uri'];
            $nameData[] = $principal['{DAV:}displayname'];
            $emailData[] = $principal['{http://sabredav.org/ns}email-address'];
        }

        $this->assertContains($firstUser->id, $idData);
        $this->assertContains($secondUser->id, $idData);

        $this->assertContains('principals/users/' . $firstUser->user_name, $uriData);
        $this->assertContains('principals/users/' . $secondUser->user_name, $uriData);

        $this->assertContains($localization->formatName($firstUser), $nameData);
        $this->assertContains($localization->formatName($secondUser), $nameData);

        $this->assertContains($firstUser->email1, $emailData);
        $this->assertContains($secondUser->email1, $emailData);
    }

    /**
     * Creates two new contacts and check their exists in principals array
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Users::getPrincipalsByPrefix
     */
    public function testContactGetPrincipalsByPrefix()
    {
        $firstContact =
            SugarTestContactUtilities::createContact('', array('email' => 'first@example.com'));
        $secondContact =
            SugarTestContactUtilities::createContact('', array('email' => 'second@example.com'));

        $localization = new Localization();
        $principalBackend = new SugarPrincipal();

        $principals = $principalBackend->getPrincipalsByPrefix('principals/contacts');

        $idData = $uriData = $nameData = $emailData = array();
        foreach ($principals as $principal) {
            $idData[] = $principal['id'];
            $uriData[] = $principal['uri'];
            $nameData[] = $principal['{DAV:}displayname'];
            $emailData[] = $principal['{http://sabredav.org/ns}email-address'];
        }

        $this->assertContains($firstContact->id, $idData);
        $this->assertContains($secondContact->id, $idData);

        $this->assertContains('principals/contacts/' . $firstContact->id, $uriData);
        $this->assertContains('principals/contacts/' . $secondContact->id, $uriData);

        $this->assertContains($localization->formatName($firstContact), $nameData);
        $this->assertContains($localization->formatName($secondContact), $nameData);

        $this->assertContains($firstContact->email1, $emailData);
        $this->assertContains($secondContact->email1, $emailData);
    }

    /**
     * Retrieve principal by path principal/users/username
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Users::getPrincipalByIdentify
     * @throws \Sabre\DAV\Exception
     */
    public function testUserGetPrincipalByPath()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $sugarUser->email1 = 'example@example.com';
        $sugarUser->save();

        $localization = new Localization();

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->getPrincipalByPath('principals/users/' . $sugarUser->user_name);

        $this->assertEquals($sugarUser->id, $result['id']);
        $this->assertEquals('principals/users/' . $sugarUser->user_name, $result['uri']);
        $this->assertEquals($localization->formatName($sugarUser), $result['{DAV:}displayname']);
        $this->assertEquals($sugarUser->email1, $result['{http://sabredav.org/ns}email-address']);
    }

    /**
     * Retrieve principal by path principal/users/username
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Contacts::getPrincipalByIdentify
     * @throws \Sabre\DAV\Exception
     */
    public function testContactGetPrincipalByPath()
    {
        $sugarContact =
            SugarTestContactUtilities::createContact('', array('email' => 'example@example.com'));

        $localization = new Localization();

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->getPrincipalByPath('principals/contacts/' . $sugarContact->id);

        $this->assertEquals($sugarContact->id, $result['id']);
        $this->assertEquals('principals/contacts/' . $sugarContact->id, $result['uri']);
        $this->assertEquals($localization->formatName($sugarContact), $result['{DAV:}displayname']);
        $this->assertEquals($sugarContact->email1, $result['{http://sabredav.org/ns}email-address']);
    }

    /**
     * Test for search user principals by displayname
     * @param string $prefixPath
     * @param string $firstName
     * @param string $lastName
     * @param string $searchString
     * @param bool $isFound
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Users::searchPrincipals
     *
     * @dataProvider searchUserDisplaynamePrincipalsProvider
     */
    public function testUserSearchDisplaynamePrincipals($prefixPath, $firstName, $lastName, $searchString, $isFound)
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $sugarUser->first_name = $firstName;
        $sugarUser->last_name = $lastName;
        $sugarUser->save();

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->searchPrincipals($prefixPath, array('{DAV:}displayname' => $searchString));

        if ($isFound) {
            $this->assertContains($prefixPath . '/' . $sugarUser->user_name, $result);
        } else {
            $this->assertNotContains($prefixPath . '/' . $sugarUser->user_name, $result);
        }
    }

    /**
     * Test for search contact principals by displayname
     * @param string $prefixPath
     * @param string $firstName
     * @param string $lastName
     * @param string $searchString
     * @param bool $isFound
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Contacts::searchPrincipals
     *
     * @dataProvider searchContactDisplaynamePrincipalsProvider
     */
    public function testContactsSearchDisplaynamePrincipals($prefixPath, $firstName, $lastName, $searchString, $isFound)
    {
        $sugarContact =
            SugarTestContactUtilities::createContact('', array('first_name' => $firstName, 'last_name' => $lastName));

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->searchPrincipals($prefixPath, array('{DAV:}displayname' => $searchString));

        if ($isFound) {
            $this->assertContains($prefixPath . '/' . $sugarContact->id, $result);
        } else {
            $this->assertNotContains($prefixPath . '/' . $sugarContact->id, $result);
        }
    }

    /**
     * Test for searching principal by email
     * @param string $prefixPath
     * @param string $email
     * @param string $searchString
     * @param bool $isFound
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Users::searchPrincipals
     *
     * @dataProvider searchUserEmailPrincipalsProvider
     */
    public function testUserSearchEmailPrincipals($prefixPath, $email, $searchString, $isFound)
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $sugarUser->email1 = $email;
        $sugarUser->save();

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->searchPrincipals(
            $prefixPath,
            array('{http://sabredav.org/ns}email-address' => $searchString)
        );

        if ($isFound) {
            $this->assertContains($prefixPath . '/' . $sugarUser->user_name, $result);
        } else {
            $this->assertNotContains($prefixPath . '/' . $sugarUser->user_name, $result);
        }
    }

    /**
     * Test for searching principal by email
     * @param string $prefixPath
     * @param string $email
     * @param string $searchString
     * @param bool $isFound
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Contacts::searchPrincipals
     *
     * @dataProvider searchContactEmailPrincipalsProvider
     */
    public function testContactSearchEmailPrincipals($prefixPath, $email, $searchString, $isFound)
    {
        $sugarContact =
            SugarTestContactUtilities::createContact('', array('email' => $email));

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->searchPrincipals(
            $prefixPath,
            array('{http://sabredav.org/ns}email-address' => $searchString)
        );

        if ($isFound) {
            $this->assertContains($prefixPath . '/' . $sugarContact->id, $result);
        } else {
            $this->assertNotContains($prefixPath . '/' . $sugarContact->id, $result);
        }
    }

    /**
     * Tests searching principals by displayname and email-address with allof and allon flag
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Users::searchPrincipals
     */
    public function testUserSearchPrincipalsMulti()
    {
        $firstUser = SugarTestUserUtilities::createAnonymousUser();
        $firstUser->email1 = mt_rand() . '@test.com';
        $firstUser->save();

        $secondUser = SugarTestUserUtilities::createAnonymousUser();
        $secondUser->first_name = 'first_name';
        $secondUser->last_name = 'first_name';
        $secondUser->email1 = mt_rand() . '@test1.com';
        $secondUser->save();

        $searchProperties =
            array('{DAV:}displayname' => 'first', '{http://sabredav.org/ns}email-address' => $firstUser->email1);

        $prefixPath = 'principals/users';

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->searchPrincipals($prefixPath, $searchProperties, 'allof');

        $this->assertEquals(array(), $result);

        $result = $principalBackend->searchPrincipals($prefixPath, $searchProperties, 'allon');

        $this->assertContains($prefixPath . '/' . $firstUser->user_name, $result);
        $this->assertContains($prefixPath . '/' . $secondUser->user_name, $result);
    }

    /**
     * Tests searching principals by displayname and email-address with allof and allon flag
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Contacts::searchPrincipals
     */
    public function testContactSearchPrincipalsMulti()
    {
        $firstContact =
            SugarTestContactUtilities::createContact('', array('email' => mt_rand() . '@test.com'));

        $secondContact =
            SugarTestContactUtilities::createContact(
                '',
                array('email' => mt_rand() . '@test1.com', 'first_name' => 'frst_name', 'lst_name' => 'first_name')
            );

        $searchProperties =
            array('{DAV:}displayname' => 'frst', '{http://sabredav.org/ns}email-address' => $firstContact->email1);

        $prefixPath = 'principals/contacts';

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->searchPrincipals($prefixPath, $searchProperties, 'allof');

        $this->assertEquals(array(), $result);

        $result = $principalBackend->searchPrincipals($prefixPath, $searchProperties, 'allon');

        $this->assertContains($prefixPath . '/' . $firstContact->id, $result);
        $this->assertContains($prefixPath . '/' . $secondContact->id, $result);
    }
}
