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

namespace Sugarcrm\Sugarcrm\Dav\Base\Principal;

use Sabre\DAVACL\PrincipalBackend\BackendInterface;
use Sabre\DAV;

use Sugarcrm\Sugarcrm\Dav\Base\Helper;

class SugarPrincipal implements BackendInterface
{
    /**
     * Instance of UserHelper
     * @var Helper\UserHelper
     */
    protected static $userHelperInstance = null;

    /**
     * Get SugarQuery Instance
     * @return \SugarQuery
     */
    public function getSugarQuery()
    {
        return new \SugarQuery();
    }

    /**
     * Get UserHelper
     * @return Helper\UserHelper
     */
    public function getUserHelper()
    {
        if (is_null(self::$userHelperInstance)) {
            self::$userHelperInstance = new Helper\UserHelper();
        }

        return self::$userHelperInstance;
    }

    /**
     * SugarCRM has no prefix for users. But we have to return the prefix in URI for caldav server pourposes
     * So return all users with $prefixPath
     * @inheritdoc
     */
    public function getPrincipalsByPrefix($prefixPath = 'principals')
    {
        $userHelper = $this->getUserHelper();
        $userBean = $userHelper->getUserBean();
        $userHelper->setPrincipalPrefix($prefixPath);
        $principals = array();

        if ($userBean->load_relationship('email_addresses_primary')) {

            $usersQuery = $this->getSugarQuery();
            $usersQuery->from($userBean);

            $userBean->email_addresses_primary->buildJoinSugarQuery($usersQuery, array('joinType' => 'LEFT'));
            $usersQuery->select()->addField('email_addresses.email_address', array('alias'=>'email1'));

            $beans = $userBean->fetchFromQuery($usersQuery);

            foreach ($beans as $user) {
                $principals[] = $userHelper->getPrincipalArrayByUser($user);
            }
        }

        return $principals;
    }

    /**
     * @inheritdoc
     */
    public function getPrincipalByPath($path)
    {
        $principal = array();
        $userHelper = $this->getUserHelper();
        $user = $userHelper->getUserByPrincipalString($path);
        if ($user) {
            $principal = $userHelper->getPrincipalArrayByUser($user);
        }
        return $principal;
    }

    /**
     * @inheritdoc
     */
    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
    {
        $userHelper = $this->getUserHelper();
        $userHelper->setPrincipalPrefix($prefixPath);

        $userBean = $userHelper->getUserBean();
        $usersQuery = $this->getSugarQuery();

        $searchFields = $userHelper->getNameFormatFields($userBean);

        $usersQuery->from($userBean);

        $mainOrQuery = $usersQuery->where()->queryOr();
        $andQuery = $mainOrQuery->queryAnd();

        foreach ($searchProperties as $property => $value) {
            switch ($property) {
                case '{DAV:}displayname':
                    $explodeName = explode(' ', $value);

                    foreach ($explodeName as $part) {
                        if ($part) {
                            $orQuery = $andQuery->queryOr();
                            foreach ($searchFields as $filed) {
                                $orQuery->contains($filed, $part);
                            }
                        }
                    }
                    break;
                case '{http://sabredav.org/ns}email-address':
                    if ($userBean->load_relationship('email_addresses_primary')) {

                        $userBean->email_addresses_primary->buildJoinSugarQuery($usersQuery,
                            array('joinType' => ($test == 'allof' ? 'INNER' : 'LEFT')));

                        $usersQuery->select(array('email_addresses.email_address'));

                        if ($test == 'allof') {
                            $usersQuery->where()->contains('email_addresses.email_address', $value);
                        } else {
                            $mainOrQuery->contains('email_addresses.email_address', $value);
                        }
                    } else {
                        return array();
                    }
                    break;
                default:
                    return array();
            }
        }

        $principals = array();
        $beans = $userBean->fetchFromQuery($usersQuery);

        foreach ($beans as $user) {
            $principals[] = $userHelper->getPrincipalStringByUser($user);
        }

        return $principals;
    }

    /**
     * @inheritdoc
     */
    public function findByUri($uri, $principalPrefix)
    {
        $uri = strtolower($uri);
        if (strpos($uri, 'mailto:') !== 0) {
            return null;
        }
        $result = $this->searchPrincipals(
            $principalPrefix,
            array('{http://sabredav.org/ns}email-address' => substr($uri, 7))
        );

        if ($result) {
            return $result[0];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getGroupMemberSet($principal)
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getGroupMembership($principal)
    {
        return array();
    }

    /**
     * Don't allows to update principal groups from external client
     * @inheritdoc
     */
    public function setGroupMemberSet($principal, array $members)
    {
        throw new DAV\Exception\Forbidden('setGroupMemberSet not allowed');
    }

    /**
     * Don't allows to update principals from external client
     * @inheritdoc
     */
    public function updatePrincipal($path, \Sabre\DAV\PropPatch $propPatch)
    {
        throw new DAV\Exception\Forbidden('updatePrincipal not allowed');
    }
}
