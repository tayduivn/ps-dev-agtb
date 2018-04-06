<?php declare(strict_types=1);
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

namespace Sugarcrm\Sugarcrm\Cache\Middleware;

use Psr\Log\LoggerInterface;
use Rhumsaa\Uuid\Uuid;
use RuntimeException;
use Sugarcrm\Sugarcrm\Cache;
use Sugarcrm\Sugarcrm\Cache\Middleware\MultiTenant\KeyStorage;
use Sugarcrm\Sugarcrm\Security\Crypto\AES256GCM;

/**
 * Multi-tenant cache middleware
 */
final class MultiTenant implements Cache
{
    /**
     * Application instance key
     *
     * @var string
     */
    private $instanceKey;

    /**
     * Encryption key
     *
     * @var Uuid
     */
    private $key;

    /**
     * Encryption key storage
     *
     * @var KeyStorage
     */
    private $keyStorage;

    /**
     * Namespace for hashing cache keys
     *
     * @var Uuid
     */
    private $namespace;

    /**
     * Cryptographic algorithm implementation
     *
     * @var AES256GCM
     */
    private $crypto;

    /**
     * Undelying cache backend
     *
     * @var Cache
     */
    private $backend;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $instanceKey
     * @param KeyStorage $keyStorage
     * @param Cache $backend
     * @param LoggerInterface $logger
     */
    public function __construct(string $instanceKey, KeyStorage $keyStorage, Cache $backend, LoggerInterface $logger)
    {
        $this->instanceKey = $instanceKey;
        $this->keyStorage = $keyStorage;
        $this->backend = $backend;
        $this->logger = $logger;

        $this->key = $keyStorage->getKey() ?: $this->generateKey();
        $this->initializeKey();
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(string $key, ?bool &$success = null)
    {
        $value = $this->backend->fetch($this->hash($key), $success);

        if (!$success) {
            return $value;
        }

        try {
            return $this->decrypt($value);
        } catch (RuntimeException $e) {
            $success = false;

            $this->logger->warning(sprintf('Failed to decrypt key "%s": %s', $key, $e->getMessage()));

            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $key, $value, ?int $ttl = null) : void
    {
        $this->backend->store($this->hash($key), $this->encrypt($value), $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key) : void
    {
        $this->backend->delete($this->hash($key));
    }

    /**
     * {@inheritDoc}
     */
    public function clear() : void
    {
        $this->key = $this->generateKey();
        $this->initializeKey();
    }

    /**
     * Initializes the updated encryption key
     */
    private function initializeKey() : void
    {
        $this->namespace = Uuid::uuid5($this->instanceKey, $this->key);
        $this->crypto = new AES256GCM($this->key->toString());
    }

    /**
     * Generates a new key and stores it in the storage
     *
     * @return Uuid
     */
    private function generateKey() : Uuid
    {
        $key = Uuid::uuid4();
        $this->keyStorage->updateKey($key);

        return $key;
    }

    /**
     * Hashes the given cache key
     *
     * @param string $key
     * @return string
     */
    private function hash(string $key) : string
    {
        return Uuid::uuid5($this->namespace, $key)->toString();
    }

    /**
     * Encrypts the value to be cached
     *
     * @param mixed $value
     * @return string
     */
    private function encrypt($value) : string
    {
        return $this->crypto->encrypt(serialize($value));
    }

    /**
     * Decrypts the cached value
     *
     * @param string $value
     * @return mixed
     */
    private function decrypt(string $value)
    {
        return unserialize($this->crypto->decrypt($value), [
            'allowed_classes' => false,
        ]);
    }
}
