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
 * Class Subscription
 *
 * sugar subscription object, it parses raw subscription data and provides APIs to access those data
 */
class Subscription
{
    const SUGAR_SELL_KEY = 'SUGAR_SELL';
    const SUGAR_SERVE_KEY = 'SUGAR_SERVE';
    const SUGAR_BASIC_KEY = 'CURRENT';

    /**
     * mapping well-known subscription_ids to keys
     */
    const SUBSCRIPTION_ID_MAPPING = [
        '181aee1c-7b3e-11e9-b962-02c10f456dba' => self::SUGAR_SELL_KEY,
        'aa8834fa-6ac0-11e9-b588-02c10f456dba' => self::SUGAR_SERVE_KEY,
    ];

    /**
     * mapping product codes to internal keys
     */
    const PRODUCT_CODE_MAPPING = [
        'ENT' => self::SUGAR_BASIC_KEY,
        'PRO' => self::SUGAR_BASIC_KEY,
        'ULT' => self::SUGAR_BASIC_KEY,
        'SELL' => self::SUGAR_SELL_KEY,
        'SERVE' => self::SUGAR_SERVE_KEY,
    ];

    /**
     * internal data
     * @var array
     */
    protected $data = [];

    /**
     * parsed subscription data
     * @var array
     */
    protected $subscriptions = [];

    /**
     * @var array of Addons
     */
    protected $addons = [];

    /**
     * private Subscription constructor.
     * @param string $jsonData
     */
    public function __construct(string $jsonData)
    {
        $this->parse($jsonData);
    }

    /**
     * parse the raw subscription data
     * @param string $jsonData
     */
    protected function parse(string $jsonData)
    {
        $decodedData = json_decode($jsonData, true);
        if ($decodedData === null) {
            throw new \Exception('Invalid subscription json data');
        }
        
        if (empty($decodedData['subscription'])) {
            return;
        }

        foreach ($decodedData['subscription'] as $key => $value) {
            if ($key === 'addons' && count($decodedData['subscription'][$key]) > 0) {
                foreach ($decodedData['subscription'][$key] as $addonId => $addonData) {
                    $this->addons[$addonId] = new Addon($addonId, $addonData);
                }
            } else {
                $this->data[$key] = $value;
            }
        }
        $this->data['addons'] = $this->addons;
    }

    /**
     * access method
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }

    /**
     * to get subscriptions
     * only gets the subscriptions with quantity > 0.
     *
     * return in array format
     * [
     *      'quantity' => ...,
     *      'expiration_date' => ...,
     * ];
     * @return array
     */
    public function getSubscriptions()
    {
        if ($this->subscriptions) {
            return $this->subscriptions;
        }

        $subscriptions = [];
        if (empty($this->data)) {
            return [];
        }

        if (!empty($this->error)) {
            $GLOBALS['log']->fatal("there is an error in license server response: " . $this->error);
            return [];
        }
        // get top level
        $prodtemplateId = $this->producttemplate_id_c;
        $quantity = (int)$this->quantity_c;
        $expirationDate = $this->expiration_date;
        if (!empty($prodtemplateId) && isset($quantity) && $quantity > 0 && $expirationDate - time() > 0) {
            if (isset(self::SUBSCRIPTION_ID_MAPPING[$prodtemplateId])) {
                // don't need to go any further
                $subscriptions[self::SUBSCRIPTION_ID_MAPPING[$prodtemplateId]] = [
                    'quantity' => $quantity,
                    'expiration_date' => $expirationDate,
                ];
            } else {
                // assume it is one of ENT, PRO, ULT, etc
                // get current product
                $subscriptions[self::SUGAR_BASIC_KEY] = [
                    'quantity' => $quantity,
                    'expiration_date' => $expirationDate,
                ];
            }
        }

        // check addons, only interested in 'SELL', 'SERVE' and Legacy product codes such as 'ENT', 'PRO', etc.
        // ignore any other addons for now
        foreach ($this->addons as $addonId => $addon) {
            $quantity = (int)$addon->quantity;
            $expirationDate = $addon->expiration_date;
            if (isset($quantity) && $quantity > 0 && isset($expirationDate) && $expirationDate - time() > 0) {
                if (isset(self::SUBSCRIPTION_ID_MAPPING[$addonId])) {
                    // using predefined subscription Ids to find out subscription types
                    $subscriptions[self::SUBSCRIPTION_ID_MAPPING[$addonId]] = [
                        'quantity' => $quantity,
                        'expiration_date' => $expirationDate,
                    ];
                } else {
                    // using product code to find out subscription types
                    $productCode = $addon->product_code_c;
                    if (!empty($productCode) && !empty(self::PRODUCT_CODE_MAPPING[strtoupper($productCode)])) {
                        $key = self::PRODUCT_CODE_MAPPING[strtoupper($productCode)];
                        if (isset($subscriptions[$key])) {
                            if (isset($GLOBALS['log'])) {
                                $GLOBALS['log']->error('Duplicated product code found: ' . $productCode);
                            }
                        }
                        $subscriptions[$key] = [
                            'quantity' => $quantity,
                            'expiration_date' => $expirationDate,
                        ];
                    }
                }
            }
        }

        $this->subscriptions = $subscriptions;

        return $subscriptions;
    }

    /**
     * get keys for subscriptions
     *
     * need to take care of ENT, PRO, etc
     */
    public function getSubscriptionKeys()
    {
        $subscriptions = $this->getSubscriptions();
        if (empty($subscriptions)) {
            return [];
        }

        $keys = [];
        foreach ($subscriptions as $key => $value) {
            if (!in_array($key, $this->getAddonProducts())) {
                $keys[self::SUGAR_BASIC_KEY] = true;
            } else {
                $keys[$key] = true;
            }
        }
        return $keys;
    }

    /**
     * get current addon products,
     * @return array
     */
    public function getAddonProducts()
    {
        return [
            Subscription::SUGAR_SELL_KEY,
            Subscription::SUGAR_SERVE_KEY,
        ];
    }
}
//END REQUIRED CODE DO NOT MODIFY
