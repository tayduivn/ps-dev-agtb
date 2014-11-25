// FILE SUGARCRM flav=ent ONLY
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
/**
 * @class View.Views.Base.OpportunitiesConfigOppsViewByView
 * @alias SUGAR.App.view.views.BaseOpportunitiesConfigOppsViewByView
 * @extends View.Views.Base.ConfigPanelView
 */
({
    extendsFrom: 'ConfigPanelView',

    /**
     * The current opps_view_by config setting when the view is initialized
     */
    currentOppsViewBySetting: undefined,

    /**
     * Stores if Forecasts is set up or not
     */
    isForecastsSetup: false,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.currentOppsViewBySetting = this.model.get('opps_view_by');

        // get the boolean form of if Forecasts is configured
        this.isForecastsSetup = !!app.metadata.getModule('Forecasts', 'config').is_setup;
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.model.on('change:opps_view_by', function() {
            if (this.isForecastsSetup && this.currentOppsViewBySetting !== this.model.get('opps_view_by')) {
                this.displayWarningAlert();
            }

            this.showRollupOptions();
        }, this);
    },

    /**
     * Displays the Forecast warning confirm alert
     */
    displayWarningAlert: function() {
        app.alert.show('forecast-warning', {
            level: 'confirmation',
            title: app.lang.get('LBL_WARNING'),
            messages: app.lang.get('LBL_OPPS_CONFIG_ALERT', 'Opportunities'),
            onConfirm: _.bind(function() {
                this.showRollupOptions();
            }, this),
            onCancel: _.bind(function() {
                this.model.set({
                    opps_view_by: this.currentOppsViewBySetting
                });
            }, this)
        });
    },

    /**
     * Displays the Latest/Earliest Date toggle
     */
    showRollupOptions: function() {
        if (this.currentOppsViewBySetting === 'RevenueLineItems' &&
            this.model.get('opps_view_by') === 'Opportunities') {
            this.getField('opps_closedate_rollup').show();
            this.$('[for=opps_closedate_rollup]').show();
            this.$('#sales-stage-text').show();

            // if there's no value here yet, set to latest
            if (!this.model.has('opps_closedate_rollup')) {
                this.$('input[value="latest"').attr('checked', true);
            }
        } else {
            this.getField('opps_closedate_rollup').hide();
            this.$('[for=opps_closedate_rollup]').hide();
            this.$('#sales-stage-text').hide();
        }

        // update the title based on settings
        this.updateTitle();
    },

    /**
     * @inheritdoc
     */
    _render: function(options) {
        this._super('_render', [options]);

        this.showRollupOptions();
    },

    /**
     * @inheritdoc
     * @override
     */
    _updateTitleValues: function() {
        var title = app.lang.getAppListStrings('opps_config_view_by_options_dom') || '';

        // defensive coding in case user removed this options dom
        if (title && _.isObject(title)) {
            title = title[this.model.get('opps_view_by')]
        }

        this.titleSelectedValues = title;
    }
})
