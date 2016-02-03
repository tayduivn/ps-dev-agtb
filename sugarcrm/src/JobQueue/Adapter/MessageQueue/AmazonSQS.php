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

namespace Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue;

use Psr\Log\LoggerInterface;
use Aws\Sqs\SqsClient;
use Aws\Common\Credentials\Credentials;
use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException;
use Sugarcrm\Sugarcrm\JobQueue\Exception\RuntimeException;

/**
 * Class AmazonSQS
 * @package JobQueue
 */
class AmazonSQS implements AdapterInterface
{
    /**
     * @var \Aws\Sqs\SqsClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $queueUrl;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var int The duration (in seconds) that the received messages are hidden after ReceiveMessage request.
     */
    protected $visibilityTimeout = 1800;

    /**
     * Initialize connection, client and queueUrl.
     *
     * @param array $config
     * @param LoggerInterface $logger
     * @throws RuntimeException
     */
    public function __construct($config, LoggerInterface $logger)
    {
        $credentials = new Credentials($config['key'], $config['secret']);
        $this->client = SqsClient::factory(array(
            'credentials' => $credentials,
            'region' => $config['region'],
        ));

        if (!$this->client) {
            throw new LogicException('Failed to connect to AmazonSQS server.');
        }
        $this->logger = $logger;

        $uniqueKey = (!empty($config['unique_key'])) ? '_' . $config['unique_key'] : '';
        $queueName = $config['queueName'] . $uniqueKey;

        $model = $this->client->getQueueUrl(array('QueueName' => $queueName));
        $this->queueUrl = $model->get('QueueUrl');
    }

    /**
     * Add a job to AmazonSQS queue.
     * {@inheritdoc}
     * @throws RuntimeException
     */
    public function addJob($route, $data)
    {
        $this->logger->info("[AmazonSQS]: send a message '{$route}'.");
        $this->logger->debug("[AmazonSQS]: data '{$data}'.");
        $done = $this->client->sendMessage(
            array(
                'QueueUrl' => $this->queueUrl,
                'MessageBody' => $data,
            )
        );

        if (!$done) {
            throw new RuntimeException('Failed to send message at AmazonSQS server.');
        }
    }

    /**
     * Binding for SQS is not supported.
     * {@inheritdoc}
     */
    public function bind($route)
    {
    }

    /**
     * Binding for SQS is not supported.
     * {@inheritdoc}
     */
    public function unbind($route)
    {
    }

    /**
     * Get a job based on AmazonSQS specific message format.
     * Handler notification from Amazon SNS.
     * {@inheritdoc}
     */
    public function getJob($message)
    {
        $notificationBody = json_decode($message['Body']);

        if (json_last_error() === JSON_ERROR_NONE &&
            is_object($notificationBody) &&
            $notificationBody->Type === 'Notification'
        ) {
            return $notificationBody->Message;
        }
        return $message['Body'];
    }

    /**
     * Get an AmazonSQS message.
     * {@inheritdoc}
     */
    public function getMessage()
    {
        $responseModel = $this->client->receiveMessage(
            array(
                'QueueUrl' => $this->queueUrl,
                'VisibilityTimeout' => $this->visibilityTimeout,
                'MaxNumberOfMessages' => 1,
                'WaitTimeSeconds' => 3,
            )
        );

        return $responseModel->getPath('Messages/0');
    }

    /**
     * Delete job from AmazonSQS queue depending on resolution.
     * {@inheritdoc}
     */
    public function resolve($message)
    {
        $this->logger->debug("[AmazonSQS]: resolve a message '{$message['ReceiptHandle']}'.");
        $this->client->deleteMessage(
            array(
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $message['ReceiptHandle'],
            )
        );
    }
}
