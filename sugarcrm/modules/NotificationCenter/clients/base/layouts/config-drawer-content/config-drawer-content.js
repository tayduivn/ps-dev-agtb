/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Layouts.Base.NotificationCenterConfigDrawerContentLayout
 * @alias SUGAR.App.view.layouts.BaseNotificationCenterConfigDrawerContentLayout
 * @extends View.Layouts.Base.ConfigDrawerContentLayout
 */
({
    extendsFrom: 'ConfigDrawerContentLayout',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.before('render', function() { this._createViews(); }, this);
    },

    /**
     * Notification Center config-panel views metadata can be an object.
     * @inheritdoc
     */
    selectPanel: function(panelName) {
        panelName = (_.isObject(panelName) && panelName.name) ? panelName.name : panelName;
        this._super('selectPanel', [panelName]);
    },

    /**
     * Dynamically sets config-panel views in metadata for carriers and each known emitter.
     * Number of generated views depends on how many Notification Center emitters are returned are found in config.
     * @private
     */
    _createViews: function() {
        var emitters;

        if (this.model.get('configMode') === 'user') {
            emitters = this.model.get('personal') ? this.model.get('personal')['config'] : null;
        } else {
            emitters = this.model.get('config');
        }

        if (!emitters) {
            return;
        }

        // Get rid of a spare config-carriers view.
        this._components[0].dispose();
        this.removeComponent(0);

        // Emitter views.
        _.each(emitters, function(val, key) {
            this.meta.components.push({
                view: {
                    name: 'config-' + key,
                    type: 'config-emitter',
                    emitter: key
                }
            });
        }, this);

        // Initialize and add component
        _.each(this.meta.components, function(def) {
            var view = this.createComponentFromDef(def);
            this.addComponent(view, def);
        }, this);
    },

    /**
     * @inheritdoc
     */
    _switchHowToData: function(helpId) {
        var title, text;

        switch(helpId) {
            case 'config-carriers':
                title = app.lang.get('LBL_CARRIER_DELIVERY_OPTION_TITLE', this.module);
                text = app.lang.get('LBL_CARRIER_DELIVERY_OPTION_HELP', this.module);
                break;
            case 'config-ApplicationEmitter':
                title = app.lang.get('LBL_APPLICATION_EMITTER_TITLE', this.module);
                text = app.lang.get('LBL_APPLICATION_EMITTER_HELP', this.module);
                break;
            case 'config-BeanEmitter':
                title = app.lang.get('LBL_APPLICATION_EMITTER_TITLE', this.module);
                text = app.lang.get('LBL_BEAN_EMITTER_HELP', this.module);
                break;
            default: // Module Emitter case
                var module = helpId.substring(7);
                title = app.lang.get('LBL_EMITTER_TITLE', module);
                text = app.lang.get('LBL_EMITTER_HELP', module);
        }

        this.currentHowToData.title = title;
        this.currentHowToData.text = text;
    }
})
