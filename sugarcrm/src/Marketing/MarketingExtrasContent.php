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

namespace Sugarcrm\Sugarcrm\Marketing;

class MarketingExtrasContent
{
    /**
     * The helper for MarketingExtrasHelper
     * @var MarketingExtrasHelper
     */
    private $marketingExtrasHelper = null;

    /**
     * Headers that block iframe rendering
     */
    private $blacklistedHeaders = [
        'x-frame-options',
        'frame-ancestors',
    ];

    /**
     * Creates the MarketingExtrasHelper (if it doesn't exist) and return it
     *
     * @return MarketingExtrasHelper
     */
    protected function getMarketingExtrasHelper(): MarketingExtrasHelper
    {
        if ($this->marketingExtrasHelper === null) {
            $this->marketingExtrasHelper = new MarketingExtrasHelper();
        }

        return $this->marketingExtrasHelper;
    }

    /**
     * Returns the marketing content URL. A URL for static content is returned if marketing URL is not
     * reachable
     *
     * @return string The marketing content URL
     */
    public function getMarketingExtrasContentUrl(): string
    {
        $marketingContentConfig = get_sugar_config_defaults()['login_page']['marketing_extras_content'];
        $baseUrl = $marketingContentConfig['url'];
        $staticUrl = $marketingContentConfig['static_url'];

        if (!empty($baseUrl)) {
            $queryParams = $this->getQueryParams();
            $url = $this->getFullUrl($baseUrl, $queryParams);

            if ($this->isContentDisplayable($url)) {
                return $url;
            }
        }

        return $staticUrl;
    }

    /**
     * Returns the query parameters for the request
     *
     * @return array The query parameters
     */
    protected function getQueryParams(): array
    {
        $helper = $this->getMarketingExtrasHelper();
        $sugarDetails = $helper->getSugarDetails();

        return [
            'domain' => $sugarDetails['domain'],
            'language' => $helper->chooseLanguage(null),
            'flavor' => $sugarDetails['flavor'],
            'version' => $sugarDetails['version'],
            'license'   => $sugarDetails['license'],
        ];
    }

    /**
     * Builds and returns the full URL (base url + query parameters)
     *
     * @param string $baseUrl
     * @param array $queryParams
     * @return string The full URL
     */
    protected function getFullUrl(string $baseUrl, array $queryParams): string
    {
        return $baseUrl . '?' . http_build_query($queryParams);
    }

    /**
     * Determines if the URL is reachable and if it can be displayed in an iframe
     *
     * @param string $url
     * @return bool
     */
    protected function isContentDisplayable(string $url): bool
    {
        // should not take more than 300ms to get headers
        $timeoutInMs = 150;

        $curlHandle = curl_init($url);
        if ($curlHandle === false) {
            \LoggerManager::getLogger()->warn('MarketingExtrasContent:: Could not open connection to URL ' . $url);
            return false;
        }

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_HEADER, true);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT_MS, $timeoutInMs);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT_MS, ($timeoutInMs * 2));

        $response = curl_exec($curlHandle);
        $headers = substr($response, 0, curl_getinfo($curlHandle, CURLINFO_HEADER_SIZE));
        $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curlHandle);

        curl_close($curlHandle);

        if ($response === false || $httpCode !== 200) {
            \LoggerManager::getLogger()->warn('MarketingExtrasContent:: Could not get response from URL ' . $url .
                ' with HTTP code: ' . $httpCode . ' and curl error: ' . $curlError);
            return false;
        }

        $headers = strtolower($headers);

        foreach ($this->blacklistedHeaders as $blacklistedHeader) {
            if (strpos($headers, $blacklistedHeader) !== false) {
                \LoggerManager::getLogger()->warn('MarketingExtrasContent:: Cannot load iframe due to ' .
                    $blacklistedHeader . ' header from URL ' . $url);
                return false;
            }
        }

        return true;
    }
}
