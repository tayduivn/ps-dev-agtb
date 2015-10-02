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
    carrierSwitcherPrototype : {
        name: 'carrier-switcher-prototype',
        type: 'carrier-switcher',
        view : 'default'
    },

    events: {
        'click input[data-action=switch-event]': 'handleRowSwitch'
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this._getLabelAndDescriptionMeta();
        if (this.meta.label) {
            this.titleViewNameTitle = this.meta.label;
        }
        this.before('render', this.populateCarriersAndEventsLists, this);
    },

    /**
     * Populate carriersList and eventsList for template.
     */
    populateCarriersAndEventsLists: function() {
        this.carriersList = this.model.get('carriers');
        this.eventsList = this.generateRowsAndColumns();
    },

    generateRowsAndColumns: function() {
        var rows = [],
            existingEmitters = this.model.get('config');

        if (existingEmitters) {
            _.each(existingEmitters[this.meta.emitter], function(event, eventKey) {
                var eventName = eventKey;
                var fields = [];
                _.each(this.model.get('carriers'), function(carrier, carrierKey) {
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
     * @returns {Field}
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
     * Handle delivery switching for the whole event.
     * @param evt
     */
    handleRowSwitch: function(evt) {
        var event = $(evt.currentTarget).data('event');
        var selector =
            'input[data-type=' +
            this.carrierSwitcherPrototype.type + '][data-action=switch-delivery][data-event='
            + event + ']';

        if ($(evt.currentTarget).is(':checked')) {
            this.$el.find(selector).removeAttr('disabled');
        } else {
            this.$el.find(selector).attr('disabled', true);
        }
    }
})
