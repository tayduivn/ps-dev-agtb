<?php

/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/SugarFields/Fields/Float/SugarFieldFloat.php');

class SugarFieldCurrency extends SugarFieldFloat 
{
    function getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col) {
        global $current_user;
        $tabindex = 1;
        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex, false);

        $baseCurrency = SugarCurrency::getBaseCurrency();
        $amount = $parentFieldArray[strtoupper($vardef['name'])];
        $currencyId = !empty($parentFieldArray['CURRENCY_ID']) ?
            $parentFieldArray['CURRENCY_ID']
            : $baseCurrency->id;
        $currencySymbol = !empty($parentFieldArray['CURRENCY_SYMBOL']) ?
            $parentFieldArray['CURRENCY_SYMBOL']
            : SugarCurrency::getCurrencyByID($currencyId)->symbol;

        if (empty($currencyId) || !empty($vardef['is_base_currency'])) {
            // this is a base USDOLLAR field
            if ($current_user->getPreference('currency_show_preferred')) {
                // display base amount in user preferred currency
                $userCurrency = SugarCurrency::getUserLocaleCurrency();
                if ($currencyId !== $userCurrency->id) {
                    // currencies differ, convert the amount
                    $currencyId = $userCurrency->id;
                    $currencySymbol = $userCurrency->symbol;
                    $amount = SugarCurrency::convertWithRate($amount, 1.0, $userCurrency->conversion_rate);
                } else {
                    // transactional and preferred currency type are the same,
                    // convert value back to transactional amount
                    if (!empty($parentFieldArray['BASE_RATE']) && $parentFieldArray['BASE_RATE'] <> 1) {
                        $amount = SugarCurrency::convertWithRate($amount, 1.0, $parentFieldArray['BASE_RATE']);
                    } else {
                        // no base rate found, fall back to base
                        $currencyId = $baseCurrency->id;
                        $currencySymbol = $baseCurrency->symbol;
                    }
                }
            } else {
                // display in base currency
                $currencyId = $baseCurrency->id;
                $currencySymbol = $baseCurrency->symbol;
            }
        }

        $this->ss->assign('currency_id', $currencyId);
        $this->ss->assign('currency_symbol', $currencySymbol);
        $this->ss->assign('amount', $amount);

        return $this->fetch($this->findTemplate('ListView'));
    }
    
    /**
     * @see SugarFieldBase::importSanitize()
     */
    public function importSanitize($value, $vardef, $focus, ImportFieldSanitize $settings)
    {
        require_once('include/SugarCurrency/SugarCurrency.php');
        /** @var Currency $base_currency */
        $base_currency = SugarCurrency::getBaseCurrency();
        $currency_id = $settings->currency_id;

        // Remove the grouping separator
        $value = str_replace($settings->num_grp_sep, '', $value);

        // change the decimal separator to a . if it's not already one
        if ($settings->dec_sep != '.') {
            $value = str_replace($settings->dec_sep, '.', $value);
        }

        if (isset($vardef['convertToBase']) && $vardef['convertToBase']) {
            // convert amount from base
            $value = str_replace($base_currency->symbol, '', $value);
            try {
                $value = SugarCurrency::convertAmountFromBase($value, $settings->currency_id);
            } catch (SugarMath_Exception $sme) {
                $GLOBALS['log']->error('Currency Field Import Error: ' . $sme->getMessage());
                return false;
            }
        } elseif (isset($vardef['is_base_currency']) && $vardef['is_base_currency']) {
            $value = str_replace($base_currency->symbol, '', $value);
            $currency_id = $base_currency->id;
        } else {
            $value = str_replace($settings->currency_symbol, '', $value);
        }

        // last check, if for some reason we get here and the value is not numeric, we should just fail out.
        if (!is_numeric($value)) {
            return false;
        }

        return SugarCurrency::formatAmount($value, $currency_id, 6, '.', '', false);
    }

    /**
     * Handles export field sanitizing for field type
     *
     * @param $value string value to be sanitized
     * @param $vardef array representing the vardef definition
     * @param $focus SugarBean object
     * @param $row Array of a row of data to be exported
     *
     * @return string sanitized value
     */
    public function exportSanitize($value, $vardef, $focus, $row = array())
    {
        // If $value is null, default to zero to prevent conversion errors.
        $value = is_null($value) ? 0 : $value;

        require_once('include/SugarCurrency/SugarCurrency.php');
        if (isset($vardef['convertToBase']) && $vardef['convertToBase']) {
            // convert amount to base
            $baseRate = isset($row['base_rate']) ? $row['base_rate'] : $focus->base_rate;
            $value = SugarCurrency::convertWithRate($value, $baseRate);
            $currency_id = '-99';
        } elseif (isset($vardef['is_base_currency']) && $vardef['is_base_currency']) {
            $currency_id = '-99';
        } else {
            //If the row has a currency_id set, use that instead of the $focus->currency_id value
            $currency_id = isset($row['currency_id']) ? $row['currency_id'] : $focus->currency_id;
        }
        return SugarCurrency::formatAmountUserLocale($value, $currency_id);
    }

    /**
	 * format the currency field based on system locale values for currency
     * Note that this may be different from the precision specified in the vardefs.
	 * @param string $rawfield value of the field
     * @param string $somewhere vardef for the field being processed
	 * @return number formatted according to currency settings
	 */
    public function formatField($rawField, $vardef){
        // for currency fields, use the user or system precision, not the precision in the vardef
        //this is achived by passing in $precision as null
        $precision = null;

        if ( $rawField === '' || $rawField === NULL ) {
            return '';
        }
        return format_number($rawField,$precision,$precision);
    }

    /**
     * BWC modules always unformat server-side
     *
     * @param string $formattedField
     * @param array $vardef
     * @return null|string
     */
    public function unformatField($formattedField, $vardef)
    {
        if ($formattedField === '' || $formattedField === null) {
            return null;
        }
        return (string)unformat_number($formattedField);
    }

    /**
     * Formats a field for the Sugar API, we need what the base does here since the SugarFieldFloat does the wrong
     * thing and really currencies are not floats
     *
     * @param array     $data
     * @param SugarBean $bean
     * @param array     $args
     * @param string    $fieldName
     * @param array     $properties
     */
    public function apiFormatField(&$data, $bean, $args, $fieldName, $properties)
    {
        if (isset($bean->$fieldName)) {
            $data[$fieldName] = $bean->$fieldName;
        } else {
            $data[$fieldName] = '';
        }
    }

    /**
     * Since SugarFieldFloat override this method, we need to do the same here and always return true as we want
     * to use the default processing
     *
     * @param Number $value                         The value for which we are trying to filter
     * @param String $fieldName                     What field we are trying to modify
     * @param SugarBean $bean                       The associated SugarBean
     * @param SugarQuery $q                         The full query object
     * @param SugarQuery_Builder_Where $where       The where object for the filter
     * @param String $op                            The filter operation we are trying to do
     * @return bool
     * @throws SugarApiExceptionInvalidParameter
     */
    public function fixForFilter(&$value, $fieldName, SugarBean $bean, SugarQuery $q, SugarQuery_Builder_Where $where, $op) {
        return true;
    }
}
