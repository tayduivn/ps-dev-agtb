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

namespace Sugarcrm\Sugarcrm\League\OAuth2\Client\Provider\HttpBasicAuth;

use League\OAuth2\Client\Provider\GenericProvider as BasicGenericProvider;

class GenericProvider extends BasicGenericProvider
{
    /**
     * @inheritdoc
     */
    protected function getAccessTokenOptions(array $params)
    {
        $encodedCredentials = base64_encode(sprintf('%s:%s', $params['client_id'], $params['client_secret']));
        unset($params['client_id'], $params['client_secret']);

        $options = parent::getAccessTokenOptions($params);
        $options['headers']['Authorization'] = 'Basic ' . $encodedCredentials;

        return $options;
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredOptions()
    {
        return array_merge(parent::getRequiredOptions(), ['clientId', 'clientSecret']);
    }
}
