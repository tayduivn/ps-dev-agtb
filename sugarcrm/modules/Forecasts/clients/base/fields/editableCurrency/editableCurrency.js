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

/**
 * Events Triggered
 *
 * field:editable:error
 *      on: context
 *      by: bindDataChange()
 *      when: if this field is open with an error, and it receives a field:editable:open event
 *            it will trigger this event to let other fields know not to open
 *
 * field:editable:open
 *      on: context
 *      by: onClick()
 *      when: user clicks on the field to open it
 *
 * forecasts:tabKeyPressed
 *      on: context
 *      by: onKeyDown()
 *      when: the tab key is pressed inside the field
 */
({
    extendsFrom: 'CurrencyField',
    symbol: '',
    inputSelector: 'span.edit input',
    errorMessage: '',
    isErrorState: false,
    _canEdit: true,

    events: {
        'mouseenter span.editable': 'togglePencil',
        'mouseleave span.editable': 'togglePencil',
        'click span.editable': 'onClick',
        'blur span.edit input': 'onBlur',
        'keyup span.edit input': 'onKeyUp',
        'keydown span.edit input': 'onKeyDown'
    },

    initialize: function(options) {
        app.view.invokeParent(this, {type: 'field', name: 'currency', method: 'initialize', args: [options]});
        this.checkIfCanEdit();
        this.symbol = app.currency.getCurrencySymbol(this.model.get('currency_id'));
    },

    /**
     * Begin Override
     *
     * This is because the base field is broken with our custom code in forecasts.
     *
     * todo-sfa: check that if these are moved when SFA-462 is done that it can work with the base field
     */
    _render: function() {
        // bypass the currencyField render and just go all the way up
        app.view.Field.prototype._render.call(this);
        return this;
    },

    format: function(value) {
        if(this.tplName === 'edit') {
            return app.utils.formatNumberLocale(value);
        }

        // TODO review this forecasts requirement and make it work with css defined on metadata
        if(this.def.convertToBase &&
            this.def.showTransactionalAmount &&
            this.model.get(this.def.currency_field || 'currency_id') !== app.currency.getBaseCurrencyId()
            ) {

            this.transactionValue = app.currency.formatAmountLocale(
                this.model.get(this.name),
                this.model.get(this.def.currency_field || 'currency_id')
            );
        }

        var baseRate = this.model.get(this.def.base_rate_field || 'base_rate');
        var currencyId = this.model.get(this.def.currency_field || 'currency_id');

        if(this.def.convertToBase) {
            value = app.currency.convertWithRate(value, baseRate);
            currencyId = app.currency.getBaseCurrencyId();
        }
        return app.currency.formatAmountLocale(value, currencyId);
    },
    /**
     * End Override
     */

    /**
     * Utility Method to check if we can edit again.
     */
    checkIfCanEdit: function() {
        var selectedUser = this.context.get('selectedUser');
        this._canEdit = _.isEqual(app.user.get('id'), selectedUser.id) && !_.contains(
            // join the two variable together from the config
            app.metadata.getModule('Forecasts', 'config').sales_stage_won.concat(app.metadata.getModule('Forecasts', 'config').sales_stage_lost), this.model.get('sales_stage'));
    },

    /**
     * Overwrite bindDomChange
     *
     * Since we need to do custom logic when a field changes, we have to overwrite this with out ever calling
     * the parent.
     *
     */
    bindDomChange: function() {
        // override parent, do nothing
    },

    /**
     * Only one CTE field can be open/active at a time.
     * When a CTE field is clicked, it sends a message to inform the others.
     * If another field is open with an error, it sends a message
     * and any other open fields will immediately close. This keeps
     * other fields from opening while an errored field is active.
     */
    bindDataChange: function() {
        this.context.on('field:editable:open', function() {
            // another CTE field has been opened
            if(this.isErrorState) {
                // I am open with an error, send the message
                this.context.trigger('field:editable:error', this.cid);
            }
        }, this);
        this.context.on('field:editable:error', function(cid) {
            if(!_.isEqual(cid, this.cid) && this.options.viewName == 'edit') {
                // some other field is open with an error, close mythis
                this.renderDetail();
            }
        }, this);
    },

    /**
     * handle click/blur/keypress events in one place
     *
     * @param {Object} evt
     * @return {Boolean}
     */
    handleEvent: function(evt) {
        if(!_.isObject(evt)
            || this.options.viewName != 'edit'
            || !this.isEditable()
            || !(this.model instanceof Backbone.Model)) {
            return false;
        }
        var el = this.$el.find(this.fieldTag);
        // test if value changed
        if(!this.compareValuesLocale(this.$el.find(this.inputSelector).val(), this.model.get(this.name))) {
            var value = this.parsePercentage(this.$el.find(this.inputSelector).val());
            if(this.isValid(value)) {
                this.model.set(this.name, this.unformat(value));
                this.$el.find("[rel=tooltip]").tooltip('destroy');
                this.renderDetail();
            } else {
                // render error
                this.isErrorState = true;
                var hb = Handlebars.compile("{{str_format key module args}}"),
                    args = [app.lang.get(this.def.label, 'Forecasts')];

                this.errorMessage = hb({'key': 'LBL_EDITABLE_INVALID', 'module': 'Forecasts', 'args': args});

                this.showErrors();
                this.$el.find(this.inputSelector).focus().select();
                // Focus doesn't always change when tabbing through inputs on IE9 (Bug54717)
                // This prevents change events from being fired appropriately on IE9
                if($.browser.msie && el.is("input")) {
                    el.on("input", function() {
                        // Set focus on input element receiving user input
                        el.focus();
                    });
                }
            }
        } else {
            this.renderDetail();
        }
        return true;
    },

    /**
     * renders the detail view
     */
    renderDetail: function() {
        this.isErrorState = false;
        this.options.viewName = 'detail';
        this.render();
    },

    /**
     * Toggles the pencil icon on and off depending on the mouse state
     *
     * @param evt
     */
    togglePencil: function(evt) {
        evt.preventDefault();
        if(!this.isEditable()) {
            return;
        }
        if(evt.type == 'mouseenter') {
            this.$el.find('.edit-icon').removeClass('hide');
            this.$el.find('.edit-icon').addClass('show');
        } else {
            this.$el.find('.edit-icon').removeClass('show');
            this.$el.find('.edit-icon').addClass('hide');
        }
    },

    /**
     * Switch the view to the Edit view if the field is editable and it's clicked on
     * @param evt
     */
    onClick: function(evt) {
        evt.stopPropagation();
        if(!this.isEditable()) {
            return;
        }
        this.options.viewName = 'edit';
        this.render();

        // set the edit input string to an unformatted number
        var formattedValue = app.utils.formatNumber(
            this.model.get(this.name),
            app.user.getPreference('decimal_precision'),
            app.user.getPreference('decimal_precision'),
            '',
            app.user.getPreference('decimal_separator')
        );
        this.$el.find(this.inputSelector).removeClass('local-error').val(formattedValue).select();
        // inform other fields that I am opening
        this.context.trigger('field:editable:open');

    },

    /**
     * Handle event when key is let up (enter, esc)
     *
     * @param evt
     */
    onKeyUp: function(evt) {
        evt.preventDefault();
        if(evt.which == 27) {
            // esc key, cancel edits
            this.cancelEdits(evt);
        } else if(evt.which == 13) {
            // enter key, handle event
            this.handleEvent(evt);
        }
    },

    /**
     * Handle event when key is pressed down (tab)
     *
     * @param evt
     */
    onKeyDown: function(evt) {
        if(evt.which == 9) {
            evt.preventDefault();
            // tab key pressed, trigger event from context
            this.context.trigger('forecasts:tabKeyPressed', evt.shiftKey, this);
        }
    },

    /**
     * reset value to model and view detail template
     *
     * evt {Object}
     */
    cancelEdits: function(evt) {
        this.$el.find(this.inputSelector).val(this.value);
        this.$el.find("[rel=tooltip]").tooltip('destroy');
        this.renderDetail();
    },

    /**
     * Handle when field is blurred
     *
     * @param evt
     */
    onBlur: function(evt) {
        evt.preventDefault();
        this.handleEvent(evt);
    },

    /**
     * compare two numeric values according to user locale
     *
     * @param val1
     * @param val2
     * @return {Boolean} true if equal
     */
    compareValuesLocale: function(val1, val2) {
        var ogVal = app.utils.formatNumber(
                app.utils.unformatNumberStringLocale(val1),
                app.user.getPreference('decimal_precision'),
                app.user.getPreference('decimal_precision'),
                '',
                app.user.getPreference('decimal_separator')
            ),
            ngVal = app.utils.formatNumber(
                val2,
                app.user.getPreference('decimal_precision'),
                app.user.getPreference('decimal_precision'),
                '',
                app.user.getPreference('decimal_separator')
            );
        return _.isEqual(ogVal, ngVal);
    },

    /**
     * field validator
     *
     * @param value
     * @return {Boolean}
     */
    isValid: function(value) {
        // trim off any whitespace
        value = value.toString().trim();

        var ds = app.utils.regexEscape(app.user.getPreference('decimal_separator')) || '.',
            gs = app.utils.regexEscape(app.user.getPreference('number_grouping_separator')) || ',',
        // matches a valid positive decimal number
            reg = new RegExp("^\\+?(\\d+|\\d{1,3}(" + gs + "\\d{3})*)?(" + ds + "\\d+)?\\%?$");

        // always make sure that we have a string here, since match only works on strings
        if(value.length == 0 || _.isNull(value.match(reg))) {
            return false;
        }

        // the value passed all validation, return true
        return true;
    },

    /**
     * Can we edit this?
     *
     * @return {boolean}
     */
    isEditable: function() {
        return this._canEdit;
    },

    /**
     * Check the value to see if it's a percentage, if it is, then figure out the change.
     *
     * @param value
     * @return {*}
     */
    parsePercentage: function(value) {
        var orig = this.model.get(this.name),
            parts = value.toString().match(/^([+-]?)(\d+(\.\d+)?)\%$/);
        if(parts) {
            // use original number to apply calculations
            value = app.math.mul(app.math.div(parts[2], 100), orig);
            if(parts[1] == '+') {
                value = app.math.add(orig, value);
            } else if(parts[1] == '-') {
                value = app.math.sub(orig, value);
            }
            value = app.math.round(value);
        }
        return value.toString();
    },

    /**
     * Method to show the error message
     */
    showErrors : function() {
        this.$el.find('.error-tooltip').addClass('add-on local').removeClass('hide').css('display','inline-block');
        this.$el.find('input').addClass('local-error');
        // we want to show the tooltip message, but hide the add-on (exclamation)
        this.$el.find("[rel=tooltip]").tooltip('destroy'); // so the title is not cached
        this.$el.find("[rel=tooltip]").tooltip({container: 'body', placement: 'top', title: this.errorMessage}).tooltip('show').hide();
    }
})
