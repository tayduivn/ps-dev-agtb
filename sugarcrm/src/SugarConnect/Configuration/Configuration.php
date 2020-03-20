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

namespace Sugarcrm\Sugarcrm\SugarConnect\Configuration;

use GuzzleHttp;
use GuzzleHttp\Client as HttpClient;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

final class Configuration implements Repository
{
    /**
     * The category under which all configurations are found.
     *
     * @var string
     */
    const CATEGORY = 'sugar_connect';

    /**
     * The name of the Sugar Connect webhook service. It is used to obtain the
     * webhook URL using service discovery.
     *
     * @var string
     */
    const WEBHOOK = 'connect-sugar-webhook:v1alpha';

    /**
     * Configuration API.
     *
     * @var \Administration
     */
    private static $admin;

    /**
     * Read and write Sugar Connect configurations.
     */
    public function __construct()
    {
        if (!static::$admin) {
            $admin = Container::getInstance()->get(\Administration::class);
            static::$admin = $admin->retrieveSettings(static::CATEGORY);
        }
    }

    /**
     * Tells whether or not Sugar Connect is enabled.
     *
     * @return bool
     */
    public function isEnabled() : bool
    {
        return $this->get('enabled', false);
    }

    /**
     * Enables Sugar Connect.
     *
     * @return void
     */
    public function enable() : void
    {
        $this->set('enabled', true);
    }

    /**
     * Disables Sugar Connect.
     *
     * @return void
     */
    public function disable() : void
    {
        $this->set('enabled', false);
    }

    /**
     * Updates service discovery parameters needed for Sugar Connect.
     *
     * @param string $url     The Discovery service URL.
     * @param string $version The version of the Discovery service to use.
     *
     * @return void
     */
    public function useServiceDiscovery(string $url, string $version) : void
    {
        $this->set('disco_url', $url);
        $this->set('disco_version', $version);
    }

    /**
     * Returns the URL to the Sugar Connect webhook for the given region.
     *
     * @param string $region The region in which webhook service should be
     *                       found.
     *
     * @return ?string
     */
    public function getWebhookURL(string $region) : ?string
    {
        $version = $this->get('disco_version', 'v1');
        $url = $this->get(
            'disco_url',
            'https://discovery-stage-backend.service.sugarcrm.com'
        );
        $url = rtrim($url, '/') . '/' . trim($version, '/') . '/services';

        $client = new HttpClient(['timeout' => 20.0]);
        $response = $client->get($url);
        $responseBody = GuzzleHttp\json_decode($response->getBody(), true);
        $services = $responseBody['services'] ?? [];

        foreach ($services as $service) {
            if ($service['name'] === static::WEBHOOK) {
                foreach ($service['endpoints'] as $endpoint) {
                    if ($endpoint['region'] === $region) {
                        return rtrim($endpoint['url'], '/');
                    }
                }
            }
        }

        return null;
    }

    /**
     * Returns the value for the given key.
     *
     * @param string $key     The key for the value to retrieve.
     * @param mixed  $default An optional default value in the event that there
     *                        is no value under the key.
     *
     * @return mixed
     */
    private function get(string $key, $default = null)
    {
        // Prefix the key with the category to find the value in settings.
        $key = static::CATEGORY . '_' . $key;
        $settings = static::$admin->settings;

        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    /**
     * Saves the value for the given key.
     *
     * @param string $key   The key for the value to retrieve.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    private function set(string $key, $value) : void
    {
        static::$admin->saveSetting(static::CATEGORY, $key, $value);
        static::$admin = static::$admin->retrieveSettings(static::CATEGORY);
    }
}
