<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Elasticsearch;

use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Elastica\Request;
use Elastica\Response;
use Elastica\Connection;
use Elastica\JSON;

/**
 *
 * PSR-0 adapter for SugarLogger until Monolog is integrated.
 *
 */
class Logger extends AbstractLogger
{
    /**
     * @var \LoggerManager
     */
    protected $logger;

    /**
     * @var array Mapping from PSR0 to Sugar log levels
     */
    protected $psrSugarMap = array();

    /**
     * @param \SugarLogger $logger
     */
    public function __construct(\LoggerManager $logger)
    {
        $this->logger = $logger;
        $this->psrSugarMap = array(
            LogLevel::EMERGENCY => 'fatal',
            LogLevel::ALERT => 'fatal',
            LogLevel::CRITICAL => 'fatal',
            LogLevel::ERROR => 'error',
            LogLevel::WARNING => 'warn',
            LogLevel::NOTICE => 'info',
            LogLevel::INFO => 'info',
            LogLevel::DEBUG => 'debug',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        $callBack = array($this->logger, $this->getSugarLevel($level));

        // LoggerManager doesn't support context so lets skip it for now
        return call_user_func($callBack, $message);
    }

    /**
     * Handle request logging on success.
     * @param \Elastica\Request $request
     * @param \Elastica\Response $response
     */
    public function onRequestSuccess(Request $request, Response $response)
    {
        // This is needed in either case
        $info = $response->getTransferInfo();

        // Sometimes no exceptions are thrown so make sure we are ok.
        if (!$response->isOk()) {
            $msg = sprintf(
                "ELASTIC FAILURE code %s [%s] %s",
                $response->getStatus(),
                $request->getMethod(),
                $info['url']
            );
            $this->log(LogLevel::CRITICAL, $msg);
        } else {

            // Dump full request in debug mode
            if ($this->logger->wouldLog(LogLevel::DEBUG)) {
                $msg = sprintf(
                    "ELASTIC [%s] %s %s",
                    $request->getMethod(),
                    $info['url'],
                    $this->encodeData($request->getData())
                );
                $this->log(LogLevel::DEBUG, $msg);
            }
        }
    }

    /**
     * Handle request logging on failure.
     * @param \Exception $e
     */
    public function onRequestFailure(\Exception $e)
    {
        $msg = sprintf(
            "ELASTIC FAILURE ... need more details here"
        );
        $this->log(LogLevel::CRITICAL, $msg);
    }

    /**
     * PSR-0 to sugar mapper
     * @param string $level
     * @return multitype:
     */
    protected function getSugarLevel($level)
    {
        return $this->psrSugarMap[$level];
    }

    /**
     * Helper method mimicing how \Elastica\Http formats its data.
     * Unfortunatily the raw value being send to the backend is not readily
     * available for log consumption.
     *
     * @param array|string $data
     * @return string
     */
    protected function encodeData($data)
    {
        if (is_array($data)) {
            $data = str_replace('\/', '/', JSON::stringify($data, 'JSON_ELASTICSEARCH'));
        }
        return $data;
    }
}
