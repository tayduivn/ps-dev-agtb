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

namespace Sugarcrm\Sugarcrm\SugarConnect\Client;

use GuzzleHttp;
use GuzzleHttp\Client as GuzzleClient;
use Sugarcrm\IdentityProvider\Srn;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config as IdmConfig;

final class HTTP extends WithConfiguration
{
    /**
     * Sends events to the Sugar Connect webhook as JSON over HTTP.
     *
     * @param array $events The events to send to the webhook.
     *
     * @return void
     */
    public function send(array $events) : void
    {
        // The clients are the last line of defense. If we somehow get past the
        // front door when disabled, then kill it before we send the events.
        if (!$this->config->isEnabled()) {
            return;
        }

        //TODO: The client shouldn't send its own tenant name. Use an oauth2
        // token.
        $sugarConfig = Container::getInstance()->get(\SugarConfig::class);
        $authConfig = new Authentication\Config($sugarConfig);
        $isIDMModeEnabled = $authConfig->isIDMModeEnabled();
        $tenant = $isIDMModeEnabled ?
            (new IdmConfig($sugarConfig))->getIDMModeConfig()['tid']
            : '';

        if (empty($tenant)) {
            throw new \Exception('sugar identity is required');
        }

        $region = Srn\Converter::fromString($tenant)->getRegion();
        $url = $this->config->getWebhookURL($region);

        //TODO: For now, add the tenant to each event. This won't be necessary
        // once the oauth2 token is used.
        $events = array_map(
            function ($v) use ($tenant) {
                return array_merge($v, ['tenant_name' => $tenant]);
            },
            $events
        );

        $log = \LoggerManager::getLogger();
        $log->debug("sugar connect: client: post: {$url}");
        $log->debug("sugar connect: client: post: " . json_encode($events));

        $code = $this->call($url, $events);

        // Retry once.
        if ($code >= 400) {
            $code = $this->call($url, $events);
        }

        if ($code >= 400) {
            throw new \Exception("failed with HTTP {$code}");
        }
    }

    /**
     * Sends the HTTP request to the Sugar Connect webhook.
     *
     * @param string $url  Send the request to this URL.
     * @param string $data The JSON to send.
     *
     * @return int The HTTP response code.
     */
    private function call(string $url, array $data) : int
    {
        $client = new GuzzleClient();
        $response = $client->post(
            $url,
            [
                GuzzleHttp\RequestOptions::JSON => $data,
            ]
        );

        return $response->getStatusCode();
    }
}
