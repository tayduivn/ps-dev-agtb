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

namespace Sugarcrm\Sugarcrm\Entitlements;

// This section of code is a portion of the code referred
// to as Critical Control Software under the End User
// License Agreement.  Neither the Company nor the Users
// may modify any portion of the Critical Control Software.
/**
 * Class SubscriptionManager
 *
 * Sugar subscription manager:
 * It can talk to license server to download subscription data and save to DB.config table
 * It will not talk to license server unless license is modified
 *
 */
class SubscriptionManager
{
    protected $subscriptionRestApiEndPoint = 'rest/subscription/';

    /**
     * internal subscription data
     * @var subscription
     */
    protected $subscription;

    /**
     * subscription or license id
     * @string
     */
    protected $licenseKey;

    /**
     * system subscription keys
     * @var array
     */
    protected $systemSubscriptionKeys = [];

    /**
     * instance
     * @var subscriptionmanager
     */
    protected static $instance;

    /**
     * no public ctor
     * subscriptionmanager constructor.
     */
    private function __construct()
    {
    }

    /**
     * singleton implementation
     * @return subscriptionmanager
     */
    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * get instance of http client for license server
     * @return \sugarlicensing
     */
    protected function getSugarLicensingClient()
    {
        return new \SugarLicensing();
    }

    /**
     * get subscription, either go to db or license server to get subscription content
     *
     * @param string $licenseKey
     * @return null|Subscription
     */
    protected function getSubscription(string $licenseKey)
    {
        if (empty($licenseKey)) {
            return null;
        }

        if (!empty($this->subscription) && $this->licenseKey === $licenseKey) {
            return $this->subscription;
        }

        $this->subscription = null;
        $content = $this->getSubscriptionContent($licenseKey, true);
        $this->subscription = new Subscription($content);
        $this->licenseKey = $licenseKey;

        return $this->subscription;
    }

    /**
     * get content of subscription, if $useDb is false, it will ignore database and retrieve directly from license server
     *
     * @param $licenseKey license key
     * @param bool $useDb if true, it will ignore data in config and retrieve directly from license server
     * @return string
     */
    protected function getSubscriptionContent(string $licenseKey, bool $useDb) : string
    {
        $admin = \BeanFactory::newBean('Administration');
        if ($useDb) {
            $admin->retrieveSettings('license');
            if (isset($admin->settings['license_subscription'])) {
                $data = $admin->settings['license_subscription'];
                if (is_array($data)) {
                    return json_encode($admin->settings['license_subscription']);
                } else {
                    return $data;
                }
            }
        }

        // go to license server to retrieve data
        $endpoint = $this->subscriptionRestApiEndPoint . $licenseKey;
        $subscriptionClient = $this->getSugarLicensingClient();
        $response = $subscriptionClient->request($endpoint, [], false);

        // try to parse and valid the content
        $this->subscription = new Subscription($response);
        $subscriptionClient = null;

        if (empty($this->subscription)) {
            // something is wrong
            return '';
        }

        // save to config table
        $admin->saveSetting('license', 'subscription', $response);

        // refresh metadata cache if not in installation time
        $this->refreshMetadataCache();
        return $response;
    }

    protected function refreshMetadataCache()
    {
        \MetaDataManager::refreshCache();
        return;
    }

    /**
     * get license key
     * @return string/null
     */
    protected function getLicenseKey()
    {
        if (!empty($this->licenseKey)) {
            return $this->licenseKey;
        }

        $admin = \Administration::getSettings('license');
        if (isset($admin->settings['license_key'])) {
            return $admin->settings['license_key'];
        }
        return null;
    }

    /**
     * set a new license key, it will trigger to access license server to download new subscription content and save to db
     * @param null|string $licenseKey
     */
    public function downloadSubscriptionContent(?string $licenseKey)
    {
        if (empty($licenseKey)) {
            $this->licenseKey = null;
            return;
        }

        // reset internal data
        $this->subscription = null;
        $this->systemSubscriptionKeys = [];
        $this->licenseKey = $licenseKey;

        // need to go to license server to get subscription data
        $this->getSubscriptionContent($licenseKey, false);
    }

    /**
     * get list of subscriptions
     * @return array
     */
    public function getSystemSubscriptions()
    {
        $licenseKey = $this->getLicenseKey();
        $subscription = $this->getSubscription($licenseKey);
        return !empty($subscription) ? $subscription->getSubscriptions() : null;
    }

    /**
     * get subscription keys
     * @return array
     */
    public function getSystemSubscriptionKeys()
    {
        if (!empty($this->systemSubscriptionKeys)) {
            return $this->systemSubscriptionKeys;
        }

        $licenseKey = $this->getLicenseKey();
        $subscription = $this->getSubscription($licenseKey);
        if (empty($subscription)) {
            return [];
        }

        $this->systemSubscriptionKeys = $subscription->getSubscriptionKeys();
        return $this->systemSubscriptionKeys;
    }

    /**
     * get user's subscription, it joins system subscription with user's license type
     * @param \User $user
     * @return array
     */
    public function getUserSubscriptions(\User $user)
    {
        // get system subscriptions
        $systemSubscriptionKeys = $this->getSystemSubscriptionKeys();

        if (empty($systemSubscriptionKeys)) {
            return [];
        }

        $userLicenseTypes = $user->getLicenseType();
        // one prod subscription, license type = current or empty will be using current product
        if (count($systemSubscriptionKeys) === 1) {
            if (empty($userLicenseTypes)) {
                // never assigned before
                return array_keys($systemSubscriptionKeys);
            }
            // check if user has current license type
            foreach ($userLicenseTypes as $type) {
                if (Subscription::SUGAR_BASIC_KEY === $type) {
                    return array_keys($systemSubscriptionKeys);
                }
            }
        }

        // pick up a license type
        if (empty($userLicenseTypes)) {
            // never assigned before, pick up one based on the order in getAllSupportedProducts()
            return $this->getUserDefaultLicenseTypes($systemSubscriptionKeys);
        }

        // loop through the license keys
        $userSubscriptions = [];
        foreach ($userLicenseTypes as $type) {
            if (isset($systemSubscriptionKeys[$type])) {
                $userSubscriptions[] = $type;
            }
        }

        // assign admin user to default license type, otherwise, an ENT user will get blank license types
        if (empty($userSubscriptions) && $user->is_admin) {
            if ($user->is_admin) {
                return $this->getUserDefaultLicenseTypes($systemSubscriptionKeys);
            }
        }
        return $userSubscriptions;
    }

    /**
     * all supported types, keep the order
     * @return array
     */
    protected function getAllSupportedProducts()
    {
        return [
            Subscription::SUGAR_BASIC_KEY,
            Subscription::SUGAR_SERVE_KEY,
            Subscription::SUGAR_SELL_KEY,
        ];
    }

    /**
     * get default license type
     * @return array
     */
    protected function getUserDefaultLicenseTypes(array $systemSubscriptionKeys)
    {
        $allProducts = $this->getAllSupportedProducts();
        foreach ($allProducts as $type) {
            if (isset($systemSubscriptionKeys[$type])) {
                $userSubscriptions[] = $type;
                return $userSubscriptions;
            }
        }

        throw new \Exception("new license type found!");
    }
}
//END REQUIRED CODE DO NOT MODIFY
