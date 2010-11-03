<?php

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 4
 * Sets the Discount Name to the Discount Code so triggers and search work.
 */

class setNameFromDiscountCode {

    function set(&$bean, $event, $arguments) {
        if($event != "before_save") return false;

        $bean->name = $bean->discount_code;

        return true;
    }
}

