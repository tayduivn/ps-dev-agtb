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
     * Reset selected Carriers Options to default.
     * @private
     */
    _resetSelectedCarriersOptions: function() {
        if (!this.get('personal')) {
            return;
        }

        var carriers = this.get('personal').carriers;
        _.each(this.get('personal').selectedCarriersOptions, function(options, name) {
            this.get('personal').selectedCarriersOptions[name] = [_.first(_.keys(carriers[name].addressTypeOptions))];
        }, this);
    },

    /**
     * Replace 'default' value to the current actual values of 'global' config.
     * Set defaults for selectedCarriersOptions if no one found.
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

        // set default selectedCarriersOptions if no one found
        var carriers = this.get('personal').carriers;
        var selectedCarriersOptions = this.get('personal').selectedCarriersOptions;
        _.each(carriers, function(carrier, name) {
            if (carriers[name].options.deliveryDisplayStyle !== 'none' && !selectedCarriersOptions[name]) {
                selectedCarriersOptions[name] = [_.first(_.keys(carrier.addressTypeOptions))];
            }
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
            this._resetSelectedCarriersOptions();
        } else {
            this._copyFiltersFromDefault(emitterName);
        }

        this.trigger('reset:' + emitterName);
        return true;
    },

    /**
     * Update selected addresses for each enabled carrier before save.
     */
    updateCarriersAddresses: function() {
        if (this.get('configMode') !== 'user') {
            return;
        }

        // Eliminate empty and duplicate values.
        var addresses = this.get('personal').selectedCarriersOptions;
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
                        // Set the first address as default if no one is selected.
                        if (addresses[carrierName].length === 0) {
                            newCarriersArray.push([carrierName, '0']);
                        }
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
