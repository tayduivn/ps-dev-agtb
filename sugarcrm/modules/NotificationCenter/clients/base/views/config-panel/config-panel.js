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
        if (this.meta.label) {
            this.title = app.lang.get(this.meta.label, this.module);
        }
    },

    /**
     * @inheritdoc
     */
    _loadTemplate: function() {
        var templateName = (this.meta.template) ? this.meta.template : this.type;
        this.template = app.template.getView(templateName, this.module);
    },

    /**
     * This view uses embedded title.
     */
    updateTitle: function() {
    }
})
