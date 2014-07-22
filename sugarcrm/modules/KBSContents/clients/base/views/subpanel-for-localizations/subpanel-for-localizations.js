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
({
    extendsFrom: 'SubpanelListView',

    initialize: function(options) {
        this._super('initialize', [options]);

        if (!app.acl.hasAccessToModel('edit', this.model)) {
            this.context.set('requiredFilter', 'records-noedit');
        }

        // setup dataView to load correct viewdefs from subpanel-for-localizations.php
        this.context.set('dataView', 'subpanel-for-localizations');
    },

    parseFieldMetadata: function(options) {
        options = this._super('parseFieldMetadata', [options]);

        if (app.acl.hasAccess('edit', options.module)) {
            return options;
        }

        _.each(options.meta.panels, function(panel, panelIdx) {
            _.each(panel.fields, function(field, fieldIdx) {
                if (field.name === 'status') {
                    delete panel.fields[fieldIdx];
                }
            }, this);
        }, this);

        return options;
    }
})
