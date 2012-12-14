/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    transactionValue: '',
    /**
     * setup transactional amount if flag is present and transaction currency is not base
     *
     * @return {Object} this
     * @private
     */
    _render: function() {
        if(this.def.convertToBase && this.def.showTransactionalAmount && this.model.get('currency_id') !== '-99') {
            this.transactionValue = app.currency.formatAmountLocale(
              this.model.get(this.name),
              this.model.get('currency_id')
            );
        }
        app.view.Field.prototype._render.call(this);
        return this;
    },
    /**
     * unformat the field
     *
     * @param {String} value the displayed string
     * @return {String} value
     */
    unformat: function(value) {
        return app.currency.unformatAmountLocale(value);
    },
    /**
     * format the field, convert to base if necessary
     *
     * @param {String} value the displayed string
     * @return {String} value
     */
    format: function(value) {
        var base_rate = this.model.get('base_rate');
        var currencyId = this.model.get('currency_id');
        // do we convert to base currency?
        if(this.def.convertToBase) {
            value = app.currency.convertWithRate(value, base_rate);
            currencyId = '-99';
        }
        return app.currency.formatAmountLocale(value, currencyId);
    }
})