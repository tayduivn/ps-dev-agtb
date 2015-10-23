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
 * @class View.Layouts.Base.NotificationCenterConfigDrawerLayout
 * @alias SUGAR.App.view.layouts.BaseNotificationCenterConfigDrawerLayout
 * @extends View.Layouts.Base.ConfigDrawerLayout
 */
({
    extendsFrom: 'ConfigDrawerLayout',

    /**
     * What type of a config we are currently viewing.
     * default - is admin only available. Allows to configure default system Notifications Center settings.
     * user - is for any user. Allows to configure User Notification Center preferences.
     */
    section: 'user',

    /**
     * Grab the current config mode.
     * @inheritdoc
     */
    initialize: function(options) {
        var section = options.context.get('section');
        if (section && section === 'default') {
            this.section = 'global';
        }
        this._super('initialize', [options]);
        this.model.set('configMode', this.section);
        this.prepareModel();
    },

    /**
     * Notification Center does not store its configuration in system 'config' table,
     * thus 'config' in app.metadata is not created for it. But we know, that this module is configurable.
     * @inheritdoc
     * @override
     */
    _checkConfigMetadata: function() {
        return true;
    },

    /**
     * We load config data because of the extended model's URL, not 'ModuleName/config'.
     * When we upgrade to backbone > 0.9.10 hard-code binding of the url to models methods in config-header-buttons
     * will be eliminated and we api call to model.fetch/save etc.;
     * @inheritdoc
     */
    loadConfig: function(options) {
        var configSection = (this.section === 'global') ? '/global' : '';
        var url = app.api.buildURL(this.module, 'config' + configSection);
        var self = this;
        app.api.call('read', url, null, {
                success: function(data) {
                    _.each(data, function(val, key) { self.model.set(key, val); }, self);
                    self.model.replaceDefaultToActualValues();
                    self.model.setSelectedAddresses();
                    self.render();
                }
            }
        );
    },

    /**
     * Create model's additional methods.
     */
    prepareModel: function() {

        // Copies filter values from 'global' config.
        this.model._copyFiltersFromDefault = function(emitter) {
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
            } else {
                _.each(this.get('personal')['config'][emitter], function(event, eventName) {
                    _.each(event, function(filter, filterName, event) {
                        event[filterName] = _.clone(globalConfig[emitter][eventName][filterName]);
                    });
                });
            }

        };

        // Copies carriers status from 'global' config.
        this.model._copyCarriersStatusFromDefault = function() {
            if (!this.get('personal')) {
                return;
            }
            var globalCarriers = this.get('global')['carriers'];
            _.each(this.get('personal')['carriers'], function(carrier, name) {
                carrier.status = globalCarriers[name].status;
            });
        };

        // Replaces 'default' value to the current actual values of 'global' config.
        this.model.replaceDefaultToActualValues = function() {
            if (!this.get('personal')) {
                return;
            }

            var emittersWithDefaultSetting = [];

            _.each(this.get('personal')['config'], function(emitter, emitterName) {
                var firstEvent = emitter[_.first(_.keys(emitter))],
                    firstFilter = firstEvent[_.first(_.keys(firstEvent))];

                if (firstFilter === 'default') {
                    emittersWithDefaultSetting.push(emitterName);
                }
            });

            _.each(emittersWithDefaultSetting, function(emitter) {
                this._copyFiltersFromDefault(emitter);
            }, this)
        };

        // Resets all user preferences to system defaults.
        this.model.resetToDefault = function(emitterName) {
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
        };

        // Extracts all active delivery addresses and forms model's "selectedAddresses" attribute.
        this.model.setSelectedAddresses = function() {
            if (!this.get('personal')) {
                return false;
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
        };

        // Before save update selected addresses for each enabled carrier.
        this.model.updateCarriersAddresses = function() {
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

    },

    /**
     * This module has no Bean and thus no ACLs.
     * But it's allowed to be accessed by any user, with only one caveat:
     * only admin-user can obtain access to the global configuration of Notification Center.
     *
     * @inheritdoc
     */
    _checkUserAccess: function() {
        var access = false;
        if (this.section === 'user') {
            access = true;
        } else {
            access = (app.user.get('type') === 'admin');
        }
        return access;
    }
})
