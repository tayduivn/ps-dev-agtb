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
(function(app) {
    app.events.on('app:init', function() {
        /**
         * A client-side implementation of SugarRelationship for managing
         * relationship metadata.
         *
         * Loads the relationship metadata via the name of the relationship.
         *
         * @param {Object} link The metadata defining the instance of the
         * relationship
         * @constructor
         */
        var Relationship = function(link) {
            this.def = app.metadata.getRelationship(link.relationship) || {};
        };

        /**
         * Returns the name of the module on the RHS of the relationship.
         *
         * @return {String}
         */
        Relationship.prototype.getRHSModule = function() {
            return this.def.rhs_module;
        };

        /**
         * Extends SUGAR.App.metadata to provide some additional features for Sugar 7.
         */
        app.metadata = _.extend(app.metadata, {
            /**
             * Returns link name-module name key-value pairs where the module
             * name is the RHS module for the relationship defined by the link.
             *
             * The value is undefined if no RHS module was found for a key.
             *
             *     @example
             *     ```
             *     app.metadata.getRHSModulesForLinks('Meetings', ['users', 'contacts'])
             *
             *     will return
             *
             *     {
             *         users: 'Users',
             *         contacts: 'Contacts'
             *     }
             *     ```
             *
             * @param {String} lhsModule The name of LHS module for the
             * relationship
             * @param {Array} links The link names from the LHS module to search
             * @return {Object}
             */
            getRHSModulesForLinks: function(lhsModule, links) {
                return _.chain(app.metadata.getModule(lhsModule).fields)
                    .filter(function(field) {
                        return _.contains(links, field.name);
                    }, this)
                    .reduce(function(modules, link) {
                        if (!_.isEmpty(link.name) && link.type === 'link') {
                            modules[link.name] = (new Relationship(link)).getRHSModule();
                        }
                        return modules;
                    }, {})
                    .value();
            }
        });
    });
})(SUGAR.App);
