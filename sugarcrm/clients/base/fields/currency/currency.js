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
    /**
     * list of events to listen for
     * @type {Object}
     */
    'events': {
        'click': 'updateCss'
    },
    /**
     * @type {String}
     * field value non-formatted or converted
     */
    transactionValue: '',
    /**
     * @type {Object}
     * reference to the currency dropdown field object
     */
    _currencyField: null,
    /**
     * @type {Boolean}
     * tracks whether the currency dropdown field is disabled or not
     */
    _currencyFieldDisabled: false,
    /**
     * @type {Boolean}
     * whether or not the currency dropdown is hidden from view
     */
    hideCurrencyDropdown: false,
    /**
     * @type {String}
     * last known record currency id
     */
    _lastCurrencyId: null,

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);
        var currencyField = this.def.currency_field || 'currency_id';
        if (this.model.isNew() && (!this.model.isCopy())) {
            // new records are set the user's preferred currency
            this.model.set(currencyField, app.user.get('preferences').currency_id);
            // set the base rate for the user's preferred currency
            this.model.set(
                this.def.base_rate_field || 'base_rate',
                app.metadata.getCurrency(app.user.get('preferences').currency_id).conversion_rate
            );
        }
        // hide currency dropdown on list views
        this.hideCurrencyDropdown = this.view.action === 'list';
        // track the last currency id to convert the value on change
        this._lastCurrencyId = this.model.get(currencyField);
    },

    /**
     * {@inheritdoc}
     *
     * Setup transactional amount if flag is present and transaction currency
     * is not base.
     * On edit view render the currency enum field associated with this field on
     * the correct placeholder
     *
     * @return {Object} this
     * @private
     */
    _render: function() {
        if (this._currencyField) {
            this._currencyField.dispose();
            this._currencyField = null;
        }
        app.view.Field.prototype._render.call(this);
        if (this.hideCurrencyDropdown === false && (this.action === 'edit' || this.action === 'disabled')) {
            this.getCurrencyField().setElement(this.$('span[sfuuid="' + this.currencySfId + '"]'));
            this.$el.find('div.select2-container').css('min-width', '8px');
            this.getCurrencyField().render();
        }
        return this;
    },

    /**
     * When currency changes, we need to make appropriate silent changes to the base rate.
     */
    bindDataChange: function() {

        // we do not call the parent which re-renders,
        // but instead update the value on the field directly
        this.model.on('change:' + this.name, this._valueChangeHandler, this);

        if (this.def.is_base_currency) {
            // do not add change handler to _usdollar fields
            return;
        }

        var currencyField = this.def.currency_field || 'currency_id';
        var baseRateField = this.def.base_rate_field || 'base_rate';
        this.model.on('change:' + currencyField, function(model, currencyId, options) {
            //When model is reset, it should not be called
            if (!currencyId || !this._lastCurrencyId) {
                this._lastCurrencyId = currencyId;
                return;
            }
            // update the base rate in the model
            this.model.set(baseRateField, app.metadata.getCurrency(currencyId).conversion_rate);
            // convert the value to new currency on the model
            if (model.has(this.name)) {
                // if user has removed currency value and hit enter, saving an empty string to the model
                // make sure we make that value 0 so it doesn't NaN in the next model set
                var val = model.get(this.name);
                if(val === '') {
                    val = 0;
                }
                this.model.set(
                    this.name,
                    app.currency.convertAmount(
                        app.currency.unformatAmountLocale(val),
                        this._lastCurrencyId,
                        currencyId
                    ),
                    // we don't want to affect other bindings like sugar logic
                    // when updating a value upon a currency_id change,
                    // so set the model silently, then update the field value
                    // directly (see next func call)
                    { silent: true }
                );
                // now defer changes to the end of the thread to avoid conflicts
                // with other events (from SugarLogic, etc.)
                var self = this;
                _.defer(function() {
                    self.model.trigger('change:' + self.name, self.model, self.model.get(self.name));
                });
            }
            this._lastCurrencyId = currencyId;
        }, this);
    },

    /**
     * Handler for when the the value changes on the model, if the action is not edit, then
     * re-render the field, otherwise just update the value via jQuery
     *
     * @param {Object} model
     * @param {string} value
     * @private
     */
    _valueChangeHandler: function(model, value)
    {
        if (this.action != 'edit') {
            this.render();
        } else {
            this.setCurrencyValue(value);
        }
    },

    /**
     * set the currency value on the field directly
     *
     * @param {String} value
     */
    setCurrencyValue: function(value) {
        this.$('[name=' + this.name + ']').val(app.utils.formatNumberLocale(value));
    },

    /**
     * {@inheritdoc}
     *
     * Convert to base currency if flag is present.
     *
     * @param {Array/Object/String/Number/Boolean} value The value to format.
     * @return {String} the formatted value based on view name.
     */
    format: function(value) {
        if (_.isNull(value) || _.isUndefined(value) || _.isNaN(value)) {
            value = '';
        }

        if (this.tplName === 'edit' || (this.tplName == 'disabled' && this.action == 'disabled')) {
            this.currencySfId = this.getCurrencyField().sfId;

            return app.utils.formatNumberLocale(value);
        }

        var baseRate;
        var currencyId;

        if (this.def.is_base_currency) {
            // usdollar fields are always in base currency, so set the currency id
            currencyId = app.currency.getBaseCurrencyId();
        } else {
            // TODO review this forecasts requirement and make it work with css defined on metadata
            // force this to recalculate the transaction value if needed
            // and more importantly, clear out previous transaction value
            this.transactionValue = '';
            if (this.def.convertToBase &&
                this.def.showTransactionalAmount &&
                this.model.get(this.def.currency_field || 'currency_id') !== app.currency.getBaseCurrencyId()
            ) {
                this.transactionValue = app.currency.formatAmountLocale(
                    this.model.get(this.name) || 0,
                    this.model.get(this.def.currency_field || 'currency_id')
                );
            }
            baseRate = this.model.get(this.def.base_rate_field || 'base_rate');
            currencyId = this.model.get(this.def.currency_field || 'currency_id');
            if (this.def.convertToBase) {
                value = app.currency.convertWithRate(value, baseRate) || 0;
                currencyId = app.currency.getBaseCurrencyId();
            }
        }
        return app.currency.formatAmountLocale(value, currencyId);
    },

    /**
     * {@inheritdoc}
     *
     * @param {String} value The value to unformat.
     * @return {Number} Unformatted value.
     */
    unformat: function(value) {

        if (this.tplName === 'edit') {
            return app.utils.unformatNumberStringLocale(value);
        }

        return app.currency.unformatAmountLocale(value);
    },

    /**
     * update dropdown css to active state
     */
    updateCss: function() {
        $('div.select2-drop.select2-drop-active').width('auto');
    },

    /**
     * Get the currency field related to this currency amount.
     *
     * @return {View.Field} the currency field associated.
     */
    getCurrencyField: function() {

        if (!_.isNull(this._currencyField)) {
            return this._currencyField;
        }

        var currencyDef = this.model.fields[this.def.currency_field || 'currency_id'];
        currencyDef.type = 'enum';
        currencyDef.options = app.currency.getCurrenciesSelector(Handlebars.compile('{{symbol}} ({{iso4217}})'));
        currencyDef.enum_width = '100%';
        currencyDef.searchBarThreshold = this.def.searchBarThreshold || 7;

        this._currencyField = app.view.createField({
            def: currencyDef,
            view: this.view,
            viewName: this.action,
            model: this.model
        });
        this._currencyField.defaultOnUndefined = false;

        return this._currencyField;
    },

    /**
     * set the mode of the dropdown field
     * @param {String} the mode name.
     */
    setMode: function(name) {
        app.view.Field.prototype.setMode.call(this, name);
        this.getCurrencyField().setMode(name);
    },

    /**
     * {@inheritdoc}
     */
    dispose: function() {
        if (this._currencyField) {
            this._currencyField.dispose();
            this._currencyField = null;
        }
        app.view.Field.prototype.dispose.call(this);
    }
})
