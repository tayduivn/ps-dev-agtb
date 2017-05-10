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

namespace Sugarcrm\Sugarcrm\IdentityProvider;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sugarcrm\Sugarcrm\Session\SessionStorageInterface;

/**
 * Mango proxy for Symfony Session.
 * Class SessionProxy
 */
class SessionProxy implements SessionInterface
{
    /**
     * @var SessionStorageInterface
     */
    protected $storage = null;

    /**
     * __construct
     * @param SessionStorageInterface $storage
     */
    public function __construct(SessionStorageInterface $storage)
    {
        $this->storage = $storage;
        if (!$this->isStarted()) {
            $this->start();
        }
    }

    /**
     * @inheritDoc
     */
    public function start()
    {
        $this->storage->start();
        return $this->isStarted();
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->storage->getId();
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        $this->storage->setId($id);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        throw new \LogicException('Cannot change the name of an active session');
    }

    /**
     * Unsupported method.
     * @throws \LogicException
     * @param null $lifetime
     */
    public function invalidate($lifetime = null)
    {
        throw new \LogicException('Not support invalidation');
    }

    /**
     * Unsupported method.
     * @throws \LogicException
     * @param bool|false $destroy
     * @param null $lifetime
     */
    public function migrate($destroy = false, $lifetime = null)
    {
        throw new \LogicException('Not support migration');
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        // saving should be in external code
    }

    /**
     * @inheritDoc
     */
    public function all()
    {
        return $this->storage;
    }

    /**
     * @inheritDoc
     */
    public function replace(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @inheritDoc
     */
    public function set($name, $value)
    {
        $this->storage[$name] = $value;
    }

    /**
     * @inheritDoc
     */
    public function remove($name)
    {
        $return = $this->get($name);
        unset($this->storage[$name]);
        return $return;
    }

    /**
     * @inheritDoc
     */
    public function get($name, $default = null)
    {
        if (!$this->has($name)) {
            return $default;
        }
        return $this->storage[$name];
    }

    /**
     * @inheritDoc
     */
    public function has($name)
    {
        return isset($this->storage[$name]);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        // nothing to do
    }

    /**
     * @inheritDoc
     */
    public function isStarted()
    {
        return $this->storage->sessionHasId();
    }

    /**
     * Unsupported method.
     * @throws \LogicException
     * @param SessionBagInterface $bag
     */
    public function registerBag(SessionBagInterface $bag)
    {
        throw new \LogicException('Not support registering bag');
    }

    /**
     * Unsupported method.
     * @throws \LogicException
     * @param string $name
     */
    public function getBag($name)
    {
        throw new \LogicException('Not support retrieving bag');
    }

    /**
     * Unsupported method.
     * @throws \LogicException
     */
    public function getMetadataBag()
    {
        throw new \LogicException('Not support retrieving metadata bag');
    }
}
