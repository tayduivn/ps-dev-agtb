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
(function(app) {
    app.events.on('app:init', function() {

        /**
         * ListEditable plugin is for fields that use a list-edit template instead of the standard edit
         * during inline editing on list views
         *
         * ATTENTION: WE DON'T SUPPORT MODULE PLUGINS. DON'T USE THIS
         * WILL BE REMOVED BY SC-3047
         */
        app.plugins.register('ContactsPortalMetadataFilter', ['view'], {
            /**
             * Check if portal is active. If not, will remove the portal fields from the metadata
             * @param {Object} meta metadata to filter.
             *
             * @deprecated since 7.2.2
             */
            removePortalFieldsIfPortalNotActive: function(meta) {
                if (!_.isObject(meta)) {
                    return;
                }
                //Portal specific fields to hide if portal is disabled
                var portalFields = ['portal_name', 'portal_active', 'portal_password'];
                var serverInfo = app.metadata.getServerInfo();
                if (!serverInfo.portal_active) {
                    _.each(meta.panels, function(panel) {
                        panel.fields = _.reject(panel.fields, function(field) {
                            var name = _.isObject(field) ? field.name : field;
                            return _.contains(portalFields, name);
                        });
                    });
                }
            }
        });
    });
})(SUGAR.App);
