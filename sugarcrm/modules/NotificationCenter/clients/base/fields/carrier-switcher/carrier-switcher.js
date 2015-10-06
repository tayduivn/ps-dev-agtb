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
     * Denotes current config mode.
     */
    section: undefined,

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.section = (this.model.get('configMode') === 'user') ? 'personal' : 'config'
        this.before('render', this.setFieldValue, this);
    },

    /**
     * Set up field value and status.
     * Method finds carrier status in config model taking into account section, emitter and event of this field.
     */
    setFieldValue: function() {
        var value = false,
            status = true,
            config = this.model.get(this.section),
            eventFilters = config[this.def.emitter][this.def.event];

        if (this.def.action === 'switch-delivery') {
            // For now we neglect event filters, so let's do flatten-unique all filters' carriers.
            value = _.contains(_.uniq(_.flatten(_.map(eventFilters, _.values))), this.def.carrier);
        } else if (this.def.action === 'switch-event') {
            // ToDo: dependencies
            //value = ( _.flatten(_.map(eventFilters, _.values)) === [] );
            value = true;
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
                config = _.clone(this.model.get(this.section)),
                modelEvent = '';

            if (this.def.action === 'switch-delivery') {
                _.each(config[this.def.emitter][this.def.event], function(filter) {
                    if (checked) {
                        if (!_.chain(filter).flatten().uniq().contains(carrier).value()) {
                            filter.push([carrier, '']);
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
                            filter.splice(carrierIdx, 1);
                        }
                    }
                }, this);
            } else if (this.def.action === 'switch-event') {
                // ToDo: dependencies
            }

            this.model.set(this.section, config);
            this.model.trigger(modelEvent);
        }, this));
    }
})

