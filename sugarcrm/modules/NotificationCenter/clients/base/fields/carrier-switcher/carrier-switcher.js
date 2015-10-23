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

        // When model resets itself to default.
        this.model.on('reset:' + this.def.emitter, this.render, this);
        this.model.on('reset:all', this.render, this);

        // Carrier's events.
        var onCarrierChangeEvent = (this.model.get('configMode') === 'user') ?
            'change:personal:carrier:' + this.def.carrier :
            'change:carrier:' + this.def.carrier;
        this.model.on(onCarrierChangeEvent, this.render, this);

        // Emitter's event events. To watch event-switcher modifications.
        this.model.on('change:event:' + this.def.emitter + ':' + this.def.event, this.handleEventSwitcherChange, this);
    },

    /**
     * Set up field display value and status.
     * Method finds carrier status in config model taking into account section, emitter and event of this field.
     * @inheritdoc
     */
    format: function(value) {
        var eventFilters = this.config[this.def.emitter][this.def.event];

        value = {status: false, disabled: false};

        // For now we neglect event filters, so let's do flatten-unique all filters' carriers.
        var filtersData = _.uniq(_.flatten(eventFilters));
        value.status = _.contains(filtersData, this.def.carrier);

        // Set how field will be displayed.
        var carriers = (this.model.get('configMode') === 'user') ?
            this.model.get('personal')['carriers'] :
            this.model.get('carriers');

        // The first filter will be enought to make a decision.
        var sampleFilter = _.chain(eventFilters).values().first().value();

        // If carrier or the entire event is disabled - disable this delivery-carrier.
        if (_.some(sampleFilter, function(a) { return _.uniq(a).join() === ''; }) || sampleFilter.length === 0) {
            value.disabled = true;
        } else if (carriers[this.def.carrier].status === false) {
            value.disabled = true;
        } else {
            value.disabled = false;
        }

        return value;
    },

    /**
     * @inheritDoc
     */
    bindDomChange: function() {
        var $el = this.$(this.fieldTag);

        $el.on('change', _.bind(function() {
            var checked = $el.prop('checked'),
                carrier = this.def.carrier,
                config = _.clone(this.config);

            _.each(config[this.def.emitter][this.def.event], function(filter, key, filterList) {
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
                });
            }, this);

            this.model.trigger('change:personal:' + this.def.emitter, this.def.event);

        }, this));
    },

    /**
     * React on Emitter's Event switch operation.
     * @param {Boolean} mode. True is for 'on', false is for 'off'.
     */
    handleEventSwitcherChange: function(mode) {
        if (mode) {
            var carriers = (this.model.get('configMode') === 'user') ?
                this.model.get('personal')['carriers'] :
                this.model.get('carriers');

            var allEventFieldsSelector = this.fieldTag +
                '[data-emitter=' + this.def.emitter + ']' +
                '[data-event=' + this.def.event + ']';

            if (_.every($(allEventFieldsSelector), function(el) { return $(el).prop('checked') === false })) {
                $(allEventFieldsSelector).each(function() {
                    var carrier = $(this).data('carrier');
                    if (carriers[carrier].status) {
                        $(this).prop('checked', true).trigger('change');
                    }
                });
            }
        }

        this.render();
    }
})
