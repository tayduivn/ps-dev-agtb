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
 * @class View.Views.Base.NotificationCenterConfigGlobalDeliveryView
 * @alias SUGAR.App.view.layouts.BaseNotificationCenterConfigGlobalDeliveryView
 * @extends View.Views.Base.NotificationCenterConfigPanelView
 */
({
    extendsFrom: 'NotificationCenterConfigPanelView',

    /**
     * List of available carriers.
     * Is used to build emitter carriers' options table.
     */
    carriersList: {},

    /**
     * List of current emitter events.
     */
    eventsList: [],

    /**
     * Prototype of a carrier-switcher field.
     */
    carrierSwitcherPrototype: {
        name: 'carrier-switcher-prototype',
        type: 'carrier-switcher',
        view : 'default'
    },

    events: {
        'click [name=reset_to_default_button]': 'handleResetToDefault'
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this._getLabelAndDescriptionMeta();
        if (this.meta.label) {
            this.title = this.meta.label;
        }

        if (this.model.get('configMode') === 'user') {
            this.createResetButton();
        }

        this.model.on('change:personal:' + this.meta.emitter, this.displayResetButton, this);
        this.model.on('reset:all', this.displayResetButton, this);
        this.model.on('reset:' + this.meta.emitter, this.displayResetButton, this);
    },

    render: function() {
        this.populateCarriersAndEventsLists();
        this._super('render');
        this.displayResetButton();
    },

    /**
     * Populate carriersList and eventsList for template.
     */
    populateCarriersAndEventsLists: function() {
        this.carriersList = (this.model.get('configMode') === 'user') ?
            this.model.get('personal')['carriers'] :
            this.model.get('carriers');
        this.eventsList = this.generateRowsAndColumns();
    },

    /**
     * Create data-table of events and carriers.
     * @returns {Array}
     */
    generateRowsAndColumns: function() {
        var rows = [], existingEmitters;

        if (this.model.get('configMode') === 'user') {
            existingEmitters = this.model.get('personal')['config'];
        } else {
            existingEmitters = this.model.get('config');
        }

        if (existingEmitters) {
            _.each(existingEmitters[this.meta.emitter], function(event, eventKey) {
                var eventName = eventKey;
                var fields = [];
                _.each(this.carriersList, function(carrier, carrierKey) {
                    var meta = {
                        carrier: carrierKey,
                        action: 'switch-delivery',
                        event: eventName
                    }
                    fields.push(this._createCarrierSwitcherField(meta))
                }, this);
                rows.push({
                    rowSwitcher: this._createCarrierSwitcherField({
                        carrier: 'all',
                        action: 'switch-event',
                        event: eventName
                    }),
                    name: eventKey,
                    label: eventKey, //ToDo: app.list
                    fields: fields
                });
            }, this);
        }

        return rows;
    },

    /**
     * Create Reset Button metadata.
     */
    createResetButton: function() {
        this.meta['buttons'] = [
            {
                name: 'reset_to_default_button',
                type: 'button',
                label: 'LBL_RESET_TO_DEFAULT_BUTTON_LABEL',
                css_class: 'btn btn-invisible btn-link'
            }
        ];
    },

    /**
     * Check whether to display 'Reset to default' button or not.
     * If all filters in all events of this emitter have "default" setting, hide the button, otherwise display it.
     */
    displayResetButton: function() {
        var button = this.getField('reset_to_default_button'),
            isAllDefault = true;

        if (this.model.get('personal') && button) {
            _.each(this.model.get('personal')['config'][this.meta.emitter], function(event, eventName) {
                return _.each(event, function(filter, filterName) {
                    var filterGlobal = this.model.get('global')['config'][this.meta.emitter][eventName][filterName];
                    if (JSON.stringify(_.chain(filter).map(_.first).uniq().compact().value()) !==
                        JSON.stringify(_.chain(filterGlobal).map(_.first).uniq().compact().value())) {
                        isAllDefault = false;
                    }
                }, this);
            }, this);

            if (isAllDefault) {
                button.hide();
            } else {
                button.show();
            }
        }
    },

    /**
     * Helper for getting correct 'label' or 'description' strings for a given emitter.
     * @private
     */
    _getLabelAndDescriptionMeta: function() {
        var module, title, desc;
        switch(this.meta.emitter) {
            case 'ApplicationEmitter':
                title = 'LBL_APPLICATION_EMITTER_TITLE';
                desc = 'LBL_APPLICATION_EMITTER_DESC';
                module = this.module;
                break;
            case 'BeanEmitter':
                title = 'LBL_BEAN_EMITTER_TITLE';
                desc = (this.model.get('configMode') === 'user') ? 'LBL_BEAN_EMITTER_DESC_USER' : 'LBL_BEAN_EMITTER_DESC_ADMIN';
                module = this.module;
                break;
            default: // Module Emitter case
                title = 'LBL_EMITTER_TITLE';
                desc = (this.model.get('configMode') === 'user') ? 'LBL_EMITTER_DESC_USER' : 'LBL_EMITTER_DESC_ADMIN';
                module = this.meta.emitter;
        }

        this.meta.label = app.lang.get(title, module);
        this.meta.description = app.lang.get(desc, module);
    },

    /**
     * Generates a carrier-switcher field object from its prototype and given definition.
     * @param {Object} def Field definition.
     * @returns {Object} Field object.
     * @private
     */
    _createCarrierSwitcherField: function(def) {
        var field = _.clone(this.carrierSwitcherPrototype);

        field.name = this.name + '-' + def.carrier;
        field.emitter = this.meta.emitter;

        _.each(def, function(val, key) {
            field[key] = val;
        }, this);

        return field;
    },

    /**
     * Reset all user settings to system defaults.
     * @returns {boolean} returns false to stop propagation.
     */
    handleResetToDefault: function() {
        var message = app.lang.get('LBL_RESET_SETTINGS_EMITTER_CONFIRMATION', this.module),
            successMessage = app.lang.get('LBL_RESET_SETTINGS_SUCCESS', this.module);
        message = message.replace('%', this.meta.label);

        app.alert.show('reset_all_confirmation', {
            level: 'confirmation',
            messages: message,
            onConfirm: _.bind(function() {
                if (this.model.resetToDefault(this.meta.emitter)) {
                    app.alert.show('reset_all_success', {
                        level: 'success',
                        autoClose: true,
                        messages: successMessage
                    });
                }
            }, this)
        });
        return false;
    }
})
