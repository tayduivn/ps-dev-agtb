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
 * @class View.Fields.Base.NotificationCenterEventSwitcherField
 * @alias SUGAR.App.view.fields.BaseNotificationCenterEventSwitcherField
 * @extends View.Fields.Base.BaseField
 */
({
    fieldTag: 'input[data-type=event-switcher]',

    config: {},

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.config = (this.model.get('configMode') === 'user') ?
            this.model.get('personal')['config'] :
            this.model.get('config');

        this.model.on('reset:' + this.def.emitter, this.render, this);
        this.model.on('reset:all', this.render, this);

        // To watch carrier-switcher modifications.
        this.model.on('change:personal:' + this.def.emitter,  this.handleCarrierSwitcherChange, this);
    },

    /**
     * @inheritdoc
     */
    format: function(value) {
        // The first filter will be enough to make a decision.
        var sampleFilter = _.chain(this.config[this.def.emitter][this.def.event]).values().first().value();

        if (sampleFilter.length === 0) {
            value = false;
        } else {
            var checkedCarriers = [],
                carriers = (this.model.get('configMode') === 'user') ?
                this.model.get('personal')['carriers'] :
                this.model.get('carriers');

            _.each(this.config[this.def.emitter][this.def.event], function(filter) {
                _.each(filter, function(carriersArray) {
                    var carrierName = _.first(carriersArray);
                    if (!_.contains(checkedCarriers, carrierName)) {
                        checkedCarriers.push(carrierName);
                    }
                })
            });

            value = (!_.contains(checkedCarriers, '') && checkedCarriers.length > 0);
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
                eventToTrigger = 'change:event:' + this.def.emitter + ':' + this.def.event;

            _.each(this.config[this.def.emitter][this.def.event], function(filter, key, filterList) {
                if (checked) {
                    var carrierIdx = null;
                    _.each(filter, function(carrierData, idx) {
                        if (_.uniq(carrierData).length === 1 && _.uniq(carrierData).join() === '') {
                            carrierIdx = idx;
                            return;
                        }
                    });
                    if (carrierIdx !== null) {
                        filterList[key].splice(carrierIdx, 1);
                    }
                } else {
                    if (!_.chain(filter).flatten().uniq().some(
                            function(a) { return _.uniq(a).length === 1 && _.uniq(a).join() === ''; }
                        ).value()) {
                        filterList[key].push(['', '']);
                    }
                }
            }, this);

            this.model.trigger(eventToTrigger, checked);
            this.model.trigger('change:personal:emitter:' + this.def.emitter);

        }, this));
    },

    /**
     * React on Emitter's carrier-switcher modifications.
     * Un-checks and disables the whole event if all its carriers are switched-off.
     * @param {String} eventName name of the event.
     */
    handleCarrierSwitcherChange: function(eventName) {
        if (eventName !== this.def.event) {
            return;
        }

        var allEventFieldsSelector = 'input[data-type=carrier-switcher]' +
            '[data-emitter=' + this.def.emitter + ']' +
            '[data-event=' + this.def.event + ']';

        var allCarriersUnchecked = true;

        $(allEventFieldsSelector).each(function() {
            if ($(this).prop('checked') === true) {
                allCarriersUnchecked = false;
            }
        });

        if (allCarriersUnchecked) {
            var $el = $(this.fieldTag + '[data-emitter=' + this.def.emitter + ']' + '[data-event=' + this.def.event + ']');
            $el.prop('checked', false).trigger('change');
            this.render();
        }
    }
})
