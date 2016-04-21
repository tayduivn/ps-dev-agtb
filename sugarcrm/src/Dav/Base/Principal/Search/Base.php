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

namespace Sugarcrm\Sugarcrm\Dav\Base\Principal\Search;

use Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Format;

abstract class Base implements SearchInterface
{
    /**
     * Module name
     * @var string
     */
    protected $moduleName;

    /**
     * Strategy for format results
     * @var Format\StrategyInterface
     */
    protected $formatStrategy;

    /**
     * @param string $prefixPath
     * @param Format\StrategyInterface|null $formatStrategy
     */
    public function __construct($prefixPath = '', Format\StrategyInterface $formatStrategy = null)
    {
        $this->formatStrategy = $formatStrategy ? $formatStrategy : new Format\PrincipalStrategy($prefixPath);
    }

    /**
     * Get priority of module for search
     * @return int
     */
    public static function getOrder()
    {
        return 10000;
    }

    /**
     * Set format strategy for current search
     * @param Format\StrategyInterface $formatStrategy
     */
    public function setFormat(Format\StrategyInterface $formatStrategy)
    {
        $this->formatStrategy = $formatStrategy;
    }

    /**
     * @inheritdoc
     */
    public function getPrincipalsByPrefix()
    {
        $focus = $this->getBean();
        $principals = array();

        if ($focus->load_relationship('email_addresses')) {
            $usersQuery = $this->getSugarQuery();
            $usersQuery->from($focus);

            $focus->email_addresses->buildJoinSugarQuery($usersQuery, array('joinType' => 'LEFT'));
            $usersQuery->select()->addField('email_addresses.email_address', array('alias' => 'email1'));

            $beans = $focus->fetchFromQuery($usersQuery);

            foreach ($beans as $bean) {
                $principals[] = $this->formatStrategy->formatBody($bean);
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

        return $this->formatStrategy->formatBody($bean);
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
                                $orQuery->starts($filed, $part);
                            }
                        }
                    }
                    break;
                case '{http://sabredav.org/ns}email-address':
                    if ($focus->load_relationship('email_addresses')) {
                        $conditionExists = true;
                        $focus->email_addresses->buildJoinSugarQuery(
                            $query,
                            array('joinType' => ($test == 'allof' ? 'INNER' : 'LEFT'))
                        );

                        $query->select(array('email_addresses.email_address'));

                        $emailCaps = strtoupper(trim($value));
                        if ($test == 'allof') {
                            $query->where()->starts('email_addresses.email_address_caps', $emailCaps);
                        } else {
                            $mainOrQuery->starts('email_addresses.email_address_caps', $emailCaps);
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
            $principals[] = $this->formatStrategy->formatUri($bean);
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
}
