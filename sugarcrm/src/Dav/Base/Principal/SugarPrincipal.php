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
     * Get search manager instance
     * @return null | \Sugarcrm\Sugarcrm\Dav\Base\Principal\Manager
     */
    public function getManager()
    {
        return new Manager();
    }

    /**
     * SugarCRM has no prefix for users. But we have to return the prefix in URI for caldav server pourposes
     * So return all users with $prefixPath
     * @inheritdoc
     */
    public function getPrincipalsByPrefix($prefixPath = 'principals/users')
    {
        return $this->getManager()->getPrincipalsByPrefix($prefixPath);
    }

    /**
     * @inheritdoc
     */
    public function getPrincipalByPath($path)
    {
        return $this->getManager()->getPrincipalByIdentify($path);
    }

    /**
     * @inheritdoc
     */
    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
    {
        return $this->getManager()->searchPrincipals($prefixPath, $searchProperties, $test);
    }

    /**
     * @inheritdoc
     */
    public function findByUri($uri, $principalPrefix)
    {
        return $this->getManager()->findByUri($uri, $principalPrefix);
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
