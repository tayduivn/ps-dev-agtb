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
 * @class View.Views.Base.NotificationCenterConfigPanelView
 * @alias SUGAR.App.view.layouts.BaseNotificationCenterConfigPanelView
 * @extends View.Views.Base.ConfigPanelView
 */
({
    extendsFrom: 'ConfigPanelView',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.toggleTitleTpl = app.template.getView('config-panel.title', this.module);
    },

    /**
     * Add default description.
     * @inheritdoc
     */
    _updateTitleTemplateVars: function() {
        this._super('_updateTitleTemplateVars');
        this.titleTemplateVars.description = this._getPanelDescription();
    },

    /**
     * Get panel description from panel's metadata.
     * @returns {string} Panel description.
     * @private
     */
    _getPanelDescription: function() {
        return (this.meta.description) ? app.lang.get(this.meta.description, this.module) : '';
    }
})
