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

use Sugarcrm\Sugarcrm\Dav\Base\Principal\SugarPrincipal;

class SugarPrincipalTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function searchDisplaynamePrincipalsProvider()
    {
        return array(
            array(
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'first',
                'find' => true,
            ),
            array(
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'last',
                'find' => true,
            ),
            array(
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'first_name last_name',
                'find' => true,
            ),
            array(
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'last_name first_name',
                'find' => true,
            ),
            array(
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'search string' => 'NotFoundValue',
                'find' => false,
            ),
        );
    }

    public function searchEmailPrincipalsProvider()
    {
        return array(
            array(
                'email' => 'asdfskdod@test.com',
                'search string' => 'asdfskdod@',
                'find' => true,
            ),
            array(
                'email' => 'asdfskdod@test.com',
                'search string' => '@test.com',
                'find' => true,
            ),
            array(
                'email' => 'asdfskdod@test.com',
                'search string' => '@test.com',
                'find' => true,
            ),
            array(
                'email' => 'asdfskdod@test1.com',
                'search string' => '@test.com',
                'find' => false,
            ),
        );
    }

    /**
     * Creates two new users and check their exists in principals array
     */
    public function testGetPrincipalsByPrefix()
    {
        $firstUser = SugarTestUserUtilities::createAnonymousUser();
        $secondUser = SugarTestUserUtilities::createAnonymousUser();

        $firstUser->email1 = 'first@example.com';
        $secondUser->email1 = 'second@example.com';

        $firstUser->save();
        $secondUser->save();

        $localization = new Localization();
        $principalBackend = new SugarPrincipal();

        $principals = $principalBackend->getPrincipalsByPrefix('principals1');

        $idData = $uriData = $nameData = $emailData = array();
        foreach ($principals as $principal) {
            $idData[] = $principal['id'];
            $uriData[] = $principal['uri'];
            $nameData[] = $principal['{DAV:}displayname'];
            $emailData[] = $principal['{http://sabredav.org/ns}email-address'];
        }

        $this->assertContains($firstUser->id, $idData);
        $this->assertContains($secondUser->id, $idData);

        $this->assertContains('principals1/' . $firstUser->user_name, $uriData);
        $this->assertContains('principals1/' . $secondUser->user_name, $uriData);

        $this->assertContains($localization->formatName($firstUser), $nameData);
        $this->assertContains($localization->formatName($secondUser), $nameData);

        $this->assertContains($firstUser->email1, $emailData);
        $this->assertContains($secondUser->email1, $emailData);
    }

    /**
     * Retrieve principal by path principal/username
     * @throws \Sabre\DAV\Exception
     */
    public function testGetPrincipalByPath()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $sugarUser->email1 = 'example@example.com';
        $sugarUser->save();

        $localization = new Localization();

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->getPrincipalByPath('principal/' . $sugarUser->user_name);

        $this->assertEquals($sugarUser->id, $result['id']);
        $this->assertEquals('principal/' . $sugarUser->user_name, $result['uri']);
        $this->assertEquals($localization->formatName($sugarUser), $result['{DAV:}displayname']);
        $this->assertEquals($sugarUser->email1, $result['{http://sabredav.org/ns}email-address']);
    }

    /**
     * Test for search principals by displayname
     * @param string $firstName
     * @param string $lastName
     * @param string $searchString
     * @param string $isFound
     *
     * @dataProvider searchDisplaynamePrincipalsProvider
     */
    public function testSearchDisplaynamePrincipals($firstName, $lastName, $searchString, $isFound)
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $sugarUser->first_name = $firstName;
        $sugarUser->last_name = $lastName;
        $sugarUser->save();

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->searchPrincipals('principals/', array('{DAV:}displayname' => $searchString));

        if ($isFound) {
            $this->assertContains('principals/' . $sugarUser->user_name, $result);
        } else {
            $this->assertNotContains('principals/' . $sugarUser->user_name, $result);
        }
    }

    /**
     * Test for searching principal by email
     * @param $email
     * @param $searchString
     * @param $isFound
     *
     * @dataProvider searchEmailPrincipalsProvider
     */
    public function testSearchEmailPrincipals($email, $searchString, $isFound)
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $sugarUser->email1 = $email;
        $sugarUser->save();

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->searchPrincipals('principals/',
            array('{http://sabredav.org/ns}email-address' => $searchString));

        if ($isFound) {
            $this->assertContains('principals/' . $sugarUser->user_name, $result);
        } else {
            $this->assertNotContains('principals/' . $sugarUser->user_name, $result);
        }
    }

    /**
     * Tests searching principals by displayname and email-address with allof and allon flag
     */
    public function testSearchPrincipalsMulti()
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

        $principalBackend = new SugarPrincipal();
        $result = $principalBackend->searchPrincipals('principals/', $searchProperties, 'allof');

        $this->assertEquals(array(), $result);

        $result = $principalBackend->searchPrincipals('principals/', $searchProperties, 'allon');

        $this->assertContains('principals/' . $firstUser->user_name, $result);
        $this->assertContains('principals/' . $secondUser->user_name, $result);
    }
}
