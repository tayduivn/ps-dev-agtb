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
({
    extendsFrom: 'RecordlistView',

    /**
     * {@inheritDoc}
     *
     * Add KBContent plugin for view.
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins || [], [
            'KBContent'
        ]);

        this._super('initialize', [options]);

        this.layout.on('list:record:deleted', function() {
            this.refreshCollection();
        }, this);

        this.context.on('kbcontents:category:deleted', function(node) {
            this.refreshCollection();
        }, this);

        if (!app.acl.hasAccessToModel('edit', this.model)) {
            this.context.set('requiredFilter', 'records-noedit');
        }
    },

    /**
     * {@inheritDoc}
     *
     * Disable diplay status fild on list view if user has no edit access.
     */
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
