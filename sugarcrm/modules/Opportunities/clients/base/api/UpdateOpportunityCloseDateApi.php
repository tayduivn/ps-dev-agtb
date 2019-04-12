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
class UpdateOpportunityCloseDateApi extends SugarApi
{
    public function registerApiRest()
    {
        return array(
            'putOpportunityClosedDate' => array(
                'reqType' => 'PUT',
                'path' => array('Opportunities', 'updateOpportunityCloseDate'),
                'pathVars' => array(''),
                'min_version' => 11.6,
                'method' => 'putCloseDate',
                'shortHelp' => 'Custom end point for updating the close date of an opportunity',
                'longHelp' => 'modules/Opportunities/clients/base/api/help/update_opportunity_closed_data_put_help.html',
            ),
        );
    }

    /**
     * Updates the closed date for all RevenueLineItems that are associated
     * with an Opportunity
     *
     * @param ServiceBase $api The REST API object.
     * @param array $args REST API arguments.
     * @throws SugarApiExceptionMissingParameter
     */
    public function putCloseDate($api, $args)
    {
        $this->requireArgs($args, array('id', 'date_closed',));

        $opportunityBean = BeanFactory::retrieveBean('Opportunities', $args['id']);
        if ($opportunityBean && $opportunityBean->load_relationship('revenuelineitems')) {
            $rlis = $opportunityBean->revenuelineitems->getBeans();
            foreach ($rlis as $rli) {
                $rli->date_closed = $args['date_closed'];
                $rli->date_closed_timestamp = intval($args['date_closed_timestamp']);
                $rli->save();
            }
        }

        return $this->formatBean($api, $args, $opportunityBean);
    }
}
