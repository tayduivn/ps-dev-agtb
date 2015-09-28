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
use Sugarcrm\Sugarcrm\Dav\Base\Principal\Search;

class SugarPrincipal implements BackendInterface
{
    /**
     * Get search class instance
     * @param $prefixPath
     * @return null | \Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Base
     */
    public function getSearchObject($prefixPath)
    {
        $factory = new Search\Factory();
        return $factory->getSearchClass($prefixPath);
    }

    /**
     * SugarCRM has no prefix for users. But we have to return the prefix in URI for caldav server pourposes
     * So return all users with $prefixPath
     * @inheritdoc
     */
    public function getPrincipalsByPrefix($prefixPath = 'principals/users')
    {
        $searchObject = $this->getSearchObject($prefixPath);
        if ($searchObject) {
            return $searchObject->getPrincipalsByPrefix();
        }

        return array();
    }

    /**
     * @inheritdoc
     */
    public function getPrincipalByPath($path)
    {
        $principalComponents = explode('/', $path);
        if (count($principalComponents) != 3) {
            return array();
        }
        $identify = array_pop($principalComponents);
        $prefixPath = implode('/', $principalComponents);
        $searchObject = $this->getSearchObject($prefixPath);
        if ($searchObject) {
            return $searchObject->getPrincipalByIdentify($identify);
        }

        return array();
    }

    /**
     * @inheritdoc
     */
    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
    {
        $searchObject = $this->getSearchObject($prefixPath);
        if ($searchObject) {
            return $searchObject->searchPrincipals($searchProperties, $test);
        }

        return array();
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
