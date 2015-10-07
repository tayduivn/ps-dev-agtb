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
 * @class View.Fields.Base.NotificationCenterCarrierSwitcherField
 * @alias SUGAR.App.view.fields.BaseNotificationCenterCarrierSwitcherField
 * @extends View.Fields.Base.BaseField
 */
({
    fieldTag: 'input[data-type=carrier-switcher]',

    /**
     * Current config.
     */
    config: {},

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.config = (this.model.get('configMode') === 'user') ?
            this.model.get('personal')['config'] :
            this.model.get('config');
        this.before('render', this.setFieldValue, this);

        // Carrier events.
        var onCarrierChangeEvent = (this.model.get('configMode') === 'user') ?
            'change:personal:carrier:' + this.def.carrier :
            'change:carrier:' + this.def.carrier;
        this.model.on(onCarrierChangeEvent, this.render, this);

        // Reset events.
        this.model.on('reset:' + this.def.emitter, this.render, this);
        this.model.on('reset:all', this.render, this);
    },

    /**
     * Set up field value and status.
     * Method finds carrier status in config model taking into account section, emitter and event of this field.
     */
    setFieldValue: function() {
        var value = false,
            status = true,
            eventFilters = this.config[this.def.emitter][this.def.event],
            carriers = (this.model.get('configMode') === 'user') ?
            this.model.get('personal')['carriers'] :
            this.model.get('carriers');

        if (this.def.action === 'switch-delivery') {
            // For now we neglect event filters, so let's do flatten-unique all filters' carriers.
            var filtersData = _.uniq(_.flatten(eventFilters));
            value = _.contains(filtersData, this.def.carrier);

            // Set how field will be displayed.
            status = carriers[this.def.carrier].status;

        } else if (this.def.action === 'switch-event') {
            var checkedCarriers = [];
            _.each(eventFilters, function(filter) {
                _.each(filter, function(carriersArray) {
                    var carrierName = _.first(carriersArray)
                    if (!_.contains(checkedCarriers, carrierName)) {
                        checkedCarriers.push(carrierName);
                    }
                })
            });
            value = (!_.contains(checkedCarriers, '') && checkedCarriers.length > 0);
        }

        this.def.value = value;
        this.def.status = status;
    },

    /**
     * @inheritDoc
     */
    bindDomChange: function() {
        var el = this.$(this.fieldTag);

        el.on('change', _.bind(function() {
            var checked = el.is(':checked'),
                carrier = this.def.carrier,
                config = _.clone(this.config),
                modelEvent = '';

            if (this.def.action === 'switch-delivery') {
                _.each(config[this.def.emitter][this.def.event], function(filter, key, filterList) {
                    if (checked) {
                        if (!_.chain(filter).flatten().uniq().contains(carrier).value()) {
                            filterList[key].push([carrier, '']);
                        }
                    } else {
                        var carrierIdx = null;
                        _.each(filter, function(carrierData, idx) {
                            if (_.contains(carrierData, carrier)) {
                                carrierIdx = idx;
                                return;
                            }
                        });
                        if (carrierIdx !== null) {
                            filterList[key].splice(carrierIdx, 1);
                        }
                    }
                }, this);
            } else if (this.def.action === 'switch-event') {
                // ToDo: dependencies
            }

            this.model.trigger('change:personal:' + this.def.emitter);

        }, this));
    }
})

