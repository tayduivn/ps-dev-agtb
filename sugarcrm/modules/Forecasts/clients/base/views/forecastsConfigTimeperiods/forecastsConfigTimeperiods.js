/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

({
    /**
     * Holds the changing date value for the title
     */
    titleSelectedValues: '',

    /**
     * Holds the view's title name
     */
    titleViewNameTitle: '',

    /**
     * Holds the message part of the toggleTitle template
     */
    titleMessage: '',

    /**
     * Holds the collapsible toggle title template
     */
    toggleTitleTpl: {},

    /**
     * Local var for if Forecasts config has been set up or not
     */
    forecastIsSetup: undefined,

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        /**
         * This is needed to make sure that this view is read only when forecasts module has been set up.
         */
        this.forecastIsSetup = app.metadata.getModule('Forecasts', 'config').is_setup;
        if(!this.forecastIsSetup) {
            _.each(_.first(options.meta.panels).fields, function(field) {
                if(field.name == 'timeperiod_start_date') {
                    field.click_to_edit = true;
                }
            }, this);
        }
        this._super('initialize', [options]);
        this.titleViewNameTitle = app.lang.get('LBL_FORECASTS_CONFIG_TITLE_TIMEPERIODS', 'Forecasts');
        this.titleMessage = app.lang.get('LBL_FORECASTS_CONFIG_TITLE_MESSAGE_TIMEPERIODS', 'Forecasts');
        this.toggleTitleTpl = app.template.getView('forecastsConfigHelpers.toggleTitle', 'Forecasts');
    },

    /**
     * {@inheritdoc}
     */
    bindDataChange: function() {
        if(this.model) {
            this.model.on('change', function(model) {
                // on a fresh install with no demo data,
                // this.model has the values and the param model is undefined
                if(_.isUndefined(model)) {
                    model = this.model;
                }

                if(model.changed['timeperiod_start_date']) {
                    var tmpD = new Date(new Date(model.changed['timeperiod_start_date']))
                    tmpD = new Date(tmpD.getTime() + (tmpD.getTimezoneOffset() * 60000));

                    this.titleSelectedValues = app.date.format(tmpD, app.user.getPreference('datepref'));
                    this.updateTitle();
                }
            }, this);
        }
    },

    /**
     * Updates the accordion toggle title
     */
    updateTitle: function() {
        var tplVars = {
            title: this.titleViewNameTitle,
            message: this.titleMessage,
            selectedValues: this.titleSelectedValues,
            viewName: 'forecastsConfigTimeperiods'
        };

        this.$el.find('#' + this.name + 'Title').html(this.toggleTitleTpl(tplVars));
    },

    /**
     * {@inheritdocs}
     *
     * Sets up a binding to the start month dropdown to populate the day drop down on change
     *
     * @param {View.Field} field
     * @private
     */
    _renderField: function(field) {
        field = this._setUpTimeperiodConfigField(field);

        // check for all fields, if forecast is setup, set to detail/readonly mode
        if(this.forecastIsSetup) {
            field.options.def.view = 'detail';
        } else if(field.name == 'timeperiod_start_date') {
            // if this is the timeperiod_start_date field and Forecasts is not setup
            field.options.def.click_to_edit = true;
        }

        app.view.View.prototype._renderField.call(this, field);
    },

    /**
     * {@inheritdoc}
     */
    _render: function() {
        app.view.View.prototype._render.call(this);

        // add accordion-group class to wrapper $el div
        this.$el.addClass('accordion-group');
        this.updateTitle();
    },

    /**
     * Sets up the fields with the handlers needed to properly get and set their values for the timeperiods config view.
     *
     * @param {View.Field} field the field to be setup for this config view.
     * @return {*} field that has been properly setup and augmented to function for this config view.
     * @private
     */
    _setUpTimeperiodConfigField: function(field) {
        switch(field.name) {
            case "timeperiod_shown_forward":
            case "timeperiod_shown_backward":
                return this._setUpTimeperiodShowField(field);
            case "timeperiod_interval":
                return this._setUpTimeperiodIntervalBind(field);
            default:
                return field;
        }
    },

    /**
     * Sets up the timeperiod_shown_forward and timeperiod_shown_backward dropdowns to set the model and values properly
     *
     * @param {View.Field} field The field being set up.
     * @return {*} The configured field.
     * @private
     */
    _setUpTimeperiodShowField: function (field) {
        // ensure Date object gets an additional function
        field.events = _.extend({"change input":  "_updateSelection"}, field.events);
        field.bindDomChange = function() {};

        field._updateSelection = function(event) {
            var value =  $(event.currentTarget).val();
            this.def.value = value;
            this.model.set(this.name, value);
        };

        // force value to a string so hbs has helper will match the dropdown correctly
        this.model.set(field.name, this.model.get(field.name).toString(), {silent: true});

        field.def.value = this.model.get(field.name) || 1;
        return field;
    }

    ,
    /**
     * Sets up the change event on the timeperiod_interval drop down to maintain the interval selection
     * and push in the default selection for the leaf period
     *
     * @param {View.Field} field the dropdown interval field
     * @return {*}
     * @private
     */
    _setUpTimeperiodIntervalBind: function(field) {

        field.def.value = this.model.get(field.name);

        // ensure selected day functions like it should
        field.events = _.extend({"change input":  "_updateIntervals"}, field.events);
        field.bindDomChange = function() {};

        if(typeof(field.def.options) == 'string') {
            field.def.options = app.lang.getAppListStrings(field.def.options);
        }

        /**
         * function that updates the selected interval
         * @param event
         * @private
         */
        field._updateIntervals = function(event) {
            //get the timeperiod interval selector
            var selected_interval = $(event.currentTarget).val();
            this.def.value = selected_interval;
            this.model.set(this.name, selected_interval);
            this.model.set('timeperiod_leaf_interval', selected_interval == 'Annual' ? 'Quarter' : 'Month');
        }

        return field;
    }
})
