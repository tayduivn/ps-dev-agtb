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
     * Holds the warning template with data for the opps_view_by field
     */
    $warningEl: undefined,

    /**
     * Boolean if the Forecasts module is set up and $warningEl has been created
     */
    hasWarningText: false,

    /**
     * The current opps_view_by config setting when the view is initialized
     */
    currentOppsViewBySetting: undefined,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.currentOppsViewBySetting = this.model.get('opps_view_by');

        if (app.metadata.getModule('Forecasts', 'config').is_setup) {
            this.createWarningEl();
        }
    },

    /**
     * Creates the `this.$warningEl` dom element with the proper text
     */
    createWarningEl: function() {
        this.hasWarningText = true;
        var warningObj = {
                warningText: app.lang.get('LBL_OPPS_CONFIG_VIEW_BY_WARNING_FIELD_TEXT', 'Opportunities')
            },
            tpl = app.template.getView('config-opps-view-by.forecast-warning', 'Opportunities');
        this.$warningEl = tpl(warningObj);
    },

    /**
     * Appends the `this.$warningEl` dom element into the opps_view_by field
     */
    appendWarning: function() {
        var $labelEl = this.$('label[for="opps_view_by"]');
        if ($labelEl) {
            $labelEl.append(this.$warningEl)
        }
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.model.on('change:opps_view_by', function() {
            if (this.hasWarningText && this.currentOppsViewBySetting !== this.model.get('opps_view_by')) {
                this.displayWarningAlert();
            }
        }, this);
    },

    /**
     * Displays the Forecast warning confirm alert
     */
    displayWarningAlert: function() {
        app.alert.show('forecast-warning', {
            level: 'confirmation',
            title: app.lang.get('LBL_WARNING'),
            messages: app.lang.get('LBL_OPPS_CONFIG_VIEW_BY_WARNING_ALERT_TEXT', 'Opportunities'),
            onConfirm: _.bind(function() {
                /**
                 *
                 * CALL ENDPOINT TO ERASE FORECAST DATA
                 *
                 */
            }, this),
            onCancel: _.bind(function() {
                this.model.set({
                    opps_view_by: this.currentOppsViewBySetting
                });
            }, this)
        });
    },

    /**
     * @inheritdoc
     */
    _render: function(options) {
        this._super('_render', [options]);

        if (this.hasWarningText) {
            this.appendWarning();
        }
    },

    /**
     * @inheritdoc
     * @override
     */
    _updateTitleValues: function() {
        this.titleSelectedValues = this.model.get('opps_view_by');
    }
})
