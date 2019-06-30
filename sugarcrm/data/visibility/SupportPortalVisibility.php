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

// FILE SUGARCRM flav=ent ONLY

use Sugarcrm\Sugarcrm\Visibility\Portal as PortalStrategy;
use Sugarcrm\Sugarcrm\Portal\Factory as PortalFactory;

/**
 * Portal visibility class replaces the team security restrictions for portal users
 * For non-portal users this class will not modify the query in any way.
 */
class SupportPortalVisibility extends SugarVisibility
{
    /**
     * Add Visibility to a SugarQuery Object
     *
     * @param SugarQuery $sugarQuery
     * @param array $options
     *
     * @return SugarQuery
     */
    public function addVisibilityQuery(SugarQuery $sugarQuery, $options = array())
    {
        if (!PortalFactory::getInstance('Session')->isActive()) {
            $GLOBALS['log']->error('Not a portal user, but running through the portal visibility class.');
            return $sugarQuery;
        }
        if ($this->bean->disable_row_level_security) {
            $GLOBALS['log']->debug(
                'No portal security applied to module (row-level security disabled): ' . $this->bean->module_dir
            );
            return $sugarQuery;
        }

        $strategy = $this->getVisibilityStrategy();
        if (empty($options['table_alias'])) {
            $options['table_alias'] = $this->getOption('table_alias');
            if (empty($options['table_alias'])) {
                $options['table_alias'] = DBManagerFactory::getInstance()->getValidDBName($this->bean->getTableName(), true, 'table');
            }
        }

        $strategy->addVisibilityQuery($sugarQuery, $options);
        return $sugarQuery;
    }

    /**
     * Get module-specific strategy
     *
     * @return Sugarcrm\Sugarcrm\Visibility\Portal\ModuleVisibility|null;
     */
    protected function getVisibilityStrategy()
    {
        $class = $this->getVisibilityStrategyClass($this->bean);
        $strategy = new $class(PortalFactory::getInstance('Session')->getVisibilityContext($this->bean));
        if (!($strategy instanceof PortalStrategy\Module)) {
            throw new \Exception('Invalid portal visibility strategy for ' . $this->bean->module_name);
        }
        return $strategy;
    }

    /**
     * Get strategy class name
     *
     * @param \SugarBean $bean Bean
     *
     * @throws \Exception
     * @return string
     */
    protected function getVisibilityStrategyClass(\SugarBean $bean)
    {
        if (isset($GLOBALS['dictionary'][$bean->getObjectName()]['portal_visibility']['class'])) {
            $file = 'data/visibility/portal/' . $GLOBALS['dictionary'][$bean->getObjectName()]['portal_visibility']['class']
                . '.php';
            $class = 'Sugarcrm\\Sugarcrm\\Visibility\\Portal\\'
                . $GLOBALS['dictionary'][$bean->getObjectName()]['portal_visibility']['class'];
        } else {
            $file = 'data/visibility/portal/Hidden.php';
            $class = 'Sugarcrm\\Sugarcrm\\Visibility\\Portal\\Hidden';
        }
        \SugarAutoLoader::requireWithCustom($file);
        $class = \SugarAutoLoader::customClass($class);
        if (!class_exists($class)) {
            throw new \Exception('Portal visibility strategy ' . $class . ' not found');
        }
        return $class;
    }
}
