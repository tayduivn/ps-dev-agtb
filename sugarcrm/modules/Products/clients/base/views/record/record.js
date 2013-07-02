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
    extendsFrom: 'RecordView',
    currencyFields: [],

    initialize: function(options) {
        //reinitialize array on each init
        this.currencyFields = [];
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initialize', args: [options]});

        //pull the fields in the panels that are editable currency fields
        _.each(options.meta.panels, function(panel) {
                _.each(panel.fields, function(field) {
                    //if the field is currency and not the known calculated field, add to the array
                    if (field.type == 'currency') {
                        this.currencyFields.push(field.name);
                    }
                }, this);
            }, this
        );
    },
    /**
     * Bind to model to make it so that it will re-render once it has loaded.
     */
    bindDataChange: function() {
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'bindDataChange'});
        this.model.on('change:base_rate', function() {
            _.debounce(this.convertCurrencyFields(this.model.previous("currency_id"), this.model.get("currency_id")), 500, true);
        }, this);
    },

    delegateButtonEvents: function() {
        this.context.on('button:convert_to_quote:click', this.convertToQuote, this);
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'delegateButtonEvents'});
    },

    /**
     * convert all of the currency fields to the new currency
     * @param oldCurrencyId
     * @param newCurrencyId
     */
    convertCurrencyFields: function(oldCurrencyId, newCurrencyId) {
        //this ends up getting called on init without an old currency id, so just return in that case
        if (_.isUndefined(oldCurrencyId)) {
            return;
        }

        //run through the editable currency fields and convert the amounts to the new currency
        _.each(this.currencyFields, function(currencyField) {
            //convert the currency and set the model silenty, then force the change to trigger.  Otherwise, a 0 value won't
            //trigger the change event, because 0 will convert to 0, but we need the change event for the currency symbol to update
            if (currencyField != 'total_amount') {
                this.model.set(currencyField, app.currency.convertAmount(this.model.get(currencyField), oldCurrencyId, newCurrencyId), {silent: true});
            }
            this.model.trigger("change:" + currencyField);
        }, this);
    }
})
