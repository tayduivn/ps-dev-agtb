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

use Sabre\DAV;
use Sabre\DAVACL;
use Sugarcrm\Sugarcrm\Dav\Base\Principal;

class Collection extends DAV\SimpleCollection implements DAVACL\IPrincipalCollection
{
    /**
     * Get sugar principal backend
     * @return Principal\SugarPrincipal
     */
    protected function getPrincipalBackend()
    {
        return new Principal\SugarPrincipal();
    }

    /**
     * @inheritdoc
     */
    public function searchPrincipals(array $searchProperties, $test = 'allof')
    {
        $collections = $this->getChildren();
        $backend = $this->getPrincipalBackend();
        $principals = array();
        foreach ($collections as $collection) {
            $principals =
                array_merge($principals, $backend->searchPrincipals($collection->getName(), $searchProperties, $test));
        }

        if (isset($searchProperties['{DAV:}displayname']) &&
            !isset($searchProperties['{http://sabredav.org/ns}email-address'])
        ) {
            array_walk($principals, function (&$principal) use ($backend) {
                $principalInfo = $backend->getPrincipalByPath($this->getName() . '/' . $principal);
                $search =
                    array('{http://sabredav.org/ns}email-address' => $principalInfo['{http://sabredav.org/ns}email-address']);
                $result = $this->searchPrincipals($search);
                if ($result) {
                    $principal = array_shift($result);
                }
            });
        }

        return $principals;
    }

    /**
     * @inheritdoc
     */
    public function findByUri($uri)
    {
        $uri = strtolower($uri);
        if (strpos($uri, 'mailto:') !== 0) {
            return null;
        }
        $result = $this->searchPrincipals(
            array('{http://sabredav.org/ns}email-address' => substr($uri, 7))
        );
        if (isset($result[0])) {
            return $this->getName() . '/' . $result[0];
        }

        return null;
    }
}
