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
namespace Sugarcrm\Sugarcrm\Dav\Base\Principal\Acl;

/**
 * Class Plugin
 * @package Sugarcrm\Sugarcrm\Dav\Base\Acl
 */
class Plugin extends \Sabre\DAVACL\Plugin
{
    /**
     * @inheritdoc
     */
    public function principalSearch(
        array $searchProperties,
        array $requestedProperties,
        $collectionUri = null,
        $test = 'allof'
    ) {
        $matches = parent::principalSearch($searchProperties, $requestedProperties, $collectionUri, $test);

        return $this->filterResults($matches);
    }

    /**
     * Filter result after search to provide unique email
     * @param array $results
     * @return array
     */
    protected function filterResults(array $results)
    {
        $existing = array();
        foreach ($results as $key => $match) {
            if (!isset($match[200]['{urn:ietf:params:xml:ns:caldav}calendar-user-address-set'])) {
                continue;
            }
            $hRefs = array_filter(
                $match[200]['{urn:ietf:params:xml:ns:caldav}calendar-user-address-set']->getHrefs(),
                function ($href) {
                    return strpos($href, 'mailto:') === 0;
                }
            );
            if ($hRefs) {
                $uri = array_shift($hRefs);
                if (!isset($existing[$uri])) {
                    $existing[$uri] = $uri;
                } else {
                    unset($results[$key]);
                }
            }
        }

        return $results;
    }
}
