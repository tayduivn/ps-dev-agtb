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

    events: {
        'click .resetLink': 'onResetLinkClicked'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        // todo: this is just a placeholder so I dont have to implement some silly logic to remove a colon
        // todo: when updating this module to make this panel work please remove this lines
        this.titleSelectedValues = 'Name, Expected Close, Stage, Likel...';

        this.titleViewNameTitle = app.lang.get('LBL_FORECASTS_CONFIG_TITLE_WORKSHEET_COLUMNS', 'Forecasts');
        this.toggleTitleTpl = app.template.getView('forecastsConfigHelpers.toggleTitle', 'Forecasts');
    },

    /**
     * Handles when reset to defaults link has been clicked
     * @param evt click event
     */
    onResetLinkClicked: function(evt) {
        evt.preventDefault();
        evt.stopImmediatePropagation();

        /**
         * todo implement resetting to defaults
         */
        console.log('reset link clicked for Worksheet Columns');
    },

    /**
     * todo implement the bindDataChange to listen to the model changing when new columns are added or removed
     * to update the this.titleSelectedValues var and call updateTitle so the collapse toggle title is correct
     */

    /**
     * Updates the accordion toggle title
     */
    updateTitle: function() {
        var tplVars = {
            title: this.titleViewNameTitle,
            selectedValues: this.titleSelectedValues,
            viewName: 'forecastsConfigWorksheetColumns'
        };

        this.$el.find('#wkstColumnsTitle').html(this.toggleTitleTpl(tplVars));
    },

    _render: function() {
        app.view.View.prototype._render.call(this);

        // add accordion-group class to wrapper $el div
        this.$el.addClass('accordion-group');
        this.updateTitle();
    }
})
