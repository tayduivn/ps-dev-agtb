/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

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
     * Holds the collapsible toggle title template
     */
    toggleTitleTpl: {},

    /**
     * Holds the select2 reference to the #wkstColumnSelect element
     */
    wkstColumnsSelect2: {},

    /**
     * Holds the default/selected items
     */
    selectedOptions:[],

    /**
     * Holds all items
     */
    allOptions:[],

    events: {
        'click .resetLink': 'onResetLinkClicked'
    },

    /**
     * {@inheritdoc}
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.titleViewNameTitle = app.lang.get('LBL_FORECASTS_CONFIG_TITLE_WORKSHEET_COLUMNS', 'Forecasts');
        this.toggleTitleTpl = app.template.getView('forecastsConfigHelpers.toggleTitle', 'Forecasts');

        var cfgFields = app.metadata.getModule('Forecasts', 'config').worksheet_columns;

        // set up scenarioOptions
        _.each(options.meta.panels[0].fields, function(field) {
            var labelModule = (!_.isUndefined(field.label_module)) ? field.label_module : 'Forecasts',
                obj = {
                    id: field.name,
                    text: app.lang.get(field.label, labelModule)
                },
                cField = _.find(cfgFields, function(cfgField) {
                    return cfgField == field.name;
                }, this);

            this.allOptions.push(obj);

            // If the current field being processed was found in the config fields,
            if(!_.isUndefined(cField)) {
                // push field to defaults
                this.selectedOptions.push(obj);
            }
        }, this);
    },

    /**
     * Handles when reset to defaults link has been clicked
     *
     * @param {jQuery.Event} evt click event
     */
    onResetLinkClicked: function(evt) {
        evt.preventDefault();
        evt.stopImmediatePropagation();

        /**
         * todo implement resetting to defaults
         */
    },

    /**
     * {@inheritdoc}
     */
    bindDataChange: function() {
        if(this.model) {
            this.model.on('change:columns', function(model) {
                var arr = [],
                    cfgFields = this.model.get('worksheet_columns'),
                    metaFields = this.meta.panels[0].fields;

                _.each(metaFields, function(metaField) {
                    _.each(cfgFields, function(field) {
                        if(metaField.name == field) {
                            var labelModule = (!_.isUndefined(metaField.label_module)) ? metaField.label_module : 'Forecasts';
                            arr.push(app.lang.get(metaField.label, labelModule));
                        }
                    }, this);
                }, this);
                this.titleSelectedValues = arr.join(', ');

                // Handle truncating the title string and adding "..."
                this.titleSelectedValues = this.titleSelectedValues.slice(0,50) + "...";

                this.updateTitle();
            }, this);

            // trigger the change event to set the title when this gets added
            this.model.trigger('change:columns', this.model);
        }
    },

    /**
     * Updates the accordion toggle title
     */
    updateTitle: function() {
        var tplVars = {
            title: this.titleViewNameTitle,
            selectedValues: this.titleSelectedValues,
            viewName: 'forecastsConfigWorksheetColumns'
        };

        this.$el.find('#' + this.name + 'Title').html(this.toggleTitleTpl(tplVars));
    },

    /**
     * {@inheritdoc}
     */
    _render: function() {
        app.view.View.prototype._render.call(this);
        // add accordion-group class to wrapper $el div
        this.$el.addClass('accordion-group');
        this.updateTitle();

        // handle setting up select2 options
        this.wkstColumnsSelect2 = this.$el.find('#wkstColumnsSelect').select2({
            data: this.allOptions,
            multiple: true,
            containerCssClass: "select2-choices-pills-close",
            initSelection : _.bind(function (element, callback) {
                callback(this.selectedOptions);
            }, this)
        });
        this.wkstColumnsSelect2.select2('val', this.selectedOptions);

        this.wkstColumnsSelect2.on('change', _.bind(this.handleColumnModelChange, this));
    },

    /**
     * Handles the select2 adding/removing columns
     *
     * @param evt change event from the select2 selected values
     */
    handleColumnModelChange: function(evt) {
        var arr = [];
        _.each($(evt.target).val().split(','), function(field) {
            arr.push(field);
        }, this);

        this.model.set('worksheet_columns', arr);
        this.model.trigger('change:columns', this.model);
    },

    /**
     * {@inheritdoc}
     *
     * override dispose function to remove custom listener off select2 instance
     */
    _dispose: function() {
        // remove event listener from select2
        this.wkstColumnsSelect2.off();
        app.view.Component.prototype._dispose.call(this);
    }
})
