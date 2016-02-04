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
 * @class Data.Base.NotificationCenterBean
 * @extends Data.Bean
 */
({
    /**
     * Copy filter values from 'global' config.
     *
     * @param {String} [emitter] Name of the emitter.
     * @param {String} [event] Name of the event.
     * @private
     */
    _copyFiltersFromDefault: function(emitter, event) {
        if (!this.get('personal')) {
            return;
        }

        var globalConfig = this.get('global')['config'];
        if (emitter === undefined) {
            _.each(this.get('personal')['config'], function(emitter, emitterName) {
                _.each(emitter, function(event, eventName) {
                    _.each(event, function(filter, filterName, event) {
                        event[filterName] = _.clone(globalConfig[emitterName][eventName][filterName]);
                    });
                });
            });
        } else if (event === undefined) {
            _.each(this.get('personal')['config'][emitter], function(event, eventName) {
                _.each(event, function(filter, filterName, event) {
                    event[filterName] = _.clone(globalConfig[emitter][eventName][filterName]);
                });
            });
        } else {
            _.each(this.get('personal')['config'][emitter][event], function(filter, filterName, eventObj) {
                eventObj[filterName] = _.clone(globalConfig[emitter][event][filterName]);
            });
        }
    },

    /**
     * Copy carriers status from 'global' config.
     * @private
     */
    _copyCarriersStatusFromDefault: function() {
        if (!this.get('personal')) {
            return;
        }
        var globalCarriers = this.get('global')['carriers'];
        _.each(this.get('personal')['carriers'], function(carrier, name) {
            carrier.status = globalCarriers[name].status;
        });
    },

    /**
     * Replace 'default' value to the current actual values of 'global' config.
     */
    replaceDefaultToActualValues: function() {
        if (!this.get('personal')) {
            return;
        }
        _.each(this.get('personal')['config'], function(emitter, emitterName) {
            _.each(emitter, function(event, eventName) {
                if (_.some(event, function(filter) {return _.isString(filter) && filter === 'default'})) {
                    this._copyFiltersFromDefault(emitterName, eventName);
                }
            }, this);
        }, this);
    },

    /**
     * Detect if given emitter has all settings by default.
     *
     * @param {String} emitterName Name of the emitter.
     * @returns {boolean} true if settings are the same as default, otherwise false.
     */
    isEmitterDefaultConfigured: function(emitterName) {
        var isDefault = true;

        if (!this.get('personal')) {
            return true;
        }
        _.each(this.get('personal')['config'][emitterName], function(event, eventName) {
            _.each(event, function(filter, filterName) {
                var filterGlobal = this.get('global')['config'][emitterName][eventName][filterName];
                if (JSON.stringify(_.chain(filter).map(_.first).uniq().sort().value()) !==
                    JSON.stringify(_.chain(filterGlobal).map(_.first).uniq().sort().value())) {
                    isDefault = false;
                }
            }, this);
        }, this);

        return isDefault;
    },

    /**
     * Reset all user preferences to system defaults.
     *
     * @param {String} emitterName Name of the emitter.
     * @returns {boolean} true is reset is allowed and ended successfully.
     */
    resetToDefault: function(emitterName) {
        if (!this.get('personal')) {
            return false;
        }

        if (emitterName === 'all') {
            this._copyCarriersStatusFromDefault();
            this._copyFiltersFromDefault();
            this.setSelectedAddresses();
        } else {
            this._copyFiltersFromDefault(emitterName);
        }

        this.trigger('reset:' + emitterName);
        return true;
    },

    /**
     * Extract all active delivery addresses and form model's "selectedAddresses" attribute.
     */
    setSelectedAddresses: function() {
        if (!this.get('personal')) {
            return;
        }

        var allCarriers = this.get('personal')['carriers'],
            addresses = {};
        // Prepare addresses
        _.each(allCarriers, function(carrier, name) {
            if (carrier.selectable) {
                addresses[name] = [];
            }
        });

        // Find and fill selected addresses for each enabled carrier.
        _.each(this.get('personal')['config'], function(emitter) {
            _.each(emitter, function(event) {
                var firstFilter = _.first(_.values(event));
                if (_.isArray(firstFilter)) {
                    _.each(firstFilter, function(carrierArray) {
                        var carrierName = _.first(carrierArray),
                            address = _.last(carrierArray);
                        if (address !== '' && allCarriers[carrierName].selectable &&
                            !_.contains(addresses[carrierName], address)) {
                            addresses[carrierName].push(address);
                        }
                    });
                }
            });
        });

        // If no addresses were selected by user, put the first one as default.
        _.each(allCarriers, function(carrier, name) {
            if (carrier.selectable && addresses[name].length === 0) {
                addresses[name].push(_.first(_.keys(carrier.options)));
            }
        });

        this.set('selectedAddresses', addresses);
    },

    /**
     * Update selected addresses for each enabled carrier before save.
     */
    updateCarriersAddresses: function() {
        if (this.get('configMode') !== 'user') {
            return;
        }

        // Eliminate empty and duplicate values.
        var addresses = this.get('selectedAddresses');
        _.each(addresses, function(val, key, list) {
            list[key] = _.uniq(_.filter(val, function(el) {return el !== ''}));
        });

        _.each(this.get('personal')['config'], function(emitter) {
            _.each(emitter, function(event) {
                // Filters' contents is the same, so we make decisions based on any of them, let's take the first.
                var firstFilter = _.first(_.values(event));
                if (_.isArray(firstFilter)) {
                    // Carriers that are checked for this event and have addresses.
                    var carriers = _.chain(firstFilter).map(_.first).uniq().intersection(_.keys(addresses)).value();

                    // Create a new array of carriers where addresses are set up.
                    var newCarriersArray = [];
                    _.each(firstFilter, function(carrierArray) {
                        if (!_.contains(carriers, _.first(carrierArray))) {
                            newCarriersArray.push(carrierArray);
                        }
                    });
                    _.each(carriers, function(carrierName) {
                        _.each(addresses[carrierName], function(address) {
                            newCarriersArray.push([carrierName, address]);
                        });
                    });

                    // Put new carriers to each filter
                    _.each(event, function(filter, filterName) {
                        event[filterName] = newCarriersArray;
                    });
                }
            });
        });
    }
})
