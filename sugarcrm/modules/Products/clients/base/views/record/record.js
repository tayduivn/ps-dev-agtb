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
    currencyFields: new Array(),

    initialize: function(options) {
        this._setupCommitStageField(options.meta.panels);
        //pull the fields in the panels that are editable currency fields
        _.each(
            options.meta.panels, function(panel) {
                _.each(panel.fields, function(field) {
                    //if the field is currency and not read only, then push it into our currency fields array
                    if(field.type == 'currency') {
                        this.currencyFields.push(field.name);
                    }
                }, this);
            }, this
        );
        app.view.views.RecordView.prototype.initialize.call(this, options);
    },

    initButtons: function() {
        app.view.views.RecordView.prototype.initButtons.call(this);

        // if the model has a quote_id and it's not empty, disable the convert_to_quote_button
        if(this.model.has('quote_id') && !_.isEmpty(this.model.get('quote_id'))
            && !_.isUndefined(this.buttons['convert_to_quote_button'])) {
            this.buttons['convert_to_quote_button'].setDisabled(true);
        }
    },

    /**
     * Bind to model to make it so that it will re-render once it has loaded.
     */
    bindDataChange : function() {
        app.view.views.RecordView.prototype.bindDataChange.call(this);
        this.model.on('change:base_rate', function() {
            _.debounce(this.convertCurrencyFields(this.model.previous("currency_id"), this.model.get("currency_id")),500, true);
        }, this);
    },

    delegateButtonEvents: function() {
        this.context.on('button:convert_to_quote:click', this.convertToQuote, this);

        app.view.views.RecordView.prototype.delegateButtonEvents.call(this);
    },

    /**
     * convert all of the currency fields to the new currency
     * @param oldCurrencyId
     * @param newCurrencyId
     */
    convertCurrencyFields: function(oldCurrencyId, newCurrencyId) {
        //this ends up getting called on init without an old currency id, so just return in that case
        if(_.isUndefined(oldCurrencyId)) {
            return;
        }

        //run through the editable currency fields and convert the amounts to the new currency
        _.each(this.currencyFields, function(currencyField) {
           this.model.set(currencyField, app.currency.convertAmount(this.model.get(currencyField), oldCurrencyId, newCurrencyId), {silent: true});
           this.model.trigger("change:"+currencyField);
        }, this);
    },

    convertToQuote: function(e) {
        var alert = app.alert.show('info_quote', {
                        level: 'info',
                        autoClose: false,
                        closeable: false,
                        title: app.lang.get("LBL_CONVERT_TO_QUOTE_INFO", this.module) + ":",
                        messages: [app.lang.get("LBL_CONVERT_TO_QUOTE_INFO_MESSAGE", this.module)]
                    });
        // remove the close since we don't want this to be closable
        alert.$el.find('a.close').remove();

        var url = app.api.buildURL(this.model.module, 'quote', { id: this.model.id });
        var callbacks = {
            'success' : _.bind(function(resp, status, xhr) {
                app.alert.dismiss('info_quote');
                window.location.hash="#bwc/index.php?module=Quotes&action=EditView&record=" + resp.id;
            }, this),
            'error' : _.bind(function(resp, status, xhr) {
                app.alert.dismiss('info_quote');
                app.alert.show('error_xhr', {
                        level: 'error',
                        autoClose: true,
                        title: app.lang.get("LBL_CONVERT_TO_QUOTE_ERROR", this.module) + ":",
                        messages: [app.lang.get("LBL_CONVERT_TO_QUOTE_ERROR_MESSAGE", this.module)]
                    });
            }, this)
        };
        app.api.call("create", url, null, callbacks);
    },

    /**
     * Set up the commit_stage field based on forecast settings - if forecasts is set up, adds the correct dropdown
     * elements, if forecasts is not set up, it removes the field.
     * @param panels
     * @private
     */
    _setupCommitStageField: function(panels) {
        _.each(panels, function(panel) {
            if(!app.metadata.getModule("Forecasts", "config").is_setup) {
                panel.fields = _.filter(panel.fields, function (field) {
                    // also remove the spacer so the look stays the same
                    return (field.name != "commit_stage" && field.name != "spacer");
                })
            } else {
                _.each(panel.fields, function(field) {
                    if (field.name == "commit_stage") {
                        field.options = app.metadata.getModule("Forecasts", "config").buckets_dom;
                    }
                })
            }
        });
    }
})
