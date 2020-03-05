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

namespace Sugarcrm\Sugarcrm\ProductDefinition\Config\Source;

use GuzzleHttp\Client as HttpClient;
use Symfony\Component\HttpFoundation\Response;

class HttpSource implements SourceInterface
{

    /**
     * Http client client timeout
     */
    const HTTP_CLIENT_TIMEOUT = 2;

    /**
     * Default server uri
     */
    const DEFAULT_BASE_URI = 'https://updates.sugarcrm.com/spds';

    /**
     * Default fallback version
     */
    const DEFAULT_FALLBACK_VERSION = '10.0.0';

    /**
     * Http client
     * @var HttpClient
     */
    protected $client;

    /**
     * Fallback version
     * @var string
     */
    protected $fallbackVersion;

    /**
     * constructor.
     * @throws \InvalidArgumentException
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (empty($options['base_uri'])) {
            throw new \InvalidArgumentException('base URL should not be empty');
        }

        $this->setHttpClient(new HttpClient([
            'base_uri' => $options['base_uri'],
            'http_errors' => false,
            'timeout' => static::HTTP_CLIENT_TIMEOUT,
        ]));

        if (!empty($options['fallback_version'])) {
            $this->fallbackVersion = $options['fallback_version'];
        }
    }

    /**
     * set http client
     * @param HttpClient $client
     * @return $this
     */
    public function setHttpClient(HttpClient $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * make request and return product definition array
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \RuntimeException
     */
    public function getDefinition():? string
    {
        $raw = $this->makeRequest($this->getSugarVersion());
        if (is_null($raw) && !empty($this->fallbackVersion)) {
            $this->getLogger()->warn(sprintf(
                'Can\'t download product definition for version %s. Trying download it for fall back version %s.',
                $this->getSugarVersion(),
                $this->fallbackVersion
            ));
            $raw = $this->makeRequest($this->fallbackVersion);
        }

        return $raw;
    }

    /**
     * Make HTTP GET request and return response
     * @param string $version
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function makeRequest(string $version):? string
    {
        try {
            $response = $this->client->request('GET', '/' . $version);
        } catch (\Exception $e) {
            $this->getLogger()->error('Can\'t download product definition for version: ' . $version);
            return null;
        }

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $this->getLogger()->error(sprintf(
                'wrong product definition service response code %s for version %s',
                Response::HTTP_OK,
                $version
            ));
            return null;
        }

        return (string) $response->getBody();
    }

    /**
     * return sugar version
     * @return string
     */
    protected function getSugarVersion(): string
    {
        global $sugar_version;
        return $sugar_version;
    }

    /**
     * @return \LoggerManager
     */
    protected function getLogger(): \LoggerManager
    {
        global $log;
        return $log;
    }
}
