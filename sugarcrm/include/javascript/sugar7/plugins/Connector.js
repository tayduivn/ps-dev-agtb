/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

(function (app) {
    app.events.on("app:init", function () {
        var hashKey = null;
        var pinged = false;
        app.plugins.register('Connector', ['view'], {

            /**
             * Check if a specific connector is valid. If it is, call the success call
             * @param {string} name
             * @param {function} successCall
             * @param {function} errorCall
             * @param {array} connectorCriteria
             */
            checkConnector: function (name, successCall, errorCall, connectorCriteria) {
                var connectors,
                    connector = null;
                var successCallWrapper = _.bind(function () {
                    this.checkConnector(name, successCall, errorCall, connectorCriteria);
                }, this);

                if (hashKey === null) {
                    pinged = true;
                    this.getConnectors(name, successCallWrapper);
                }
                else {
                    connectors = app.cache.get(hashKey);
                    connector = connectors[name];
                    // if connector exists and all connectorCriteria is true, call the success call
                    if ((connector) &&
                        (this.checkCriteria(connector, connectorCriteria))) {
                        connector.connectorHash = hashKey;
                        successCall(connector);
                    }
                    else {
                        if (pinged === false) {
                            pinged = true;
                            this.getConnectors(name, successCallWrapper);
                        }
                        else {
                            pinged = false;
                            errorCall(connector);
                        }
                    }
                }
            },

            /**
             * Check to see if specified criteria is met
             * @param {object} connector
             * @param {array} criteria
             *
             * @return {boolean} true if criteria is met
             */
            checkCriteria: function (connector, criteria) {
                var check = true;

                _.each(criteria, function (criterion) {
                    if (criterion === 'test_passed') {
                        if (connector.testing_enabled) {
                            check = check && connector.test_passed;
                        }
                    }
                    else {
                        check = check && connector[criterion];
                    }

                    if (!check) {
                        return check;
                    }
                });

                return check;
            },
            /**
             * gets connector field mappings
             * @param {String} connector
             * @param {Module} module
             * @returns {{}}
             */
            getConnectorModuleFieldMapping: function (connector, module) {
                var connectors = app.cache.get(hashKey);
                var mappings = {};
                if (connectors[connector] &&
                    connectors[connector].field_mapping &&
                    connectors[connector].field_mapping.beans &&
                    connectors[connector].field_mapping.beans[module]
                    ) {
                    mappings = connectors[connector].field_mapping.beans[module];
                }
                return mappings;
            },

            /**
             * API call to connectors endpoint to populate cache
             * @param {string} name
             * @param {function} successCall
             */
            getConnectors: function (name, successCall) {
                var connectorURL = app.api.buildURL('connectors');

                app.api.call('GET', connectorURL, {}, {
                    success: function (data) {
                        hashKey = data['_hash'];
                        app.cache.set(hashKey, data['connectors']);
                        successCall();
                    }
                });
            }
        });
    });
})(SUGAR.App);