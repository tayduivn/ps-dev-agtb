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

namespace Sugarcrm\Sugarcrm\Dav\Base\Principal\Search;

use Sugarcrm\Sugarcrm\Dav\Base\Constants as DavConstants;

abstract class Base implements SearchInterface
{
    /**
     * Module name
     * @var string
     */
    protected $moduleName;

    /**
     * Principal prefix path
     * @var string
     */
    protected $prefixPath;

    /**
     * @param string $prefixPath
     */
    public function __construct($prefixPath)
    {
        if (strrpos($prefixPath, '/') !== strlen($prefixPath) - 1) {
            $prefixPath .= '/';
        }
        $this->prefixPath = $prefixPath;
    }

    /**
     * @inheritdoc
     */
    public function getPrincipalsByPrefix()
    {
        $focus = $this->getBean();
        $principals = array();

        if ($focus->load_relationship('email_addresses_primary')) {

            $usersQuery = $this->getSugarQuery();
            $usersQuery->from($focus);

            $focus->email_addresses_primary->buildJoinSugarQuery($usersQuery, array('joinType' => 'LEFT'));
            $usersQuery->select()->addField('email_addresses.email_address', array('alias' => 'email1'));

            $beans = $focus->fetchFromQuery($usersQuery);

            foreach ($beans as $bean) {
                $principals[] = $this->getPrincipalArray($bean);
            }
        }

        return $principals;
    }

    /**
     * @inheritdoc
     */
    public function getPrincipalByIdentify($identify)
    {
        if (!$identify) {
            return array();
        }
        $focus = $this->getBean();
        $bean = $focus->retrieve($identify);

        if (!$bean) {
            return array();
        }

        return $this->getPrincipalArray($bean);
    }

    /**
     * @inheritdoc
     */
    public function searchPrincipals(array $searchProperties, $test = 'allof')
    {
        $focus = $this->getBean();
        $query = $this->getSugarQuery();
        $searchFields = $this->getNameFormatFields($focus);

        $query->from($focus);

        $mainOrQuery = $query->where()->queryOr();
        $andQuery = $mainOrQuery->queryAnd();

        $conditionExists = false;
        foreach ($searchProperties as $property => $value) {
            switch ($property) {
                case '{DAV:}displayname':
                    $explodeName = explode(' ', $value);

                    foreach ($explodeName as $part) {
                        if ($part) {
                            $conditionExists = true;
                            $orQuery = $andQuery->queryOr();
                            foreach ($searchFields as $filed) {
                                $orQuery->contains($filed, $part);
                            }
                        }
                    }
                    break;
                case '{http://sabredav.org/ns}email-address':
                    if ($focus->load_relationship('email_addresses_primary')) {
                        $conditionExists = true;
                        $focus->email_addresses_primary->buildJoinSugarQuery(
                            $query,
                            array('joinType' => ($test == 'allof' ? 'INNER' : 'LEFT'))
                        );

                        $query->select(array('email_addresses.email_address'));

                        if ($test == 'allof') {
                            $query->where()->contains('email_addresses.email_address', $value);
                        } else {
                            $mainOrQuery->contains('email_addresses.email_address', $value);
                        }
                    } else {
                        return array();
                    }
                    break;
            }
        }
        if (!$conditionExists) {
            return array();
        }
        $principals = array();
        $beans = $focus->fetchFromQuery($query);

        foreach ($beans as $bean) {
            $principals[] = $this->formatPrincipalString($bean);
        }

        return $principals;
    }

    /**
     * Get SugarBean by module
     * @return null|\SugarBean
     */
    protected function getBean()
    {
        if ($this->moduleName) {
            return \BeanFactory::getBean($this->moduleName);
        }

        return null;
    }

    /**
     * Get SugarQuery
     * @return \SugarQuery
     */
    protected function getSugarQuery()
    {
        return new \SugarQuery();
    }

    /**
     * Retrieve fields array for name formating
     * @param \SugarBean $bean
     * @return array
     */
    protected function getNameFormatFields(\SugarBean $bean)
    {
        $localization = new \Localization();

        return $localization->getNameFormatFields($bean);
    }

    /**
     * Convert SugarBean to DAV principal array
     * @param \SugarBean $bean
     * @return array
     */
    protected function getPrincipalArray(\SugarBean $bean)
    {
        return array(
            'id' => $bean->id,
            'uri' => $this->formatPrincipalString($bean),
            '{DAV:}displayname' => $bean->full_name,
            '{http://sabredav.org/ns}email-address' => $bean->email1,
            '{' . DavConstants::NS_SUGAR . '}x-sugar-module' => $bean->module_name,
        );
    }

    /**
     * Return principal string depending on bean
     * @param \SugarBean $bean
     * @return string
     */
    protected function formatPrincipalString(\SugarBean $bean)
    {
        return $this->prefixPath . $bean->id;
    }
}
