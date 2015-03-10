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

use Sugarcrm\Sugarcrm\Logger\LoggerTransition as BaseLogger;
use Psr\Log\LogLevel;
use Elastica\Request;
use Elastica\Response;
use Elastica\Connection;
use Elastica\JSON;

/**
 *
 * Logger specially for Elastic search.
 *
 */
class Logger extends BaseLogger
{
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
                "Elasticsearch response failure: code %s [%s] %s",
                $response->getStatus(),
                $request->getMethod(),
                $info['url']
            );
            $this->log(LogLevel::CRITICAL, $msg);
        } else {

            // Dump full request in debug mode
            if ($this->logger->wouldLog(LogLevel::DEBUG)) {
                $msg = sprintf(
                    "Elasicsearch response debug: [%s] %s %s",
                    $request->getMethod(),
                    $info['url'],
                    $this->encodeData($request->getData())
                );
                $this->log(LogLevel::DEBUG, $msg);
            }
        }
    }

    /**
     * Check if the exception is from a request of index deletion
     * @param \Exception $e
     * @return bool
     */
    public function isDeleteMissingIndexRequest(\Exception $e)
    {
        if ($e instanceof \Elastica\Exception\ResponseException) {
            $method = $e->getRequest()->getMethod();
            $expMsg = $e->getMessage();

            // Method expected to be "DELETE" and contains IndexMissingException
            // example: "IndexMissingException[[0e787f44c65e77fc6ac2c4fac1a01c65_shared] missing]"
            if ($method === Request::DELETE && strpos($expMsg, "IndexMissingException") !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle request logging on failure.
     * @param \Exception $e
     */
    public function onRequestFailure(\Exception $e)
    {
        //If the exception is from index deletion, no critical message is logged.
        if ($this->isDeleteMissingIndexRequest($e)) {
            $msg = "Elasticsearch request failure: Attempting to drop a non-existing index";
            $this->log(LogLevel::DEBUG, $msg);
            return;
        }

        $this->log(LogLevel::CRITICAL, "Elasticsearch request failure: " . $e->getMessage());
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
