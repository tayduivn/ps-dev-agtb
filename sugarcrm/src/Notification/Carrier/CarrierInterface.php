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

namespace Sugarcrm\Sugarcrm\Notification\Carrier;

use Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\AddressTypeInterface;

/**
 * Interface CarrierInterface.
 *
 * @package Sugarcrm\Sugarcrm\Notification\Carrier
 */
interface CarrierInterface
{
    /** allow only one carrier option selected */
    const DELIVERY_BEHAVIOR_SINGLE = 'single';

    /** allow more than one carrier options selected */
    const DELIVERY_BEHAVIOR_MULTIPLE = 'multiple';

    /** use when carrier options should not be displayed */
    const DELIVERY_DISPLAY_STYLE_NONE = 'none';

    /** use when carrier options should be displayed like selectbox with multiple selection */
    const DELIVERY_DISPLAY_STYLE_MULTISELECT = 'multiselect';

    /** use when carrier options should be displayed like selectbox without multiple selection */
    const DELIVERY_DISPLAY_STYLE_SINGLESELECT = 'select';

    /** use when carrier options should be displayed like radio buttons */
    const DELIVERY_DISPLAY_STYLE_RADIO = 'radio';

    /** use when carrier options should be displayed like checkboxes */
    const DELIVERY_DISPLAY_STYLE_CHECKBOX = 'checkbox';

    /**
     * @return TransportInterface
     */
    public function getTransport();

    /**
     * ToDo: decide 'label' or 'title'.
     * @return array(
     *      'label' => '', - full info, but short
     *      'url' => '', - url to event
     *      'subject' => '',- can be not full info, used for email, rss
     *      'text' => '', - full info can be long
     *      'html' => '', full info can be long
     *  );
     *
     */
    public function getMessageSignature();

    /**
     * @return AddressTypeInterface
     */
    public function getAddressType();

    /**
     * Return carriers options, like selectable type, view name, etc.
     *
     * @return array (
     *      follow option describe which input type should be used for carrier options displayed
     *      by default it is hidden field, otherwise some of constants.
     *     'deliveryOptionsDisplayStyle' => DELIVERY_DISPLAY_STYLE_NONE|DELIVERY_DISPLAY_STYLE_MULTISELECT|
     *                                      DELIVERY_DISPLAY_STYLE_SINGLESELECT|DELIVERY_DISPLAY_STYLE_RADIO|
     *                                      DELIVERY_DISPLAY_STYLE_CHECKBOX (default DELIVERY_DISPLAY_STYLE_NONE),
     *
     *      follow options describe has ot not user select more than one option for carrier delivery method
     *      by default user can select only one.
     *      'deliveryOptionsBehavior' => DELIVERY_BEHAVIOR_SINGLE|DELIVERY_BEHAVIOR_MULTIPLE,
     *                                      (default DELIVERY_BEHAVIOR_SINGLE)
     * );
     * If deliveryOptionsBehavior isn't present it means single.
     */
    public function getOptions();
}
