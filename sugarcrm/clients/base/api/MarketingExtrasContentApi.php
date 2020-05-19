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

use Sugarcrm\Sugarcrm\Marketing\MarketingExtrasContent;

class MarketingExtrasContentApi extends SugarApi
{
    /**
     * The service for MarketingExtrasContent
     * @var MarketingExtrasContent
     */
    private $marketingExtrasContentService;

    /**
     * Instantiates (if empty) and returns the service for MarketingExtrasContent
     *
     * @return MarketingExtrasContent
     */
    public function getMarketingExtrasContentService()
    {
        if (!isset($this->marketingExtrasContentService)) {
            $this->marketingExtrasContentService = new MarketingExtrasContent();
        }

        return $this->marketingExtrasContentService;
    }

    /**
     * Set up the endpoint for this API
     *
     * @return array
     */
    public function registerApiRest()
    {
        return [
            'getMarketingContentUrl' => [
                'reqType' => 'GET',
                'path' => ['login', 'marketingContentUrl'],
                'method' => 'getMarketingContentUrl',
                'shortHelp' => 'Gets the SugarCRM marketing content URL',
                'longHelp' => 'include/api/help/marketing_extras_content_get_help.html',
                'noLoginRequired' => true,
                'ignoreSystemStatusError' => true,
                'minVersion' => '11.9',
            ],
        ];
    }

    /**
     * Gets and returns the marketing content URL
     *
     * @param ServiceBase $api
     * @param array $args
     * @return string The marketing content URL
     */
    public function getMarketingContentUrl(ServiceBase $api, array $args): string
    {
        return $this->getMarketingExtrasContentService()->getMarketingExtrasContentUrl();
    }
}
