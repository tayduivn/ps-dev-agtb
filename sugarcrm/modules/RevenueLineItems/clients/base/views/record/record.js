/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    extendsFrom: 'RecordView',
    currencyFields: [],

    initialize: function(options) {
        //reinitialize array on each init
        this.currencyFields = [];
        this._setupCommitStageField(options.meta.panels);
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
     * extend save options
     * @param {Object} options save options.
     * @return {Object} modified success param.
     */
    getCustomSaveOptions: function(options) {
        // make copy of original function we are extending
        var origSuccess = options.success;
        // return extended success function with added alert
        return {
            success: _.bind(function() {
                if (_.isFunction(origSuccess)) {
                    origSuccess();
                }
                if (!_.isEmpty(this.model.get('quote_id'))) {
                     app.alert.show('save_rli_quote_notice', {
                        level: 'info',
                        messages: app.lang.get(
                            'SAVE_RLI_QUOTE_NOTICE',
                            'RevenueLineItems'
                        ),
                        autoClose: true
                     });
                }
            }, this)
        };
    },

    initButtons: function() {
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initButtons'});

        // if the model has a quote_id and it's not empty, disable the convert_to_quote_button
        if (this.model.has('quote_id') && !_.isEmpty(this.model.get('quote_id'))
            && !_.isUndefined(this.buttons['convert_to_quote_button'])) {
            this.buttons['convert_to_quote_button'].setDisabled(true);
        }
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
     * @param string oldCurrencyId
     * @param string newCurrencyId
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
            'success': _.bind(function(resp, status, xhr) {
                app.alert.dismiss('info_quote');
                window.location.hash = "#bwc/index.php?module=Quotes&action=EditView&record=" + resp.id;
            }, this),
            'error': _.bind(function(resp, status, xhr) {
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
     * @param array panels
     * @protected
     */
    _setupCommitStageField: function(panels) {
        _.each(panels, function(panel) {
            if (!app.metadata.getModule("Forecasts", "config").is_setup) {
                // use _.every so we can break out after we found the commit_stage field
                _.every(panel.fields, function(field, index) {
                    if (field.name == 'commit_stage') {
                        panel.fields[index] = {
                            'name': 'spacer',
                            'span': 6,
                            'readonly': true
                        };
                        return false;
                    }
                    return true;
                }, this);
            } else {
                _.each(panel.fields, function(field) {
                    if (field.name == "commit_stage") {
                        field.options = app.metadata.getModule("Forecasts", "config").buckets_dom;
                    }
                });
            }
        });
    }
})
