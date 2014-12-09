<?php

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

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

/**
 * User metadata context
 */
class MetaDataContextUser implements MetaDataContextInterface
{
    /**
     * @var MetaDataContextRoleSet
     */
    protected $context;

    /**
     * Constructor
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        if (!empty($GLOBALS['sugar_config']['roleBasedViews'])) {
            $roleSet = $this->getRoleSet($user);
            if ($roleSet) {
                $this->context = new MetaDataContextRoleSet($roleSet);
                return;
            }
        }

        $this->context = new MetaDataContextDefault();
    }

    /** {@inheritDoc} */
    public function getHash()
    {
        return $this->context->getHash();
    }

    /** {@inheritDoc} */
    public function isValid(array $file)
    {
        return $this->context->isValid($file);
    }

    /** {@inheritDoc} */
    public function compare(array $a, array $b)
    {
        return $this->context->compare($a, $b);
    }

    /**
     * Gets the role set of the given user
     *
     * @param User $user
     * @return ACLRoleSet|null
     */
    protected function getRoleSet(User $user)
    {
        $user->load_relationship('acl_role_sets');
        $roleSets = $user->acl_role_sets->getBeans();
        return array_shift($roleSets);
    }
}
