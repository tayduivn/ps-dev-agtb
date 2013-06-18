<?php
//FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once('data/SugarACLStrategy.php');

class SugarACLForecastWorksheets extends SugarACLStrategy
{
    public function checkAccess($module, $view, $context)
    {
        if ($module != 'ForecastWorksheets') {
            return false;
        }

        if ($view == 'team_security') {
            // Let the other modules decide
            return true;
        }

        // Let's make it a little easier on ourselves and fix up the actions nice and quickly
        $view = SugarACLStrategy::fixUpActionName($view);
        $bean = $this->getForecastByBean();
        $current_user = $this->getCurrentUser($context);

        if ($current_user->isAdminForModule($bean->module_name)) {
            return true;
        }

        if (empty($view) || empty($current_user->id)) {
            return true;
        }

        if ($view == 'field') {
            // Opp Bean, Amount Field = Likely Case on worksheet
            if ($bean instanceof Opportunity && $context['field'] == 'likely_case') {
                $context['field'] = 'amount';
            }
            // make sure the user has access to the field
            return $bean->ACLFieldAccess($context['field'], $context['action'], $context + array('bean' => $bean));
        }

        return true;
    }

    /**
     * Return the bean for what we are forecasting by
     *
     * @return Product|Opportunity|SugarBean
     */
    protected function getForecastByBean()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');

        $bean = ucfirst($settings['forecast_by']);

        return BeanFactory::getBean($bean);
    }
}
